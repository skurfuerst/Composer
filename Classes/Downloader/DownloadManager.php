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

namespace Composer\Downloader;

use Composer\Package\PackageInterface;
use Composer\Downloader\DownloaderInterface;

/**
 * Downloaders manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DownloadManager
{
    private $preferSource = false;
    private $downloaders  = array();

    /**
     * Initializes download manager.
     *
     * @param   Boolean $preferSource   prefer downloading from source
     */
    public function __construct($preferSource = false)
    {
        $this->preferSource = $preferSource;
    }

    /**
     * Makes downloader prefer source installation over the dist.
     *
     * @param   Boolean $preferSource   prefer downloading from source
     */
    public function setPreferSource($preferSource)
    {
        $this->preferSource = $preferSource;
    }

    /**
     * Sets installer downloader for a specific installation type.
     *
     * @param   string              $type       installation type
     * @param   DownloaderInterface $downloader downloader instance
     */
    public function setDownloader($type, DownloaderInterface $downloader)
    {
        $this->downloaders[$type] = $downloader;
    }

    /**
     * Returns downloader for a specific installation type.
     *
     * @param   string  $type   installation type
     *
     * @return  DownloaderInterface
     *
     * @throws  UnexpectedValueException    if downloader for provided type is not registeterd
     */
    public function getDownloader($type)
    {
        if (!isset($this->downloaders[$type])) {
            throw new \InvalidArgumentException('Unknown downloader type: '.$type);
        }

        return $this->downloaders[$type];
    }

    /**
     * Returns downloader for already installed package.
     *
     * @param   PackageInterface    $package    package instance
     *
     * @return  DownloaderInterface
     *
     * @throws  InvalidArgumentException        if package has no installation source specified
     * @throws  LogicException                  if specific downloader used to load package with
     *                                          wrong type
     */
    public function getDownloaderForInstalledPackage(PackageInterface $package)
    {
        $installationSource = $package->getInstallationSource();

        if ('dist' === $installationSource) {
            $downloader = $this->getDownloader($package->getDistType());
        } elseif ('source' === $installationSource) {
            $downloader = $this->getDownloader($package->getSourceType());
        } else {
            throw new \InvalidArgumentException(
                'Package '.$package.' seems not been installed properly'
            );
        }

        if ($installationSource !== $downloader->getInstallationSource()) {
            throw new \LogicException(sprintf(
                'Downloader "%s" is a %s type downloader and can not be used to download %s',
                get_class($downloader), $downloader->getInstallationSource(), $installationSource
            ));
        }

        return $downloader;
    }

    /**
     * Downloads package into target dir.
     *
     * @param   PackageInterface    $package        package instance
     * @param   string              $targetDir      target dir
     * @param   Boolean             $preferSource   prefer installation from source
     *
     * @throws  InvalidArgumentException            if package have no urls to download from
     */
    public function download(PackageInterface $package, $targetDir, $preferSource = null)
    {
        $preferSource = null !== $preferSource ? $preferSource : $this->preferSource;
        $sourceType   = $package->getSourceType();
        $distType     = $package->getDistType();

        if (!($preferSource && $sourceType) && $distType) {
            $package->setInstallationSource('dist');
        } elseif ($sourceType) {
            $package->setInstallationSource('source');
        } else {
            throw new \InvalidArgumentException(
                'Package '.$package.' should have source or dist specified'
            );
        }

        $fs = new Util\Filesystem();
        $fs->ensureDirectoryExists($targetDir);
        
        $downloader = $this->getDownloaderForInstalledPackage($package);
        $downloader->download($package, $targetDir);
    }

    /**
     * Updates package from initial to target version.
     *
     * @param   PackageInterface    $initial    initial package version
     * @param   PackageInterface    $target     target package version
     * @param   string              $targetDir  target dir
     *
     * @throws  InvalidArgumentException        if initial package is not installed
     */
    public function update(PackageInterface $initial, PackageInterface $target, $targetDir)
    {
        $downloader = $this->getDownloaderForInstalledPackage($initial);
        $installationSource = $initial->getInstallationSource();

        if ('dist' === $installationSource) {
            $initialType = $initial->getDistType();
            $targetType  = $target->getDistType();
        } else {
            $initialType = $initial->getSourceType();
            $targetType  = $target->getSourceType();
        }

        if ($initialType === $targetType) {
            $target->setInstallationSource($installationSource);
            $downloader->update($initial, $target, $targetDir);
        } else {
            $downloader->remove($initial, $targetDir);
            $this->download($target, $targetDir, 'source' === $installationSource);
        }
    }

    /**
     * Removes package from target dir.
     *
     * @param   PackageInterface    $package    package instance
     * @param   string              $targetDir  target dir
     */
    public function remove(PackageInterface $package, $targetDir)
    {
        $downloader = $this->getDownloaderForInstalledPackage($package);
        $downloader->remove($package, $targetDir);
    }
}
