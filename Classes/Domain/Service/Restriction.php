<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 AOE media GmbH, <dev@aoemedia.de>
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
 * @package Tx_FeloginBruteforceProtection
 * @subpackage Domain_Service
 * @author Kevin Schu <kevin.schu@aoemedia.de>
 * @author Timo Fuchs <timo.fuchs@aoemedia.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FeloginBruteforceProtection_Domain_Service_Restriction
{

    /**
     * @var boolean
     */
    private static $preventFailureCount = false;

    /**
     * @var string
     */
    private $clientIdentifier;

    /**
     * @var Tx_FeloginBruteforceProtection_System_Configuration
     */
    private $configuration;

    /**
     * @var Tx_FeloginBruteforceProtection_Domain_Repository_Entry
     */
    private $entryRepository;

    /**
     * @var Tx_Extbase_Persistence_Manager
     */
    private $persistenceManager;

    /**
     * @var Tx_Extbase_Object_ObjectManager
     */
    private $objectManager;

    /**
     * @var Tx_FeloginBruteforceProtection_Domain_Model_Entry
     */
    private $entry;

    /**
     * @var boolean
     */
    private $clientRestricted;

    /**
     * @param boolean $preventFailureCount
     * @return void
     */
    public static function setPreventFailureCount($preventFailureCount)
    {
        self::$preventFailureCount = $preventFailureCount;
    }

    /**
     * @param Tx_FeloginBruteforceProtection_System_Configuration $configuration
     * @return void
     */
    public function injectConfiguration(Tx_FeloginBruteforceProtection_System_Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param Tx_FeloginBruteforceProtection_Domain_Repository_Entry $entryRepository
     * @return void
     */
    public function injectEntryRepository(Tx_FeloginBruteforceProtection_Domain_Repository_Entry $entryRepository)
    {
        $this->entryRepository = $entryRepository;
    }

    /**
     * @param Tx_Extbase_Persistence_Manager $persistenceManager
     * @return void
     */
    public function injectPersistenceManager(Tx_Extbase_Persistence_Manager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param Tx_Extbase_Object_ObjectManager $objectManager
     * @return void
     */
    public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return boolean
     */
    public function isClientRestricted()
    {
        if (false === isset($this->clientRestricted)) {
            if ($this->hasEntry() && $this->isRestricted($this->getEntry())) {
                $this->clientRestricted = true;
            } else {
                $this->clientRestricted = false;
            }
        }
        return $this->clientRestricted;
    }

    /**
     * @return void
     */
    public function removeEntry()
    {
        if ($this->hasEntry()) {
            $this->entryRepository->remove($this->entry);
            $this->persistenceManager->persistAll();
        }
        $this->clientRestricted = false;
        unset($this->entry);
    }

    /**
     * @return void
     */
    public function incrementFailureCount()
    {
        if (self::$preventFailureCount) {
            return;
        }
        if (false === $this->hasEntry()) {
            $this->createEntry();
        }
        if ($this->hasMaximumNumberOfFailuresReached($this->getEntry())) {
            return;
        }
        $this->entry->increaseFailures();
        $this->saveEntry();
    }

    /**
     * @return void
     */
    private function createEntry()
    {
        /** @var $entry Tx_FeloginBruteforceProtection_Domain_Model_Entry */
        $this->entry = $this->objectManager->get('Tx_FeloginBruteforceProtection_Domain_Model_Entry');
        $this->entry->setFailures(0);
        $this->entry->setCrdate(time());
        $this->entry->setTstamp(time());
        $this->entry->setIdentifier($this->getClientIdentifier());
        $this->entryRepository->add($this->entry);
        $this->persistenceManager->persistAll();
        $this->clientRestricted = false;
    }

    /**
     * @return void
     */
    private function saveEntry()
    {
        if ($this->entry->getFailures() > 0) {
            $this->entry->setTstamp(time());
        }
        $this->entryRepository->add($this->entry);
        $this->persistenceManager->persistAll();
        if ($this->hasMaximumNumberOfFailuresReached($this->entry)) {
            $this->clientRestricted = true;
        }
    }

    /**
     * @param Tx_FeloginBruteforceProtection_Domain_Model_Entry $entry
     * @return boolean
     */
    private function isRestricted(Tx_FeloginBruteforceProtection_Domain_Model_Entry $entry)
    {
        if ($this->hasMaximumNumberOfFailuresReached($entry)) {
            if (false === $this->isRestrictionTimeReached($entry)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return boolean
     */
    private function hasEntry()
    {
        return ($this->getEntry() instanceof Tx_FeloginBruteforceProtection_Domain_Model_Entry);
    }

    /**
     * @return Tx_FeloginBruteforceProtection_Domain_Model_Entry|NULL
     */
    private function getEntry()
    {
        if (false === isset($this->entry)) {
            $entry = $this->entryRepository->findOneByIdentifier($this->getClientIdentifier());
            if ($entry instanceof Tx_FeloginBruteforceProtection_Domain_Model_Entry) {
                $this->entry = $entry;
                if ($this->isOutdated($entry)) {
                    $this->removeEntry();
                }
            }
        }
        return $this->entry;
    }

    /**
     * @param Tx_FeloginBruteforceProtection_Domain_Model_Entry $entry
     * @return boolean
     */
    private function isOutdated(Tx_FeloginBruteforceProtection_Domain_Model_Entry $entry)
    {
        return (
            ($this->hasMaximumNumberOfFailuresReached($entry) && $this->isRestrictionTimeReached($entry)) ||
            (false === $this->hasMaximumNumberOfFailuresReached($entry) && $this->isResetTimeOver($entry))
        );
    }

    /**
     * @param Tx_FeloginBruteforceProtection_Domain_Model_Entry $entry
     * @return boolean
     */
    private function isResetTimeOver(Tx_FeloginBruteforceProtection_Domain_Model_Entry $entry)
    {
        return ($entry->getCrdate() < time() - $this->configuration->getResetTime());
    }

    /**
     * @param Tx_FeloginBruteforceProtection_Domain_Model_Entry $entry
     * @return boolean
     */
    private function hasMaximumNumberOfFailuresReached(Tx_FeloginBruteforceProtection_Domain_Model_Entry $entry)
    {
        return ($entry->getFailures() >= $this->configuration->getMaximumNumerOfFailures());
    }

    /**
     * @param Tx_FeloginBruteforceProtection_Domain_Model_Entry $entry
     * @return boolean
     */
    private function isRestrictionTimeReached(Tx_FeloginBruteforceProtection_Domain_Model_Entry $entry)
    {
        return ($entry->getTstamp() < time() - $this->configuration->getRestrictionTime());
    }

    /**
     * Returns the client identifier based on the clients IP address.
     *
     * @return string
     */
    private function getClientIdentifier()
    {
        if (false === isset($this->clientIdentifier)) {
            $this->clientIdentifier = md5(
                $_SERVER['REMOTE_ADDR'] . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
            );
        }
        return $this->clientIdentifier;
    }
}