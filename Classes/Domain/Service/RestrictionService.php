<?php
namespace Aoe\FeloginBruteforceProtection\Domain\Service;

    /***************************************************************
     *  Copyright notice
     *
     *  (c) 2013 AOE GmbH, <dev@aoe.com>
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
 * Class Restriction
 *
 * @package Aoe\FeloginBruteforceProtection\\Domain\Service
 *
 * @author Kevin Schu <kevin.schu@aoe.com>
 * @author Timo Fuchs <timo.fuchs@aoe.com>
 * @author Andre Wuttig <wuttig@portrino.de>
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RestrictionService
{

    /**
     * @var boolean
     */
    protected static $preventFailureCount = false;

    /**
     * @var string
     */
    protected $clientIdentifier;

    /**
     * @var \Aoe\FeloginBruteforceProtection\System\Configuration
     * @inject
     */
    protected $configuration;

    /**
     * @var \Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository
     * @inject
     */
    protected $entryRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @inject
     */
    protected $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \Aoe\FeloginBruteforceProtection\Domain\Model\Entry
     */
    protected $entry;

    /**
     * @var boolean
     */
    protected $clientRestricted;

    /**
     * @param boolean $preventFailureCount
     * @return void
     */
    public static function setPreventFailureCount($preventFailureCount)
    {
        self::$preventFailureCount = $preventFailureCount;
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
        /** @var $entry \Aoe\FeloginBruteforceProtection\Domain\Model\Entry */
        $this->entry = $this->objectManager->get('Aoe\FeloginBruteforceProtection\\Domain\Model\Entry');
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
     * @param \Aoe\FeloginBruteforceProtection\Domain\Model\Entry $entry
     * @return boolean
     */
    private function isRestricted(\Aoe\FeloginBruteforceProtection\Domain\Model\Entry $entry)
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
        return ($this->getEntry() instanceof \Aoe\FeloginBruteforceProtection\Domain\Model\Entry);
    }

    /**
     * @return \Aoe\FeloginBruteforceProtection\Domain\Model\Entry|NULL
     */
    private function getEntry()
    {
        if (false === isset($this->entry)) {
            $entry = $this->entryRepository->findOneByIdentifier($this->getClientIdentifier());
            if ($entry instanceof \Aoe\FeloginBruteforceProtection\Domain\Model\Entry) {
                $this->entry = $entry;
                if ($this->isOutdated($entry)) {
                    $this->removeEntry();
                }
            }
        }
        return $this->entry;
    }

    /**
     * @param \Aoe\FeloginBruteforceProtection\Domain\Model\Entry $entry
     * @return boolean
     */
    private function isOutdated(\Aoe\FeloginBruteforceProtection\Domain\Model\Entry $entry)
    {
        return (
            ($this->hasMaximumNumberOfFailuresReached($entry) && $this->isRestrictionTimeReached($entry)) ||
            (false === $this->hasMaximumNumberOfFailuresReached($entry) && $this->isResetTimeOver($entry))
        );
    }

    /**
     * @param \Aoe\FeloginBruteforceProtection\Domain\Model\Entry $entry
     * @return boolean
     */
    private function isResetTimeOver(\Aoe\FeloginBruteforceProtection\Domain\Model\Entry $entry)
    {
        return ($entry->getCrdate() < time() - $this->configuration->getResetTime());
    }

    /**
     * @param \Aoe\FeloginBruteforceProtection\Domain\Model\Entry $entry
     * @return boolean
     */
    private function hasMaximumNumberOfFailuresReached(\Aoe\FeloginBruteforceProtection\Domain\Model\Entry $entry)
    {
        return ($entry->getFailures() >= $this->configuration->getMaximumNumerOfFailures());
    }

    /**
     * @param \Aoe\FeloginBruteforceProtection\Domain\Model\Entry $entry
     * @return boolean
     */
    private function isRestrictionTimeReached(\Aoe\FeloginBruteforceProtection\Domain\Model\Entry $entry)
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
