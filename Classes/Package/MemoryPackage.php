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

namespace Composer\Package;

/**
 * A package with setters for all members to create it dynamically in memory
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
class MemoryPackage extends BasePackage
{
    protected $type;
    protected $targetDir;
    protected $installationSource;
    protected $sourceType;
    protected $sourceUrl;
    protected $sourceReference;
    protected $distType;
    protected $distUrl;
    protected $distReference;
    protected $distSha1Checksum;
    protected $version;
    protected $prettyVersion;
    protected $repositories;
    protected $license;
    protected $releaseDate;
    protected $keywords;
    protected $authors;
    protected $description;
    protected $homepage;
    protected $extra = array();
    protected $binaries = array();

    protected $requires = array();
    protected $conflicts = array();
    protected $provides = array();
    protected $replaces = array();
    protected $recommends = array();
    protected $suggests = array();
    protected $autoload = array();

    /**
     * Creates a new in memory package.
     *
     * @param string $name        The package's name
     * @param string $version     The package's version
     * @param string $prettyVersion The package's non-normalized version
     */
    public function __construct($name, $version, $prettyVersion)
    {
        parent::__construct($name);

        $this->version = $version;
        $this->prettyVersion = $prettyVersion;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->type ?: 'library';
    }

    /**
     * @param string $targetDir
     */
    public function setTargetDir($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * {@inheritDoc}
     */
    public function getTargetDir()
    {
        return $this->targetDir;
    }

    /**
     * @param array $extra
     */
    public function setExtra(array $extra)
    {
        $this->extra = $extra;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param array $binaries
     */
    public function setBinaries(array $binaries)
    {
        $this->binaries = $binaries;
    }

    /**
     * {@inheritDoc}
     */
    public function getBinaries()
    {
        return $this->binaries;
    }

    /**
     * {@inheritDoc}
     */
    public function setInstallationSource($type)
    {
        $this->installationSource = $type;
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallationSource()
    {
        return $this->installationSource;
    }

    /**
     * @param string $type
     */
    public function setSourceType($type)
    {
        $this->sourceType = $type;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * @param string $url
     */
    public function setSourceUrl($url)
    {
        $this->sourceUrl = $url;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    /**
     * @param string $reference
     */
    public function setSourceReference($reference)
    {
        $this->sourceReference = $reference;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceReference()
    {
        return $this->sourceReference;
    }

    /**
     * @param string $type
     */
    public function setDistType($type)
    {
        $this->distType = $type;
    }

    /**
     * {@inheritDoc}
     */
    public function getDistType()
    {
        return $this->distType;
    }

    /**
     * @param string $url
     */
    public function setDistUrl($url)
    {
        $this->distUrl = $url;
    }

    /**
     * {@inheritDoc}
     */
    public function getDistUrl()
    {
        return $this->distUrl;
    }

    /**
     * @param string $reference
     */
    public function setDistReference($reference)
    {
        $this->distReference = $reference;
    }

    /**
     * {@inheritDoc}
     */
    public function getDistReference()
    {
        return $this->distReference;
    }

    /**
     * @param string $sha1checksum
     */
    public function setDistSha1Checksum($sha1checksum)
    {
        $this->distSha1Checksum = $sha1checksum;
    }

    /**
     * {@inheritDoc}
     */
    public function getDistSha1Checksum()
    {
        return $this->distSha1Checksum;
    }

    /**
     * Set the repositories
     *
     * @param string $repositories
     */
    public function setRepositories($repositories)
    {
        $this->repositories = $repositories;
    }

    /**
     * {@inheritDoc}
     */
    public function getRepositories()
    {
        return $this->repositories;
    }

    /**
     * {@inheritDoc}
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritDoc}
     */
    public function getPrettyVersion()
    {
        return $this->prettyVersion;
    }

    /**
     * Set the license
     *
     * @param string $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * {@inheritDoc}
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Set the required packages
     *
     * @param array $requires A set of package links
     */
    public function setRequires(array $requires)
    {
        $this->requires = $requires;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequires()
    {
        return $this->requires;
    }

    /**
     * Set the conflicting packages
     *
     * @param array $conflicts A set of package links
     */
    public function setConflicts(array $conflicts)
    {
        $this->conflicts = $conflicts;
    }

    /**
     * {@inheritDoc}
     */
    public function getConflicts()
    {
        return $this->conflicts;
    }

    /**
     * Set the provided virtual packages
     *
     * @param array $provides A set of package links
     */
    public function setProvides(array $provides)
    {
        $this->provides = $provides;
    }

    /**
     * {@inheritDoc}
     */
    public function getProvides()
    {
        return $this->provides;
    }

    /**
     * Set the packages this one replaces
     *
     * @param array $replaces A set of package links
     */
    public function setReplaces(array $replaces)
    {
        $this->replaces = $replaces;
    }

    /**
     * {@inheritDoc}
     */
    public function getReplaces()
    {
        return $this->replaces;
    }

    /**
     * Set the recommended packages
     *
     * @param array $recommends A set of package links
     */
    public function setRecommends(array $recommends)
    {
        $this->recommends = $recommends;
    }

    /**
     * {@inheritDoc}
     */
    public function getRecommends()
    {
        return $this->recommends;
    }

    /**
     * Set the suggested packages
     *
     * @param array $suggests A set of package links
     */
    public function setSuggests(array $suggests)
    {
        $this->suggests = $suggests;
    }

    /**
     * {@inheritDoc}
     */
    public function getSuggests()
    {
        return $this->suggests;
    }

    /**
     * Set the releaseDate
     *
     * @param DateTime $releaseDate
     */
    public function setReleasedate(\DateTime $releaseDate)
    {
        $this->releaseDate = $releaseDate;
    }

    /**
     * {@inheritDoc}
     */
    public function getReleaseDate()
    {
        return $this->releaseDate;
    }

    /**
     * Set the keywords
     *
     * @param array $keywords
     */
    public function setKeywords(array $keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * {@inheritDoc}
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set the authors
     *
     * @param array $authors
     */
    public function setAuthors(array $authors)
    {
        $this->authors = $authors;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Set the description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the homepage
     *
     * @param string $homepage
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
    }

    /**
     * {@inheritDoc}
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * Set the autoload mapping
     *
     * @param array $autoload Mapping of autoloading rules
     */
    public function setAutoload(array $autoload)
    {
        $this->autoload = $autoload;
    }

    /**
     * {@inheritDoc}
     */
    public function getAutoload()
    {
        return $this->autoload;
    }
}
