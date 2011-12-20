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
use Symfony\Component\Process\Process;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ZipDownloader extends FileDownloader
{
    protected function extract($file, $path)
    {
        if (!class_exists('ZipArchive')) {
            // try to use unzip on *nix
            if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
                $process = new Process('unzip '.escapeshellarg($file).' -d '.escapeshellarg($path));
                $process->run();
                if ($process->isSuccessful()) {
                    return;
                }
            }

            throw new \RuntimeException('You need the zip extension enabled to use the ZipDownloader');
        }

        $zipArchive = new \ZipArchive();

        if (true !== ($retval = $zipArchive->open($file))) {
            throw new \UnexpectedValueException($file.' is not a valid zip archive, got error code '.$retval);
        }

        $zipArchive->extractTo($path);
        $zipArchive->close();
    }
}
