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

namespace Composer\Repository;

/**
 * Repositories manager.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RepositoryManager
{
    private $localRepository;
    private $repositories = array();
    private $repositoryClasses = array();

    /**
     * Used for lazy loading of packages and their contained repositories
     *
     * This is a performance optimization to avoid loading all packages unless they are needed
     *
     * @var Boolean
     */
    private $initialized;

    /**
     * Searches for a package by it's name and version in managed repositories.
     *
     * @param   string  $name       package name
     * @param   string  $version    package version
     *
     * @return  PackageInterface|null
     */
    public function findPackage($name, $version)
    {
        foreach ($this->repositories as $repository) {
            if ($package = $repository->findPackage($name, $version)) {
                return $package;
            }
        }
    }

    /**
     * Adds repository
     *
     * @param   RepositoryInterface $repository repository instance
     */
    public function addRepository(RepositoryInterface $repository)
    {
        $this->repositories[] = $repository;

        // already initialized, so initialize new repos on the fly
        if ($this->initialized) {
            $repository->getPackages();
        }
    }

    /**
     * Returns a new repository for a specific installation type.
     *
     * @param   string $type repository type
     * @param   string $config repository configuration
     * @return  RepositoryInterface
     * @throws  InvalidArgumentException     if repository for provided type is not registeterd
     */
    public function createRepository($type, $config)
    {
        if (!isset($this->repositoryClasses[$type])) {
            throw new \InvalidArgumentException('Repository type is not registered: '.$type);
        }

        $class = $this->repositoryClasses[$type];
        return new $class($config);
    }

    /**
     * Stores repository class for a specific installation type.
     *
     * @param   string  $type   installation type
     * @param   string  $class  class name of the repo implementation
     */
    public function setRepositoryClass($type, $class)
    {
        $this->repositoryClasses[$type] = $class;
    }

    /**
     * Returns all repositories, except local one.
     *
     * @return  array
     */
    public function getRepositories()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            // warm up repos to be sure all sub-repos are added before we return
            foreach ($this->repositories as $repository) {
                $repository->getPackages();
            }
        }
        return $this->repositories;
    }

    /**
     * Sets local repository for the project.
     *
     * @param   RepositoryInterface $repository repository instance
     */
    public function setLocalRepository(RepositoryInterface $repository)
    {
        $this->localRepository = $repository;
    }

    /**
     * Returns local repository for the project.
     *
     * @return  RepositoryInterface
     */
    public function getLocalRepository()
    {
        return $this->localRepository;
    }
}
