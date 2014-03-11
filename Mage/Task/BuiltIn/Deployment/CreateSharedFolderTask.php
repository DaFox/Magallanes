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
use Mage\Task\Releases\IsReleaseAware;

/**
 * Task for Clearing Cache
 *
 * @author Oscar Reales <oreales@gmail.com>
 */
class CreateSharedFolderTask extends AbstractTask implements IsReleaseAware
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
     * Clears Cache
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $command  = "if [ ! -d ./shared/logs ]; then mkdir -p ./shared/logs; fi; ";
		$command .= "if [ ! -d ./shared/spool ]; then mkdir -p ./shared/spool; fi; ";
		
        $result = $this->runCommand($command);

        return $result;
    }
}