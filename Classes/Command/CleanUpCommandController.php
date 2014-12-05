<?php
namespace Aoe\FeloginBruteforceProtection\Command;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 AOE GmbH <dev@aoe.com>
 *  (c) 2014 André Wuttig <wuttig@portrino.de>, portrino GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class CleanUpCommandController
 *
 * @package Aoe\FeloginBruteforceProtection\Command
 *
 * @author Andre Wuttig <wuttig@portrino.de>
 */
class CleanUpCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected $configurationManager;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * @var \Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository
     * @inject
     */
    protected $entryRepository;

    /**
     * @var \Aoe\FeloginBruteforceProtection\System\Configuration
     * @inject
     */
    protected $configuration;

    public function cleanupCommand() {
        $entriesToCleanUp = $this->entryRepository->findEntriesToCleanUp(
            $this->configuration->get(\Aoe\FeloginBruteforceProtection\System\Configuration::CONF_SECONDS_TILL_RESET),
            $this->configuration->get(\Aoe\FeloginBruteforceProtection\System\Configuration::CONF_MAX_FAILURES),
            $this->configuration->get(\Aoe\FeloginBruteforceProtection\System\Configuration::CONF_RESTRICTION_TIME)
        );

        foreach($entriesToCleanUp as $entryToCleanUp) {
            $this->entryRepository->remove($entryToCleanUp);
        }

        $this->persistenceManager->persistAll();
    }

}
