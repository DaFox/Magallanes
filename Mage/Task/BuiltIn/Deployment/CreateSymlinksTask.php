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
 * Task for Clearing Cache
 *
 * @author Oscar Reales <oreales@gmail.com>
 */
class CreateSymlinksTask extends AbstractTask
{
	/**
	 * (non-PHPdoc)
	 * @see \Mage\Task\AbstractTask::getName()
	 */
    public function getName()
    {
        return 'Create symlinks to shared folders [built-in]';
    }

    /**
     * Clears Cache
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
		$target   = rtrim($this->getConfig()->deployment('to'), '/');
		$shared   = $target . '/shared';
        $command  = "ln -s $shared/spool spool";
		
        return $this->runCommand($command);
    }
}