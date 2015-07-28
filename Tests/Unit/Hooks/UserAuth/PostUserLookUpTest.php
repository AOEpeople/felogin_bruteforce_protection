<?php
namespace Aoe\FeloginBruteforceProtection\Tests\Unit\Hooks\UserAuth;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 AOE GmbH <dev@aoe.com>
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

use Aoe\FeloginBruteforceProtection\Hooks\UserAuth\PostUserLookUp;
use Aoe\FeloginBruteforceProtection\System\Configuration;
use \TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Test case for class Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUp.
 *
 * @package TYPO3
 * @subpackage brute force protection
 *
 */
class PostUserLookUpTest extends UnitTestCase
{
    /**
     * @var PostUserLookUp
     */
    private $postUserLookUp;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->postUserLookUp = $this->getMock(
            'Aoe\\FeloginBruteforceProtection\\Hooks\\UserAuth\\PostUserLookUp',
            array('getConfiguration'),
            array(),
            '',
            false
        );
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        unset($this->postUserLookUp);
    }

//    /**
//     * with backend login
//     * @test
//     */
//    public function handlePostUserLookUpWithBackendLogin()
//    {
//        $this->runHandlePostUserLookUp('BE', 'TESTLogin');
//
//        $this->assertFalse(
//            $GLOBALS['felogin_bruteforce_protection']['restricted'],
//            'TEST:'. print_r($GLOBALS['felogin_bruteforce_protection'], true)
//        );
//    }

    /**
     * @test
     */
    public function handlePostUserLookUpWithFrontendLogin()
    {
        $this->runHandlePostUserLookUp('FE', 'TESTLogin');

        $this->assertFalse(
            $GLOBALS['felogin_bruteforce_protection']['restricted'],
            'TEST:'. print_r($GLOBALS['felogin_bruteforce_protection'], true)
        );
    }

    /**
     * @param string $loginType
     * @param string $uname
     */
    protected function runHandlePostUserLookUp($loginType, $uname)
    {
        // Setup Configuration
        $config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['felogin_bruteforce_protection']);
        $config[Configuration::CONF_IDENTIFICATION_IDENTIFIER] = 2;
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['felogin_bruteforce_protection'] = serialize($config);
        $configuration = new Configuration();
        $this->postUserLookUp->expects($this->any())->method('getConfiguration')->will($this->returnValue($configuration));

        // Setup FrontendUserAuthentication
        $feUserAuth = $this->getMock('\\TYPO3\\CMS\\Frontend\\Authentication\\FrontendUserAuthentication');
        $loginFormData['uname'] = $uname;
        $feUserAuth->expects($this->any())->method('getLoginFormData')->will(
            $this->returnValue($loginFormData)
        );
        $feUserAuth->loginType = $loginType;
        $feUserAuth->loginFailure = true;
        $params = array();
        $params['pObj'] = $feUserAuth;

        /**
         * @todo RestrictionService dependencies need to be injected
         */
        //$this->postUserLookUp->handlePostUserLookUp($params);
    }
}
