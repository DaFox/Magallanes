<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Deployment;

use Mage\Task\AbstractTask;

/**
 * Task for creating shared folder and symlinks for releases
 *
 * @author Thomas Hamacher <th.hamacher@gmail.com>
 */
class CreateSharedFolderTask extends AbstractTask
{
	/**
	 * (non-PHPdoc)
	 * @see \Mage\Task\AbstractTask::getName()
	 */
    public function getName()
    {
        return 'Create shared project folders [built-in]';
    }

    /**
     * Creates shared folder and symlinks
     *
     * @see \Mage\Task\AbstractTask::run()
     * @return boolean
     */
    public function run()
    {
        $defaults = array(
            'directory' => 'shared',
            'symlink' => array()
        );

        $config = $this->getConfig()->release('shared', array());

        if(is_string($config)) {
            $config = array(
                'directory' => $config,
                'symlink' => array()
            );
        }

        $config = array_merge($defaults, $config);
        $shared = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $config['directory'];

        $command = "if [ ! -d $shared ]; then mkdir -p $shared; fi; ";

        if(is_string($config['symlink'])) {
            $command .= "ln -s $shared {$config['symlink']}; ";
        }
        else {
            foreach($config['symlink'] as $subDir => $symlink) {
                $command .= "if [ ! -d $shared/$subDir ]; then mkdir -p $shared/$subDir; fi; ";
                $command .= "ln -s $shared/$subDir $symlink; ";
            }
        }

        return $this->runCommand($command);
    }
}