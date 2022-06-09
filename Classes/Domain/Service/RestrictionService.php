<?php

namespace Aoe\FeloginBruteforceProtection\Domain\Service;

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

use Aoe\FeloginBruteforceProtection\Domain\Model\Entry;
use Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository;
use Aoe\FeloginBruteforceProtection\Service\FeLoginBruteForceApi\FeLoginBruteForceApi;
use Aoe\FeloginBruteforceProtection\Service\Logger\Logger;
use Aoe\FeloginBruteforceProtection\Service\Logger\LoggerInterface;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * @package Aoe\\FeloginBruteforceProtection\\Domain\\Service
 *
 * @author  Kevin Schu <kevin.schu@aoe.com>
 * @author  Timo Fuchs <timo.fuchs@aoe.com>
 * @author  Andre Wuttig <wuttig@portrino.de>
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
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var EntryRepository
     */
    protected $entryRepository;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var ObjectManagerInterface
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
     * @var Logger
     */
    protected $logger;

    /**
     * @var FeLoginBruteForceApi
     */
    protected $feLoginBruteForceApi;

    public function __construct(RestrictionIdentifierInterface $restrictionIdentifier)
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->restrictionIdentifier = $restrictionIdentifier;

        $this->configuration = $this->objectManager->get(Configuration::class);
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $this->entryRepository = $this->objectManager->get(EntryRepository::class);
    }

    /**
     * @param boolean $preventFailureCount
     */
    public static function setPreventFailureCount($preventFailureCount): void
    {
        self::$preventFailureCount = $preventFailureCount;
    }

    public function isClientRestricted(): bool
    {
        if (!isset($this->clientRestricted)) {
            $this->clientRestricted = $this->hasEntry() && $this->isRestricted($this->getEntry());
        }

        return $this->clientRestricted;
    }

    public function removeEntry(): void
    {
        if ($this->hasEntry()) {
            $this->entryRepository->remove($this->entry);
            $this->persistenceManager->persistAll();

            $this->log('Bruteforce Counter removed', LoggerInterface::SEVERITY_INFO);
        }
        $this->clientRestricted = false;
        unset($this->entry);
    }

    public function checkAndHandleRestriction(): void
    {
        if (self::$preventFailureCount) {
            return;
        }

        $identifierValue = $this->restrictionIdentifier->getIdentifierValue();
        if (empty($identifierValue)) {
            return;
        }

        if (!$this->hasEntry()) {
            $this->createEntry();
        }

        if ($this->hasMaximumNumberOfFailuresReached($this->getEntry())) {
            return;
        }

        $this->entry->increaseFailures();
        $this->saveEntry();

        $this->restrictionLog();
    }

    public function hasEntry(): bool
    {
        return $this->getEntry() instanceof Entry;
    }

    /**
     * @return Entry|null
     */
    public function getEntry()
    {
        if (!isset($this->entry)) {
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

    protected function restrictionLog(): void
    {
        if ($this->getFeLoginBruteForceApi()->shouldCountWithinThisRequest()) {
            if ($this->isClientRestricted()) {
                $this->log('Bruteforce Protection Locked', LoggerInterface::SEVERITY_WARNING);
            } else {
                $this->log('Bruteforce Counter increased', LoggerInterface::SEVERITY_NOTICE);
            }
        } else {
            $this->log(
                'Bruteforce Counter would increase, but is prohibited by API',
                LoggerInterface::SEVERITY_NOTICE
            );
        }
    }

    /**
     * @return FeLoginBruteForceApi
     */
    protected function getFeLoginBruteForceApi()
    {
        if (!isset($this->feLoginBruteForceApi)) {
            $this->feLoginBruteForceApi = $this->objectManager->get(
                FeLoginBruteForceApi::class
            );
        }

        return $this->feLoginBruteForceApi;
    }

    /**
     * @param $message
     * @param $severity
     */
    private function log($message, $severity): void
    {
        $failureCount = 0;
        if ($this->hasEntry()) {
            $failureCount = $this->getEntry()
                ->getFailures();
        }
        $restricted = ($this->isClientRestricted()) ? 'Yes' : 'No';
        $additionalData = [
            'FAILURE_COUNT' => $failureCount,
            'RESTRICTED' => $restricted,
            'REMOTE_ADDR' => GeneralUtility::getIndpEnv('REMOTE_ADDR'),
            'REQUEST_URI' => GeneralUtility::getIndpEnv('REQUEST_URI'),
            'HTTP_USER_AGENT' => GeneralUtility::getIndpEnv('HTTP_USER_AGENT'),
        ];

        $this->getLogger()
            ->log($message, $severity, $additionalData, 'felogin_bruteforce_protection');
    }

    /**
     * @return Logger
     */
    private function getLogger()
    {
        if (!isset($this->logger)) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    private function createEntry(): void
    {
        $this->entry = $this->objectManager->get(Entry::class);
        $this->entry->setFailures(0);
        $this->entry->setCrdate(time());
        $this->entry->setTstamp(time());
        $this->entry->setIdentifier($this->getClientIdentifier());

        $this->entryRepository->add($this->entry);
        $this->persistenceManager->persistAll();
        $this->clientRestricted = false;
    }

    private function saveEntry(): void
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

    private function isRestricted(Entry $entry): bool
    {
        return $this->hasMaximumNumberOfFailuresReached($entry) && !$this->isRestrictionTimeReached($entry);
    }

    private function isOutdated(Entry $entry): bool
    {
        if ($this->hasMaximumNumberOfFailuresReached($entry) && $this->isRestrictionTimeReached($entry)) {
            return true;
        }

        return !$this->hasMaximumNumberOfFailuresReached($entry) && $this->isResetTimeOver($entry);
    }

    private function isResetTimeOver(Entry $entry): bool
    {
        return $entry->getCrdate() < (time() - $this->configuration->getResetTime());
    }

    private function hasMaximumNumberOfFailuresReached(Entry $entry): bool
    {
        return $entry->getFailures() >= $this->configuration->getMaximumNumberOfFailures();
    }

    private function isRestrictionTimeReached(Entry $entry): bool
    {
        return $entry->getTstamp() < (time() - $this->configuration->getRestrictionTime());
    }

    /**
     * Returns the client identifier based on the clients IP address.
     */
    private function getClientIdentifier(): string
    {
        if (!isset($this->clientIdentifier)) {
            $this->clientIdentifier = md5(
                $this->restrictionIdentifier->getIdentifierValue() . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
            );
        }

        return $this->clientIdentifier;
    }
}
