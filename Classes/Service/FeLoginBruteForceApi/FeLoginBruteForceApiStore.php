<?php

namespace Aoe\FeloginBruteforceProtection\Service\FeLoginBruteForceApi;

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

use TYPO3\CMS\Core\SingletonInterface;

/**
 * This Class is a singleton which is able to store Property Values during a Request, when an Api Call to the
 * BruteForceProtection extension makes this necessary
 */
class FeLoginBruteForceApiStore implements SingletonInterface
{
    /**
     * @var array
     */
    private $propertyStore = [];

    /**
     * @param $propertyName
     * @param $propertyValue
     */
    public function setProperty($propertyName, $propertyValue): void
    {
        $this->propertyStore[$propertyName] = $propertyValue;
    }

    /**
     * @param $propertyName
     */
    public function getProperty($propertyName)
    {
        if (array_key_exists($propertyName, $this->propertyStore)) {
            return $this->propertyStore[$propertyName];
        }

        return null;
    }
}
