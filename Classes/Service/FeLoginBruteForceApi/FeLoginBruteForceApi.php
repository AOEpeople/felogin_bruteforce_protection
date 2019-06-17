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

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Class FeLoginBruteForceApi
 * @package Aoe\FeloginBruteforceProtection\Service\FeLoginBruteForceApi
 */
class FeLoginBruteForceApi implements FeLoginBruteForceApiInterface
{
    /** @var FeLoginBruteForceApiStore */
    protected $apiStore;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /**
     * @param null $apiStore
     */
    public function __construct($apiStore = null)
    {
        if (isset($apiStore)) {
            $this->apiStore = $apiStore;
        } else {
            $this->apiStore = $this->getObjectManager()->get(
                'Aoe\FeloginBruteforceProtection\Service\FeLoginBruteForceApi\FeLoginBruteForceApiStore'
            );
        }
    }

    /**
     * @return void
     */
    public function stopCountWithinThisRequest()
    {
        $this->apiStore->setProperty('stopCountWithinThisRequest', true);
    }

    /**
     * @return boolean
     */
    public function shouldCountWithinThisRequest()
    {
        return $this->apiStore->getProperty('stopCountWithinThisRequest') !== true;
    }

    /**
     * @return ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        if (false === isset($this->objectManager)) {
            $this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        }
        return $this->objectManager;
    }
}
