<?php
/**
 * Test case for class Tx_FeloginBruteforceProtection_Domain_Service_Restriction.
 *
 * @package TYPO3
 * @subpackage brute force protection
 *
 */
class Tx_FeloginBruteforceProtection_Domain_Service_RestrictionTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_FeloginBruteforceProtection_Domain_Service_Restriction
	 */
	private $restriction;
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	public function setUp() {
		$this->restriction = new Tx_FeloginBruteforceProtection_Domain_Service_Restriction ();
		$this->restriction->injectPersistenceManager($this->getMock('Tx_Extbase_Persistence_Manager'));
		
		
	}
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	public function tearDown() {
		unset ( $this->restriction );
	}
	/**
	 * @test
	 */
	public function isClientRestricted() {
		$configuration = $this->getMock('Tx_FeloginBruteforceProtection_System_Configuration');
		$configuration->expects($this->any())->method('getMaximumNumerOfFailures')->will($this->returnValue(10));
		$configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
		$configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));
		$entryRepository = $this->getMock('Tx_FeloginBruteforceProtection_Domain_Repository_Entry');
		$entry = $this->getMock('Tx_FeloginBruteforceProtection_Domain_Model_Entry');
		$entry->expects($this->any())->method('getFailures')->will($this->returnValue(0));
		$entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time()-400));
		$entryRepository->expects($this->any())->method('findOneByIdentifier')->will($this->returnValue($entry));
		$this->restriction->injectEntryRepository($entryRepository);
		$this->restriction->injectConfiguration($configuration);
		$this->assertFalse($this->restriction->isClientRestricted());
	}
	/**
	 * @test
	 */
	public function isClientRestrictedWithFailures() {
		$configuration = $this->getMock('Tx_FeloginBruteforceProtection_System_Configuration',array(),array(),'',FALSE);
		$configuration->expects($this->any())->method('getMaximumNumerOfFailures')->will($this->returnValue(10));
		$configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
		$configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));
		$entry = $this->getMock('Tx_FeloginBruteforceProtection_Domain_Model_Entry',array(),array(),'',FALSE);
		$entry->expects($this->any())->method('getFailures')->will($this->returnValue(10));
		$entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time()-400));
		$entry->expects($this->any())->method('getTstamp')->will($this->returnValue(time()-400));
		$entryRepository = $this->getMock('Tx_FeloginBruteforceProtection_Domain_Repository_Entry',array('findOneByIdentifier','remove'),array(),'',FALSE);
		$entryRepository->expects ( $this->any () )->method ( 'findOneByIdentifier' )->will ( $this->returnValue ( $entry) );
		$this->restriction->injectEntryRepository($entryRepository);
		$this->restriction->injectConfiguration($configuration);
		$this->assertTrue($this->restriction->isClientRestricted());
	}
	/**
	 * @test
	 */
	public function isClientRestrictedWithFailuresAndTimeout() {
		$configuration = $this->getMock('Tx_FeloginBruteforceProtection_System_Configuration',array(),array(),'',FALSE);
		$configuration->expects($this->any())->method('getMaximumNumerOfFailures')->will($this->returnValue(10));
		$configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
		$configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));
		$entry = $this->getMock('Tx_FeloginBruteforceProtection_Domain_Model_Entry',array(),array(),'',FALSE);
		$entry->expects($this->any())->method('getFailures')->will($this->returnValue(10));
		$entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time()-400));
		$entry->expects($this->any())->method('getTstamp')->will($this->returnValue(time()-4000));
		$entryRepository = $this->getMock('Tx_FeloginBruteforceProtection_Domain_Repository_Entry',array('findOneByIdentifier','remove'),array(),'',FALSE);
		$entryRepository->expects ( $this->any () )->method ( 'findOneByIdentifier' )->will ( $this->returnValue ( $entry) );
		$this->restriction->injectEntryRepository($entryRepository);
		$this->restriction->injectConfiguration($configuration);
		$this->assertFalse($this->restriction->isClientRestricted());
	}
/**
	 * @test
	 */
	public function isClientRestrictedWithLessFailures() {
		$configuration = $this->getMock('Tx_FeloginBruteforceProtection_System_Configuration',array(),array(),'',FALSE);
		$configuration->expects($this->any())->method('getMaximumNumerOfFailures')->will($this->returnValue(10));
		$configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
		$configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));
		$entry = $this->getMock('Tx_FeloginBruteforceProtection_Domain_Model_Entry',array(),array(),'',FALSE);
		$entry->expects($this->any())->method('getFailures')->will($this->returnValue(5));
		$entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time()-400));
		$entry->expects($this->any())->method('getTstamp')->will($this->returnValue(time()-400));
		$entryRepository = $this->getMock('Tx_FeloginBruteforceProtection_Domain_Repository_Entry',array('findOneByIdentifier','remove'),array(),'',FALSE);
		$entryRepository->expects ( $this->any () )->method ( 'findOneByIdentifier' )->will ( $this->returnValue ( $entry) );
		$this->restriction->injectEntryRepository($entryRepository);
		$this->restriction->injectConfiguration($configuration);
		$this->assertFalse($this->restriction->isClientRestricted());
	}
}