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
class Tx_FeloginBruteforceProtection_Domain_Repository_Entry extends Tx_Extbase_Persistence_Repository
{
    /**
     * @param int $uid
     * @return object
     */
    public function findByUid($uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        $query->getQuerySettings()->setRespectStoragePage(FALSE);
        $query->getQuerySettings()->setRespectEnableFields(FALSE);
        $query->matching($query->equals('uid', $uid));
        return $query->execute()->getFirst();
    }

    /**
     * @param $seconds
     * @return void
     */
    public function removeEntriesOlderThan($seconds)
    {
        $age = (int)time() - $seconds;
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectSysLanguage(FALSE);
        $query->getQuerySettings()->setRespectStoragePage(FALSE);
        $query->getQuerySettings()->setRespectEnableFields(FALSE);
        $query->matching($query->lessThan('crdate', $age));
        foreach ($query->execute() as $object) {
            $this->removedObjects->attach($object);
            $this->addedObjects->detach($object);
        }
    }
}