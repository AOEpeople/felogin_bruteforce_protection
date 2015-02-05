<?php

/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Kevin Schu <kevin.schu@aoe.com>, AOE GmbH
 * (c) 2014 André Wuttig <wuttig@portrino.de>, portrino GmbH
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

use TYPO3\CMS\Core as Core;
use TYPO3\CMS\Extbase\Utility as Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend as Frontend;
use \TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use Aoe\FeloginBruteforceProtection\Service\Logger;
use Aoe\FeloginBruteforceProtection\Service\FeLoginBruteForceApi\FeLoginBruteForceApi;

/**
 * @package Aoe\FeloginBruteforceProtection\\Hook\UserAuth
 *
 * @author Kevin Schu <kevin.schu@aoe.com>
 * @author Timo Fuchs <timo.fuchs@aoe.com>
 * @author Andre Wuttig <wuttig@portrino.de>
 * @author Stefan Masztalerz <stefan.masztalerz@aoe.com>
 */
class Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUp
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Aoe\FeloginBruteforceProtection\System\Configuration
     */
    protected $configuration;

    /**
     * @var \Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService
     */
    protected $restrictionService;

    /**
     * @var Logger\LoggerService
     */
    protected $loggerService;

    /**
     * @var Logger\Logger
     */
    protected $logger;

    /**
     * @var FeLoginBruteForceApi
     */
    protected $feLoginBruteForceApi;

    /**
     * @param array $params
     * @return void
     */
    public function handlePostUserLookUp(&$params)
    {
        /** @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUserAuthentication */
        $frontendUserAuthentication = $params['pObj'];

        if (!$frontendUserAuthentication instanceof \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication) {
            return;
        }

        if (!$this->getConfiguration()->isEnabled()) {
            return;
        }

        if ($this->hasFeUserLoggedIn($frontendUserAuthentication)) {
            $this->getRestrictionService()->removeEntry();
            $this->log('Bruteforce Counter removed', Logger\LoggerInterface::SEVERITY_INFO);
        } elseif ($this->hasFeUserLogInFailed($frontendUserAuthentication)) {
            $this->getRestrictionService()->incrementFailureCount();
            $this->updateGlobals($frontendUserAuthentication);

            if ($this->getRestrictionService()->isClientRestricted()) {
                $this->log('Bruteforce Counter increased', Logger\LoggerInterface::SEVERITY_WARNING);
            } else {
                $this->log('Bruteforce Counter increased', Logger\LoggerInterface::SEVERITY_NOTICE);
            }

            if ($this->getFeLoginBruteForceApi()->shouldCountWithinThisRequest()) {
                if ($this->getRestrictionService()->isClientRestricted()) {
                    $this->log('Bruteforce Protection Locked', Logger\LoggerInterface::SEVERITY_WARNING);
                } else {
                    $this->log('Bruteforce Counter increased', Logger\LoggerInterface::SEVERITY_NOTICE);
                }
            } else {
                $this->log(
                    'Bruteforce Counter would increase, but is prohibited by API',
                    Logger\LoggerInterface::SEVERITY_NOTICE
                );
            }
        }
    }

    /**
     * @param $message
     * @param $severity
     */
    private function log($message, $severity)
    {
        $failureCount=0;
        if ($this->getRestrictionService()->hasEntry()) {
            $failureCount=$this->getRestrictionService()->getEntry()->getFailures();
        }
        if ($this->getRestrictionService()->isClientRestricted()) {
            $restricted = 'Yes';
        } else {
            $restricted = 'No';
        }
        $additionalData = array(
            'FAILURE_COUNT' => $failureCount,
            'RESTRICTED' => $restricted,
            'REMOTE_ADDR' => GeneralUtility::getIndpEnv('REMOTE_ADDR'),
            'REQUEST_URI' => GeneralUtility::getIndpEnv('REQUEST_URI'),
            'HTTP_USER_AGENT' => GeneralUtility::getIndpEnv('HTTP_USER_AGENT')
        );

        $this->getLogger()->log($message, $severity, $additionalData, 'felogin_bruteforce_protection');
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
        return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
            'restriction_message',
            'felogin_bruteforce_protection',
            array($time, $time)
        );
    }

    /**
     * @param Core\Authentication\AbstractUserAuthentication $userAuthObject
     * @return boolean
     */
    private function hasFeUserLoggedIn(Core\Authentication\AbstractUserAuthentication $userAuthObject)
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
     * @param Core\Authentication\AbstractUserAuthentication $userAuthObject
     * @return boolean
     */
    private function hasFeUserLogInFailed(Core\Authentication\AbstractUserAuthentication $userAuthObject)
    {
        if ($userAuthObject->loginType === 'FE' && $userAuthObject->loginFailure === true && !$userAuthObject->user) {
            return true;
        }
        return false;
    }

    /**
     * @return Logger\LoggerService
     */
    private function getLoggerService()
    {
        if (!isset($this->loggerService)) {
            $this->loggerService = new Logger\LoggerService();
        }
        return $this->loggerService;
    }

    /**
     * @return Logger\Logger
     */
    private function getLogger()
    {
        if (!isset($this->logger)) {
            $this->logger = new Logger\Logger();
        }
        return $this->logger;
    }

    /**
     * @return \Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService
     */
    private function getRestrictionService()
    {
        if (false === isset ($this->restrictionService)) {
            $this->restrictionService = $this->getObjectManager()
                ->get('Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService');
        }
        return $this->restrictionService;
    }

    /**
     * @return \Aoe\FeloginBruteforceProtection\System\Configuration
     */
    protected function getConfiguration()
    {
        if (false === isset ($this->configuration)) {
            $this->configuration = $this->getObjectManager()
                ->get('Aoe\FeloginBruteforceProtection\System\Configuration');
        }
        return $this->configuration;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    private function getObjectManager()
    {
        if (false === isset ($this->objectManager)) {
            $this->objectManager = Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        }
        return $this->objectManager;
    }

    /**
     * @return FeLoginBruteForceApi
     */
    protected function getFeLoginBruteForceApi()
    {
        if (!isset ($this->feLoginBruteForceApi)) {
            $this->feLoginBruteForceApi = $this->getObjectManager()->get(
                'Aoe\FeloginBruteforceProtection\Service\FeLoginBruteForceApi\FeLoginBruteForceApi'
            );
        }
        return $this->feLoginBruteForceApi;
    }
}

