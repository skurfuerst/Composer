<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\DependencyResolver;

use Composer\Repository\RepositoryInterface;
use Composer\Package\PackageInterface;
use Composer\Package\LinkConstraint\VersionConstraint;

/**
 * @author Nils Adermann <naderman@naderman.de>
 */
class DefaultPolicy implements PolicyInterface
{
    public function allowUninstall()
    {
        return true;
    }

    public function allowDowngrade()
    {
        return true;
    }

    public function versionCompare(PackageInterface $a, PackageInterface $b, $operator)
    {
        $constraint = new VersionConstraint($operator, $b->getVersion());
        $version = new VersionConstraint('==', $a->getVersion());

        return $constraint->matchSpecific($version);
    }

    public function findUpdatePackages(Solver $solver, Pool $pool, array $installedMap, PackageInterface $package, $allowAll = false)
    {
        $packages = array();

        foreach ($pool->whatProvides($package->getName()) as $candidate) {
            // skip old packages unless downgrades are an option
            if (!$allowAll && !$this->allowDowngrade() && $this->versionCompare($package, $candidate, '>')) {
                continue;
            }

            if ($candidate !== $package) {
                $packages[] = $candidate;
            }
        }

        return $packages;
    }

    public function installable(Solver $solver, Pool $pool, array $installedMap, PackageInterface $package)
    {
        // todo: package blacklist?
        return true;
    }

    public function getPriority(Pool $pool, PackageInterface $package)
    {
        return $pool->getPriority($package->getRepository());
    }

    public function selectPreferedPackages(Pool $pool, array $installedMap, array $literals)
    {
        $packages = $this->groupLiteralsByNamePreferInstalled($installedMap, $literals);

        foreach ($packages as &$literals) {
            $policy = $this;
            usort($literals, function ($a, $b) use ($policy, $pool, $installedMap) {
                return $policy->compareByPriorityPreferInstalled($pool, $installedMap, $a->getPackage(), $b->getPackage(), true);
            });
        }

        foreach ($packages as &$literals) {
            $literals = $this->pruneToBestVersion($literals);

            $literals = $this->pruneToHighestPriorityOrInstalled($pool, $installedMap, $literals);
        }

        $selected = call_user_func_array('array_merge', $packages);

        // now sort the result across all packages to respect replaces across packages
        usort($selected, function ($a, $b) use ($policy, $pool, $installedMap) {
            return $policy->compareByPriorityPreferInstalled($pool, $installedMap, $a->getPackage(), $b->getPackage());
        });

        return $selected;
    }

    protected function groupLiteralsByNamePreferInstalled(array $installedMap, $literals)
    {
        $packages = array();
        foreach ($literals as $literal) {
            $packageName = $literal->getPackage()->getName();

            if (!isset($packages[$packageName])) {
                $packages[$packageName] = array();
            }

            if (isset($installedMap[$literal->getPackageId()])) {
                array_unshift($packages[$packageName], $literal);
            } else {
                $packages[$packageName][] = $literal;
            }
        }

        return $packages;
    }

    public function compareByPriorityPreferInstalled(Pool $pool, array $installedMap, PackageInterface $a, PackageInterface $b, $ignoreReplace = false)
    {
        if ($a->getRepository() === $b->getRepository()) {

            if (!$ignoreReplace) {
                // return original, not replaced
                if ($this->replaces($a, $b)) {
                    return 1; // use b
                }
                if ($this->replaces($b, $a)) {
                    return -1; // use a
                }
            }

            // priority equal, sort by package id to make reproducible
            if ($a->getId() === $b->getId()) {
                return 0;
            }

            return ($a->getId() < $b->getId()) ? -1 : 1;
        }

        if (isset($installedMap[$a->getId()])) {
            return -1;
        }

        if (isset($installedMap[$b->getId()])) {
            return 1;
        }

        return ($this->getPriority($pool, $a) > $this->getPriority($pool, $b)) ? -1 : 1;
    }

    /**
    * Checks if source replaces a package with the same name as target.
    *
    * Replace constraints are ignored. This method should only be used for
    * prioritisation, not for actual constraint verification.
    *
    * @param PackageInterface $source
    * @param PackageInterface $target
    * @return bool
    */
    protected function replaces(PackageInterface $source, PackageInterface $target)
    {
        foreach ($source->getReplaces() as $link) {
            if ($link->getTarget() === $target->getName()
//                && (null === $link->getConstraint() ||
//                $link->getConstraint()->matches(new VersionConstraint('==', $target->getVersion())))) {
                ) {
                return true;
            }
        }

        return false;
    }

    protected function pruneToBestVersion($literals)
    {
        $bestLiterals = array($literals[0]);
        $bestPackage = $literals[0]->getPackage();
        foreach ($literals as $i => $literal) {
            if (0 === $i) {
                continue;
            }

            if ($this->versionCompare($literal->getPackage(), $bestPackage, '>')) {
                $bestPackage = $literal->getPackage();
                $bestLiterals = array($literal);
            } else if ($this->versionCompare($literal->getPackage(), $bestPackage, '==')) {
                $bestLiterals[] = $literal;
            }
        }

        return $bestLiterals;
    }

    protected function selectNewestPackages(array $installedMap, array $literals)
    {
        $maxLiterals = array($literals[0]);
        $maxPackage = $literals[0]->getPackage();
        foreach ($literals as $i => $literal) {
            if (0 === $i) {
                continue;
            }

            if ($this->versionCompare($literal->getPackage(), $maxPackage, '>')) {
                $maxPackage = $literal->getPackage();
                $maxLiterals = array($literal);
            } else if ($this->versionCompare($literal->getPackage(), $maxPackage, '==')) {
                $maxLiterals[] = $literal;
            }
        }

        return $maxLiterals;
    }

    /**
    * Assumes that installed packages come first and then all highest priority packages
    */
    protected function pruneToHighestPriorityOrInstalled(Pool $pool, array $installedMap, array $literals)
    {
        $selected = array();

        $priority = null;

        foreach ($literals as $literal) {
            $package = $literal->getPackage();

            if (isset($installedMap[$package->getId()])) {
                $selected[] = $literal;
                continue;
            }

            if (null === $priority) {
                $priority = $this->getPriority($pool, $package);
            }

            if ($this->getPriority($pool, $package) != $priority) {
                break;
            }

            $selected[] = $literal;
        }

        return $selected;
    }
}
