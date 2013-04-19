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

require_once t3lib_extMgm::extPath('sv', 'class.tx_sv_auth.php');

/**
 * @package felogin_bruteforce_protection
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_FeloginBruteforceProtection_Service_AuthUser extends tx_sv_auth
{
    /**
     * @var Tx_FeloginBruteforceProtection_Domain_Repository_Entry|null
     */
    private $entryRepository = NULL;

    /**
     * @var null|Tx_Extbase_Object_ObjectManager
     */
    private $objectManager = NULL;

    /**
     * Ensure chain breaking if client is already banned!
     *
     * @param   mixed       $userData Data of user.
     * @return  integer     Chain result (<0: break chain; 100: use next chain service; 200: success)
     */
    public function authUser($userData)
    {
        if (TRUE === $this->isClientTemporaryRestricted()) {
            return -1;
        }
        return 100;
    }

    /**
     * @return string
     */
    public static function getIdentifier()
    {
        return md5($_SERVER['REMOTE_ADDR'] . self::getEncryptionKey());
    }

    /**
     * @return bool|mixed
     */
    public function getUser()
    {
        if (TRUE === $this->isClientTemporaryRestricted()) {
            return FALSE;
        }
        return parent::getUser();
    }

    /**
     * @return string
     */
    private static function getEncryptionKey()
    {
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
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
     * @return Tx_Extbase_Object_ObjectManager
     */
    private function getObjectManager()
    {
        if (NULL === $this->objectManager) {
            $this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
        }
        return $this->objectManager;
    }

    /**
     * @return bool
     */
    public function isClientTemporaryRestricted()
    {
        $GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
        $entry = $this->getEntryRepository()->findOneByIdentifier(self::getIdentifier());
        if ($entry instanceof Tx_FeloginBruteforceProtection_Domain_Model_Entry && $entry->getFailures() >= 10) {
            return FALSE;
        }
        return TRUE;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Service/AuthUser.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/felogin_bruteforce_protection/Classes/Service/AuthUser.php']);
}