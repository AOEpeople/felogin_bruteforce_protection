<?php

namespace Aoe\FeloginBruteforceProtection\Hooks\UserAuth;

/***************************************************************
 * Copyright notice
 *
 * (c) 2019 AOE GmbH, <dev@aoe.com>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFabric;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierInterface;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Class PostUserLookUp
 *
 * @package Aoe\FeloginBruteforceProtection\Hooks\UserAuth
 */
class PostUserLookUp
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var RestrictionService
     */
    protected $restrictionService;

    /**
     * @var RestrictionIdentifierInterface
     */
    protected $restrictionIdentifier;

    /**
     * @var FrontendUserAuthentication
     */
    protected $frontendUserAuthentication;

    /**
     * @param array $params
     */
    public function handlePostUserLookUp(&$params): void
    {
        /** @var FrontendUserAuthentication $frontendUserAuthentication */
        $frontendUserAuthentication = $params['pObj'];

        // Continue only if the user is in front-end
        if (!$this->isUserInFrontEnd($frontendUserAuthentication)) {
            return;
        }

        $this->setFrontendUserAuthentication($frontendUserAuthentication);

        // Continue only if the protection is enabled
        if ($this->getConfiguration()->isEnabled()) {
            /** @var RestrictionIdentifierFabric $restrictionIdentifierFabric */
            $restrictionIdentifierFabric = $this->getRestrictionIdentifierFabric();
            $this->restrictionIdentifier = $restrictionIdentifierFabric->getRestrictionIdentifier(
                $this->getConfiguration(),
                $frontendUserAuthentication
            );
            $this->restrictionService = $this->initRestrictionService();

            if ($this->restrictionIdentifier->checkPreconditions()) {
                if ($this->hasFeUserLoggedIn($this->frontendUserAuthentication)) {
                    $this->restrictionService
                        ->removeEntry();
                } elseif ($this->hasFeUserLogInFailed($this->frontendUserAuthentication)) {
                    $this->restrictionService
                        ->checkAndHandleRestriction();
                    $this->updateGlobals($this->frontendUserAuthentication);
                }
            }
        }
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        if (!isset($this->configuration)) {
            $this->configuration = GeneralUtility::makeInstance(Configuration::class);
        }

        return $this->configuration;
    }

    protected function setFrontendUserAuthentication(FrontendUserAuthentication $frontendUserAuthentication): void
    {
        $this->frontendUserAuthentication = $frontendUserAuthentication;
    }

    /**
     * @return RestrictionIdentifierFabric
     */
    protected function getRestrictionIdentifierFabric()
    {
        return GeneralUtility::makeInstance(RestrictionIdentifierFabric::class);
    }

    /**
     * @return RestrictionService
     */
    protected function initRestrictionService()
    {
        return GeneralUtility::makeInstance(RestrictionService::class, $this->restrictionIdentifier);
    }

    /**
     * Check if the user is in front end
     */
    private function isUserInFrontEnd(AbstractUserAuthentication $userAuthentication): bool
    {
        return $userAuthentication instanceof FrontendUserAuthentication;
    }

    private function updateGlobals(FrontendUserAuthentication $userAuthObject): bool
    {
        $GLOBALS['felogin_bruteforce_protection']['restricted'] = false;
        if ($this->restrictionService->isClientRestricted()) {
            $GLOBALS['felogin_bruteforce_protection']['restricted'] = true;
            $GLOBALS['felogin_bruteforce_protection']['restriction_time'] = $this->getConfiguration()->getRestrictionTime();
            $GLOBALS['felogin_bruteforce_protection']['restriction_message'] = $this->getRestrictionMessage();

            return false;
        }

        return true;
    }

    private function getRestrictionMessage(): string
    {
        $time = (int) ($this->getConfiguration()->getRestrictionTime() / 60);

        return LocalizationUtility::translate(
            'restriction_message',
            'felogin_bruteforce_protection',
            [$time, $time]
        );
    }

    private function hasFeUserLoggedIn(AbstractUserAuthentication $userAuthObject): bool
    {
        return $userAuthObject->loginType === 'FE' &&
            is_array($userAuthObject->user) &&
            $userAuthObject->loginSessionStarted;
    }

    private function hasFeUserLogInFailed(AbstractUserAuthentication $userAuthObject): bool
    {
        return $userAuthObject->loginType === 'FE' && !$userAuthObject->user;
    }
}
