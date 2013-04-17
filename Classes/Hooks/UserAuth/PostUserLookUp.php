<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Kevin Schu <kevin.schu@aoemedia.de>, AOE media GmbH
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
 * @package felogin_bruteforce_protection
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUp extends Tx_FeloginBruteforceProtection_Hooks_AbstractHook
{
    /**
     * @var string
     */
    const ERROR_MAX_LOGIN_FAILURES = 'error_max_login_failures';

    /**
     * @var Tx_FeloginBruteforceProtection_Domain_Repository_Entry|null
     */
    private $entryRepository = NULL;

    /**
     * @var Tx_FeloginBruteforceProtection_Domain_Model_Entry|null
     */
    private $currentEntry = NULL;

    /**
     * @var object
     */
    private $userAuthObject = NULL;

    /**
     * @param $pObj
     * @return void
     */
    public function handlePostUserLookUp(&$pObj)
    {
        $this->userAuthObject = $pObj['pObj'];
        $GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
        if ($this->userAuthObject->loginType == 'FE') {
            //$this->cleanUpEntries();
            if ($this->userAuthObject->loginFailure == 1) {
                $this->rememberFailedLogin();
            }
            $this->validate($pObj);
        }
    }

    /**
     * @return void
     */
    private function cleanUpEntries()
    {
        $this->getEntryRepository()->removeEntriesOlderThan(300);
        $this->getPersistenceManager()->persistAll();
    }

    /**
     * @return Tx_FeloginBruteforceProtection_Domain_Model_Entry
     */
    private function getEntryForCurrentClient()
    {
        if (NULL === $this->currentEntry) {
            $entry = $this->getEntryRepository()->findOneByIdentifier(Tx_FeloginBruteforceProtection_Service_AuthUser::getIdentifier());
            if (FALSE === ($entry instanceof Tx_FeloginBruteforceProtection_Domain_Model_Entry)) {
                $time = time();
                /** @var $entry Tx_FeloginBruteforceProtection_Domain_Model_Entry */
                $entry = $this->getObjectManager()->get('Tx_FeloginBruteforceProtection_Domain_Model_Entry');
                $entry->setFailures(0);
                $entry->setCrdate($time);
                $entry->setTstamp($time);
                $entry->setIdentifier(Tx_FeloginBruteforceProtection_Service_AuthUser::getIdentifier());
                $this->getEntryRepository()->add($entry);
                $this->getPersistenceManager()->persistAll();
            }
            $this->currentEntry = $entry;
        }

        return $this->currentEntry;
    }

    /**
     * @return Tx_FeloginBruteforceProtection_Domain_Repository_Entry
     */
    private function getEntryRepository()
    {
        if (NULL === $this->entryRepository) {
            $this->entryRepository = $this->getObjectManager()->get('Tx_FeloginBruteforceProtection_Domain_Repository_Entry');
        }
        return $this->entryRepository;
    }

    /**
     * @return string
     */
    private function getMaxLoginFailuresErrorMessage()
    {
        return Tx_Extbase_Utility_Localization::translate(self::ERROR_MAX_LOGIN_FAILURES, 'felogin_bruteforce_protection', array(
            (int)(300 / 60)
        ));
    }

    /**
     * @return bool
     */
    private function hasEntryForCurrentClient()
    {
        $entry = $this->getEntryRepository()->findOneByIdentifier(Tx_FeloginBruteforceProtection_Service_AuthUser::getIdentifier());
        if ($entry instanceof Tx_FeloginBruteforceProtection_Domain_Model_Entry) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return void
     */
    private function rememberFailedLogin()
    {
        $entry = $this->getEntryForCurrentClient();
        if ($entry->getFailures() < 10) {
            $entry->increaseFailures();
            $entry->setTstamp(time());
            $this->currentEntry = $entry;
            $this->getEntryRepository()->add($entry); // Need to use "add", "update" does not work...
            $this->getPersistenceManager()->persistAll();
        }
    }

    /**
     * @return void
     */
    private function validate()
    {
        if ($this->hasEntryForCurrentClient() && $this->getEntryForCurrentClient()->getFailures() >= 10) {
            $this->userAuthObject->loginFailure = 1;
            $GLOBALS['felogin_bruteforce_protection']['errors'][self::ERROR_MAX_LOGIN_FAILURES] = $this->getMaxLoginFailuresErrorMessage();
        }
    }
}