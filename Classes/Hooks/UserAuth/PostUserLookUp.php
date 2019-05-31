<?php
namespace Aoe\FeloginBruteforceProtection\Hooks\UserAuth;

/***************************************************************
 * Copyright notice
 *
 * (c) 2018 AOE GmbH, <dev@aoe.com>
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFabric;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierInterface;

class PostUserLookUp
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

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
     * @return void
     */
    public function handlePostUserLookUp(&$params)
    {
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUserAuthentication */
        $frontendUserAuthentication = $params['pObj'];

        // Continue only if the user is in front-end
        if (false === $this->isUserInFrontEnd($frontendUserAuthentication)) {
            return;
        } else {
            $this->setFrontendUserAuthentication($frontendUserAuthentication);
        }

        // Continue only if the protection is enabled
        if ($this->getConfiguration()->isEnabled()) {
            /**
             * @var RestrictionIdentifierFabric $restrictionIdentifierFabric
             */
            $restrictionIdentifierFabric = $this->getRestrictionIdentifierFabric();
            $this->restrictionIdentifier = $restrictionIdentifierFabric->getRestrictionIdentifier(
                $this->getConfiguration(),
                $frontendUserAuthentication
            );
            $this->restrictionService = $this->initRestrictionService();

            if ($this->restrictionIdentifier->checkPreconditions()) {
                if ($this->hasFeUserLoggedIn($this->getFrontendUserAuthentication())) {
                    $this->getRestrictionService()->removeEntry();

                } elseif ($this->hasFeUserLogInFailed($this->getFrontendUserAuthentication())) {
                    $this->getRestrictionService()->checkAndHandleRestriction();
                    $this->updateGlobals($this->getFrontendUserAuthentication());
                }
            }
        }
    }

    /**
     * Check if the user is in front end
     *
     * @param AbstractUserAuthentication $userAuthentication
     * @return boolean
     */
    private function isUserInFrontEnd(AbstractUserAuthentication $userAuthentication)
    {
        return $userAuthentication instanceof FrontendUserAuthentication;
    }

    /**
     * @param $userAuthObject
     * @return boolean
     */
    private function updateGlobals(&$userAuthObject)
    {
        $GLOBALS ['felogin_bruteforce_protection'] ['restricted'] =
            false;
        if ($this->getRestrictionService()->isClientRestricted()) {
            $userAuthObject->loginFailure = 1;
            $GLOBALS ['felogin_bruteforce_protection'] ['restricted'] =
                true;
            $GLOBALS ['felogin_bruteforce_protection'] ['restriction_time'] =
                $this->getConfiguration()->getRestrictionTime();
            $GLOBALS ['felogin_bruteforce_protection'] ['restriction_message'] =
                $this->getRestrictionMessage();
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    private function getRestrictionMessage()
    {
        $time = (integer)($this->getConfiguration()->getRestrictionTime() / 60);
        return LocalizationUtility::translate(
            'restriction_message',
            'felogin_bruteforce_protection',
            array($time, $time)
        );
    }

    /**
     * @param AbstractUserAuthentication $userAuthObject
     * @return boolean
     */
    private function hasFeUserLoggedIn(AbstractUserAuthentication $userAuthObject)
    {
        if ($userAuthObject->loginType === 'FE' &&
            $userAuthObject->loginFailure === false &&
            is_array($userAuthObject->user) &&
            $userAuthObject->loginSessionStarted === true
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param AbstractUserAuthentication $userAuthObject
     * @return boolean
     */
    private function hasFeUserLogInFailed(AbstractUserAuthentication $userAuthObject)
    {
        if ($userAuthObject->loginType === 'FE' && $userAuthObject->loginFailure === true && !$userAuthObject->user) {
            return true;
        }
        return false;
    }

    /**
     * @return RestrictionService
     */
    private function getRestrictionService()
    {
        return $this->restrictionService;
    }

    /**
     * @return \Aoe\FeloginBruteforceProtection\System\Configuration
     */
    protected function getConfiguration()
    {
        if (false === isset($this->configuration)) {
            $this->configuration = $this->getObjectManager()
                ->get('Aoe\FeloginBruteforceProtection\System\Configuration');
        }
        return $this->configuration;
    }

    /**
     * @return FrontendUserAuthentication
     */
    protected function getFrontendUserAuthentication()
    {
        return $this->frontendUserAuthentication;
    }

    /**
     * @param FrontendUserAuthentication $frontendUserAuthentication
     */
    protected function setFrontendUserAuthentication(FrontendUserAuthentication $frontendUserAuthentication)
    {
        $this->frontendUserAuthentication = $frontendUserAuthentication;
    }

    /**
     * @return RestrictionIdentifierFabric
     */
    protected function getRestrictionIdentifierFabric()
    {
        return $this->getObjectManager()
            ->get(
                'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFabric'
            );
    }

    /**
     * @return RestrictionService
     */
    protected function initRestrictionService()
    {
        return $this->getObjectManager()
            ->get(
                'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService',
                $this->restrictionIdentifier
            );
    }

    /**
     * @return ObjectManagerInterface
     */
    private function getObjectManager()
    {
        if (false === isset($this->objectManager)) {
            $this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        }
        return $this->objectManager;
    }
}
