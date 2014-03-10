<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Deployment\Strategy;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Task for Sync the Local Tarball to the Remote Hosts
 *
 * @author Thomas Hamacher <dafox@gmx.com>
 */
class PreBuiltTarGzTask extends AbstractTask implements IsReleaseAware
{
	/**
	 * (non-PHPdoc)
	 * @see \Mage\Task\AbstractTask::getName()
	 */
    public function getName()
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            if ($this->getConfig()->getParameter('overrideRelease', false) == true) {
                return 'Deploy via pre-built TarGz (with Releases override) [built-in]';
            } else {
                return 'Deploy via pre-built TarGz (with Releases) [built-in]';
            }
        } else {
                return 'Deploy via pre-built TarGz [built-in]';
        }
    }

    /**
     * Syncs the Local Code to the Remote Host
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $overrideRelease = $this->getParameter('overrideRelease', false);

        if ($overrideRelease == true) {
            $releaseToOverride = false;
            $resultFetch = $this->runCommandRemote('ls -ld current | cut -d"/" -f2', $releaseToOverride);
            if ($resultFetch && is_numeric($releaseToOverride)) {
                $this->getConfig()->setReleaseId($releaseToOverride);
            }
        }


        // If we are working with releases
        $deployToDirectory = $this->getConfig()->deployment('to');
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');

            $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
                               . '/' . $releasesDirectory
                               . '/' . $this->getConfig()->getReleaseId();
            $this->runCommandRemote('mkdir -p ' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId());
        }

        // Create Tar Gz
        $localTarGz = $this->getConfig()->deployment('from');
        $remoteTarGz = basename($localTarGz);

        // Copy Tar Gz  to Remote Host
        $command = 'scp -P ' . $this->getConfig()->getHostPort() . ' ' . $localTarGz . ' '
                 . $this->getConfig()->deployment('user') . '@' . $this->getConfig()->getHostName() . ':' . $deployToDirectory;
        $result = $this->runCommandLocal($command);

        // Extract Tar Gz
        if ($this->getConfig()->release('enabled', false) == true) {
        	$releasesDirectory = $this->getConfig()->release('directory', 'releases');

        	$deployToDirectory = $releasesDirectory . '/' . $this->getConfig()->getReleaseId();
        	$command = 'cd ' . $deployToDirectory . ' && tar xfz ' . $remoteTarGz;
        } else {
        	$command = 'tar xfz ' . $remoteTarGz;
        }
        $result = $this->runCommandRemote($command) && $result;

        // Delete Tar Gz from Remote Host
        if ($this->getConfig()->release('enabled', false) == true) {
        	$releasesDirectory = $this->getConfig()->release('directory', 'releases');

        	$deployToDirectory = $releasesDirectory . '/' . $this->getConfig()->getReleaseId();
        	$command = 'rm ' . $deployToDirectory . '/' . $remoteTarGz;
        } else {
        	$command = 'rm ' . $remoteTarGz;
        }
        $result = $this->runCommandRemote($command) && $result;

        // Count Releases
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            if (substr($symlink, 0, 1) == '/') {
                $releasesDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;
            }

            $maxReleases = $this->getConfig()->release('max', false);
            if (($maxReleases !== false) && ($maxReleases > 0)) {
                $releasesList = '';
                $countReleasesFetch = $this->runCommandRemote('ls -1 ' . $releasesDirectory, $releasesList);
                $releasesList = trim($releasesList);

                if ($countReleasesFetch && $releasesList != '') {
                    $releasesList = explode(PHP_EOL, $releasesList);
                    if (count($releasesList) > $maxReleases) {
                        $releasesToDelete = array_diff($releasesList, array($this->getConfig()->getReleaseId()));
                        sort($releasesToDelete);
                        $releasesToDeleteCount = count($releasesToDelete) - $maxReleases;
                        $releasesToDelete = array_slice($releasesToDelete, 0, $releasesToDeleteCount + 1);

                        foreach ($releasesToDelete as $releaseIdToDelete) {
                            $directoryToDelete = $releasesDirectory . '/' . $releaseIdToDelete;
                            if ($directoryToDelete != '/') {
                                $command = 'rm -rf ' . $directoryToDelete;
                                $result = $result && $this->runCommandRemote($command);
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }
}