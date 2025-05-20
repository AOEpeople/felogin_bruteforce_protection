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

class RestrictionService
{
    protected static bool $preventFailureCount = false;

    protected RestrictionIdentifierInterface $restrictionIdentifier;

    protected string $clientIdentifier;

    protected Configuration $configuration;

    protected EntryRepository $entryRepository;

    protected Entry $entry;

    protected bool $clientRestricted;

    protected Logger $logger;

    protected FeLoginBruteForceApi $feLoginBruteForceApi;

    public function __construct(RestrictionIdentifierInterface $restrictionIdentifier)
    {
        $this->restrictionIdentifier = $restrictionIdentifier;

        $this->configuration = GeneralUtility::makeInstance(Configuration::class);
        $this->entryRepository = GeneralUtility::makeInstance(EntryRepository::class);
        $this->feLoginBruteForceApi = GeneralUtility::makeInstance(FeLoginBruteForceApi::class);
    }

    public static function setPreventFailureCount(bool $preventFailureCount): void
    {
        self::$preventFailureCount = $preventFailureCount;
    }

    public function isClientRestricted(): bool
    {
        if (!isset($this->clientRestricted)) {
            $this->clientRestricted = $this->hasEntry() && $this->getEntry() !== null && $this->isRestricted($this->getEntry());
        }

        return $this->clientRestricted;
    }

    public function removeEntry(): void
    {
        if ($this->hasEntry()) {
            $this->entryRepository->removeEntry($this->entry);

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

        if ($this->getEntry() !== null && $this->hasMaximumNumberOfFailuresReached($this->getEntry())) {
            return;
        }

        $this->entry->increaseFailures();
        $this->updateEntry();

        $this->restrictionLog();
    }

    public function hasEntry(): bool
    {
        return $this->getEntry() instanceof Entry;
    }

    public function getEntry(): ?Entry
    {
        if (!isset($this->entry)) {
            $entry = $this->entryRepository->findOneEntryByIdentifier($this->getClientIdentifier());
            if ($entry instanceof Entry) {
                $this->entry = $entry;
                if ($this->isOutdated($entry)) {
                    $this->removeEntry();
                }
            }
        }

        return $this->entry ?? null;
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

    protected function getFeLoginBruteForceApi(): FeLoginBruteForceApi
    {
        return $this->feLoginBruteForceApi;
    }

    private function log(string $message, int $severity): void
    {
        $failureCount = 0;
        if ($this->hasEntry() && $this->getEntry() !== null) {
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

    private function getLogger(): Logger
    {
        if (!isset($this->logger)) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    private function createEntry(): void
    {
        $this->entry = GeneralUtility::makeInstance(Entry::class);
        $this->entry->setFailures(0);
        $this->entry->setCrdate(time());
        $this->entry->setTstamp(time());
        $this->entry->setIdentifier($this->getClientIdentifier());

        $this->entryRepository->createEntry($this->entry);
        $this->clientRestricted = false;
    }

    private function updateEntry(): void
    {
        if ($this->entry->getFailures() > 0) {
            $this->entry->setTstamp(time());
        }

        $this->entryRepository->updateEntry($this->entry);
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
