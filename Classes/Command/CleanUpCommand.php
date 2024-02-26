<?php

namespace Aoe\FeloginBruteforceProtection\Command;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 AOE GmbH <dev@aoe.com>
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

use Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class CleanUpCommand extends Command
{
    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var EntryRepository
     */
    protected $entryRepository;

    /**
     * @var Configuration
     */
    protected $configuration;

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    public function injectPersistenceManager(PersistenceManager $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function injectEntryRepository(EntryRepository $entryRepository): void
    {
        $this->entryRepository = $entryRepository;
    }

    public function injectConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entriesToCleanUp = $this->entryRepository->findEntriesToCleanUp(
            $this->configuration->get(Configuration::CONF_SECONDS_TILL_RESET),
            $this->configuration->get(Configuration::CONF_MAX_FAILURES),
            $this->configuration->get(Configuration::CONF_RESTRICTION_TIME)
        );

        foreach ($entriesToCleanUp as $entryToCleanUp) {
            $this->entryRepository->remove($entryToCleanUp);
        }

        $this->persistenceManager->persistAll();

        return Command::SUCCESS;
    }
}
