<?php

namespace Aoe\FeloginBruteforceProtection\Service;

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

use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierFabric;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionIdentifierInterface;
use Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use stdClass;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Authentication\AuthenticationService;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

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
     * returns TRUE if the service is available
     */
    public function init(): bool
    {
        $loginTypePost = GeneralUtility::_POST('logintype');
        #$value = $request->getParsedBody()['tx_scheduler']);

        if ($loginTypePost != 'login') {
            return parent::init();
        }

        ExtensionManagementUtility::loadBaseTca(false);
        if (!isset($GLOBALS['TSFE'])) {
            $GLOBALS['TSFE'] = new stdClass();
        }

        if (empty($GLOBALS['TSFE']->sys_page)) {
            $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(PageRepository::class);
        }

        if (empty($GLOBALS['TSFE']->tmpl)) {
            #$fullTypoScript = $request()->getAttribute('frontend.typoscript')->getSetupArray();
            $GLOBALS['TSFE']->tmpl = GeneralUtility::makeInstance(TemplateService::class);
        }

        return parent::init();
    }

    /**
     * Initialize authentication service
     *
     * @param string $mode Subtype of the service which is used to call the service.
     * @param array $loginData Submitted login form data
     * @param array $authInfo Information array. Holds submitted form data etc.
     * @param AbstractUserAuthentication $pObj Parent object
     * @todo Define visibility
     */
    public function initAuth($mode, $loginData, $authInfo, $pObj): void
    {
        $this->frontendUserAuthentication = $pObj;
        parent::initAuth($mode, $loginData, $authInfo, $pObj);
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
            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup'][$this->frontendUserAuthentication->loginType . '_fetchAllUsers'] = false;
            return [
                'uid' => 0,
                'username' => '',
            ];
        }

        return parent::getUser();
    }

    /**
     * Ensure chain breaking if client is already banned!
     *
     * @param array $userData Data of user.
     *
     * @return int Chain result (<0: break chain; 100: use next chain service; 200: success)
     */
    public function authUser(array $userData): int
    {
        if ($this->isProtectionEnabled() && $this->getRestrictionService()->isClientRestricted()) {
            return -1;
        }

        return 100;
    }

    public function isProtectionEnabled(): bool
    {
        return $this->getConfiguration()
            ->isEnabled();
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

    /**
     * @return RestrictionIdentifierFabric
     */
    protected function getRestrictionIdentifierFabric(): object
    {
        return GeneralUtility::makeInstance(RestrictionIdentifierFabric::class);
    }

    /**
     * @return RestrictionService
     */
    private function getRestrictionService(): object
    {
        if (!isset($this->restrictionService)) {
            /** @var RestrictionIdentifierFabric $restrictionIdentifierFabric */
            $restrictionIdentifierFabric = $this->getRestrictionIdentifierFabric();
            /** @var RestrictionIdentifierInterface $restrictionIdentifier */
            $restrictionIdentifier = $restrictionIdentifierFabric->getRestrictionIdentifier(
                $this->getConfiguration(),
                $this->frontendUserAuthentication
            );

            $this->restrictionService = GeneralUtility::makeInstance(RestrictionService::class, $restrictionIdentifier);
        }

        return $this->restrictionService;
    }
}
