<?php
namespace Aoe\FeloginBruteforceProtection\Service;

    /***************************************************************
     *  Copyright notice
     *
     *  (c) 2013 Kevin Schu <kevin.schu@aoe.com>, AOE GmbH
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

use Aoe\FeloginBruteforceProtection\System\Configuration;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFabric;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Sv\AuthenticationService;

/**
 * @package Aoe\FeloginBruteforceProtection\\Service
 *
 * @author Kevin Schu <kevin.schu@aoe.com>
 * @author Timo Fuchs <timo.fuchs@aoe.com>
 * @author Andre Wuttig <wuttig@portrino.de>
 *
 */
class AuthUser extends AuthenticationService
{

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var RestrictionService
     */
    protected $restrictionService;

    /**
     * @var FrontendUserAuthentication
     */
    protected $frontendUserAuthentication;

    /**
     * Load extbase dependencies to use repositories and persistence.
     *
     * @return boolean TRUE if the service is available
     */
    public function init()
    {
        ExtensionManagementUtility::loadBaseTca(false);
        if (!isset($GLOBALS['TSFE']) || empty($GLOBALS['TSFE']->sys_page)) {
            $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance('t3lib_pageSelect');
        }
        if (!isset($GLOBALS['TSFE']) || empty($GLOBALS['TSFE']->tmpl)) {
            $GLOBALS['TSFE']->tmpl = GeneralUtility::makeInstance('t3lib_tstemplate');
        }

        return parent::init();
    }

    /**
     * Initialize authentication service
     *
     * @param string $mode Subtype of the service which is used to call the service.
     * @param array $loginData Submitted login form data
     * @param array $authInfo Information array. Holds submitted form data etc.
     * @param object $pObj Parent object
     * @return void
     * @todo Define visibility
     */
    public function initAuth($mode, $loginData, $authInfo, $pObj)
    {
        $this->frontendUserAuthentication = $pObj;
    }

    /**
     * Ensure chain breaking if client is already banned!
     * Simulate an invalid user and stop the chain by setting the "fetchAllUsers" configuration to "FALSE";
     *
     * @return boolean|array
     */
    public function getUser()
    {
        if ($this->isProtectionEnabled() && $this->getRestrictionService()->isClientRestricted()) {
            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']
            [$this->frontendUserAuthentication->loginType . '_fetchAllUsers'] = false;
            return array('uid' => 0);
        }
        return parent::getUser();
    }

    /**
     * Ensure chain breaking if client is already banned!
     *
     * @param mixed $userData Data of user.
     * @return integer     Chain result (<0: break chain; 100: use next chain service; 200: success)
     */
    public function authUser($userData)
    {
        if ($this->isProtectionEnabled() && $this->getRestrictionService()->isClientRestricted()) {
            return -1;
        }
        return 100;
    }

    /**
     * @return boolean
     */
    public function isProtectionEnabled()
    {
        return $this->getConfiguration()->isEnabled();
    }

    /**
     * @return RestrictionService
     */
    private function getRestrictionService()
    {
        if (false === isset ($this->restrictionService)) {
            /**
             * @var RestrictionIdentifierFabric $restrictionIdentifierFabric
             */
            $restrictionIdentifierFabric = $this->getRestrictionIdentifierFabric();
            /**
             * @var RestrictionIdentifierInterface $restrictionIdentifier
             */
            $restrictionIdentifier = $restrictionIdentifierFabric->getRestrictionIdentifier(
                $this->getConfiguration(),
                $this->frontendUserAuthentication
            );

            $this->restrictionService = $this->getObjectManager()
                ->get(
                    'Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService',
                    $restrictionIdentifier
                );
        }
        return $this->restrictionService;
    }

    /**
     * @return Configuration
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
     * @return ObjectManagerInterface
     */
    private function getObjectManager()
    {
        if (false === isset ($this->objectManager)) {
            $this->objectManager = GeneralUtility::makeInstance(
                'TYPO3\CMS\Extbase\Object\ObjectManager'
            );
        }
        return $this->objectManager;
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
}
