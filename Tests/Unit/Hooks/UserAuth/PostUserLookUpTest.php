<?php
/**
 * Test case for class Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUp.
 *
 * @package TYPO3
 * @subpackage brute force protection
 *
 */
class Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUpTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUp
	 */
	private $postUserLookUp;
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	public function setUp() {
		$this->postUserLookUp = new Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUp ();
	}
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	public function tearDown() {
		unset ( $this->postUserLookUp );
	}
	/**
	 * with backend login
	 * @test
	 */
	public function handlePostUserLookUpWithBackendLogin() {
		$feUserAuth = $this->getMock('tslib_feUserAuth');
		$feUserAuth->loginType='BE';
		$params = array();
		$params['pObj'] = $feUserAuth;
		$this->postUserLookUp->handlePostUserLookUp($params);
	}
}