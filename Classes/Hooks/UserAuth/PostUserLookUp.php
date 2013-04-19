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
    const CONF_MAX_FAILURES = 'max_failures';

    /**
     * @var string
     */
    const CONF_SECONDS_TILL_RESET = 'seconds_till_reset';

    /**
     * @var Tx_FeloginBruteforceProtection_Domain_Repository_Entry
     */
    private $entryRepository = NULL;

    /**
     * @var Tx_FeloginBruteforceProtection_Domain_Model_Entry
     */
    private $currentEntry = NULL;

    /**
     * @var object
     */
    private $userAuthObject = NULL;

    /**
     * @param $params
     * @return void
     */
    public function handlePostUserLookUp(&$params)
    {
        $this->userAuthObject = $params['pObj'];
        if ($this->userAuthObject->loginType === 'FE') {
            if(FALSE === ($GLOBALS['TSFE']->sys_page instanceof t3lib_pageSelect)) {
                $GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
            }
            $this->cleanUpEntries();
            if ($this->userAuthObject->loginFailure == 1) {
                $this->rememberFailedLogin();
            }
            $this->validate();
        }
    }

    /**
     * @return void
     */
    private function cleanUpEntries()
    {
        $this->getEntryRepository()->removeEntriesOlderThan($this->getConfiguration(self::CONF_SECONDS_TILL_RESET));
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
    private function getRestrictionMessage()
    {
        return Tx_Extbase_Utility_Localization::translate('restriction_message', 'felogin_bruteforce_protection', array(
            (int)($this->getConfiguration(self::CONF_SECONDS_TILL_RESET) / 60)
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
        if ($this->getEntryForCurrentClient()->getFailures() < $this->getConfiguration(self::CONF_MAX_FAILURES)) {
			$this->getEntryForCurrentClient()->increaseFailures();
			$this->getEntryForCurrentClient()->setTstamp(time());
            $this->getEntryRepository()->add($this->getEntryForCurrentClient()); // Need to use "add", "update" does not work...
            $this->getPersistenceManager()->persistAll();
        }
    }

    /**
     * @return void
     */
    private function validate()
    {
		$GLOBALS['felogin_bruteforce_protection']['restricted'] = FALSE;
        if ($this->hasEntryForCurrentClient() && $this->getEntryForCurrentClient()->getFailures() >= $this->getConfiguration(self::CONF_MAX_FAILURES)) {
            $this->userAuthObject->loginFailure = 1;
            $GLOBALS['felogin_bruteforce_protection']['restricted'] = TRUE;
            $GLOBALS['felogin_bruteforce_protection']['restriction_message'] = $this->getRestrictionMessage();
        }
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Hooks/UserAuth/PostUserLookUp.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Hooks/UserAuth/PostUserLookUp.php']);
}