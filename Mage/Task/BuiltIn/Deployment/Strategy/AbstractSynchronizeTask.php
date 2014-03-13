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

/**
 * Abstract base task for all local sync tasks
 *
 * @author Thomas Hamacher <th.hamacher@gmail.com>
 */
abstract class AbstractSynchronizeTask extends AbstractTask {

    protected function _overrideReleaseId() {
        $overrideRelease = $this->getParameter('overrideRelease', false);

        if ($overrideRelease == true) {
            $releaseToOverride = false;
            $symlink = $this->getConfig()->release('symlink', 'current');
            $resultFetch = $this->runCommandRemote('ls -ld ' . $symlink . ' | cut -d"/" -f2', $releaseToOverride);
            if ($resultFetch && is_numeric($releaseToOverride)) {
                $this->getConfig()->setReleaseId($releaseToOverride);
            }
        }
    }

    protected function _getExcludes() {
        $excludes = array(
            '.git',
            '.svn',
            '.mage',
            '.gitignore',
            '.gitkeep',
            'nohup.out'
        );

        // Look for User Excludes
        return array_merge($excludes, $this->getConfig()->deployment('excludes', array()));
    }
}