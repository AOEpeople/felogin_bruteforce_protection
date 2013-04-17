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

abstract class Tx_FeloginBruteforceProtection_Hooks_AbstractHook
{
    /**
     * @var null|Tx_Extbase_Object_ObjectManager
     */
    private $objectManager = NULL;

    /**
     * @var null|Tx_Extbase_Persistence_Manager
     */
    private $persistenceManager = NULL;

    /**
     * @return array
     */
    protected function getConfiguration()
    {
        return unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['felogin_bruteforce_protection']);
    }

    /**
     * @return Tx_Extbase_Object_ObjectManager
     */
    protected function getObjectManager()
    {
        if (NULL === $this->objectManager) {
            $this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
        }
        return $this->objectManager;
    }

    /**
     * @return Tx_Extbase_Persistence_Manager
     */
    protected function getPersistenceManager()
    {
        if (NULL === $this->persistenceManager) {
            $this->persistenceManager = $this->getObjectManager()->get('Tx_Extbase_Persistence_Manager');
        }
        return $this->persistenceManager;
    }
}