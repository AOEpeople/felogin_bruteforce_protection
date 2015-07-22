<?php
namespace Aoe\FeloginBruteforceProtection\Domain\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 AOE GmbH, <dev@aoe.com>
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

use Aoe\FeloginBruteforceProtection\Domain\Model\Entry;

/**
 *
 * @package Aoe\FeloginBruteforceProtection\\Domain\Service
 *
 * @author Kevin Schu <kevin.schu@aoe.com>
 * @author Timo Fuchs <timo.fuchs@aoe.com>
 * @author Andre Wuttig <wuttig@portrino.de>
 *
 */
class RestrictionService
{
    /**
     * @var boolean
     */
    protected static $preventFailureCount = false;

    /**
     * @var RestrictionIdentifierInterface
     */
    protected $restrictionIdentifier;

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
     * @var Entry
     */
    protected $entry;

    /**
     * @var boolean
     */
    protected $clientRestricted;

    /**
     * @param RestrictionIdentifierInterface $restrictionIdentifier
     */
    public function __construct(RestrictionIdentifierInterface $restrictionIdentifier)
    {
        $this->restrictionIdentifier = $restrictionIdentifier;
    }

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
    public function checkAndHandleRestriction()
    {
        if (self::$preventFailureCount) {
            return;
        }

        $identifierValue = $this->restrictionIdentifier->getIdentifierValue();
        if (empty($identifierValue)) {
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
        /** @var $entry Entry */
        $this->entry = $this->objectManager->get('Aoe\FeloginBruteforceProtection\Domain\Model\Entry');
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
     * @param Entry $entry
     * @return boolean
     */
    private function isRestricted(Entry $entry)
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
    public function hasEntry()
    {
        return ($this->getEntry() instanceof Entry);
    }

    /**
     * @return Entry|null
     */
    public function getEntry()
    {
        if (false === isset($this->entry)) {
            $entry = $this->entryRepository->findOneByIdentifier($this->getClientIdentifier());
            if ($entry instanceof Entry) {
                $this->entry = $entry;
                if ($this->isOutdated($entry)) {
                    $this->removeEntry();
                }
            }
        }
        return $this->entry;
    }

    /**
     * @param Entry $entry
     * @return boolean
     */
    private function isOutdated(Entry $entry)
    {
        return (
            ($this->hasMaximumNumberOfFailuresReached($entry) && $this->isRestrictionTimeReached($entry)) ||
            (false === $this->hasMaximumNumberOfFailuresReached($entry) && $this->isResetTimeOver($entry))
        );
    }

    /**
     * @param Entry $entry
     * @return boolean
     */
    private function isResetTimeOver(Entry $entry)
    {
        return ($entry->getCrdate() < time() - $this->configuration->getResetTime());
    }

    /**
     * @param Entry $entry
     * @return boolean
     */
    private function hasMaximumNumberOfFailuresReached(Entry $entry)
    {
        return ($entry->getFailures() >= $this->configuration->getMaximumNumberOfFailures());
    }

    /**
     * @param Entry $entry
     * @return boolean
     */
    private function isRestrictionTimeReached(Entry $entry)
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
                $this->restrictionIdentifier->getIdentifierValue() . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
            );
        }
        return $this->clientIdentifier;
    }
}
