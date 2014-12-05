<?php

/**
 * Test case for class Tx_FeloginBruteforceProtection_Domain_Service_Restriction.
 *
 * @package TYPO3
 * @subpackage brute force protection
 *
 */
class Tx_FeloginBruteforceProtection_Domain_Service_RestrictionTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService
     */
    private $restriction;

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->restriction = new Aoe\FeloginBruteforceProtection\Domain\Service\RestrictionService();
        $this->inject(
            $this->restriction,
            'persistenceManager',
            $this->getMock('\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager')
        );
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        unset ($this->restriction);
    }

    /**
     * @test
     */
    public function isClientRestricted()
    {
        $configuration = $this->getMock('Aoe\FeloginBruteforceProtection\System\Configuration');
        $configuration->expects($this->any())->method('getMaximumNumerOfFailures')->will($this->returnValue(10));
        $configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
        $configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));
        $entryRepository = $this->getMock(
            'Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository',
            array('findOneByIdentifier', 'remove'),
            array(),
            '',
            false
        );
        $entry = $this->getMock('Aoe\FeloginBruteforceProtection\Domain\Model\Entry');
        $entry->expects($this->any())->method('getFailures')->will($this->returnValue(0));
        $entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time() - 400));
        $entryRepository->expects($this->any())->method('findOneByIdentifier')->will($this->returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $configuration);
        $this->assertFalse($this->restriction->isClientRestricted());
    }

    /**
     * @test
     */
    public function isClientRestrictedWithFailures()
    {
        $configuration = $this->getMock('Aoe\FeloginBruteforceProtection\System\Configuration', array(), array(), '', false);
        $configuration->expects($this->any())->method('getMaximumNumerOfFailures')->will($this->returnValue(10));
        $configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
        $configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));
        $entry = $this->getMock('Aoe\FeloginBruteforceProtection\Domain\Model\Entry', array(), array(), '', false);
        $entry->expects($this->any())->method('getFailures')->will($this->returnValue(10));
        $entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time() - 400));
        $entry->expects($this->any())->method('getTstamp')->will($this->returnValue(time() - 400));
        $entryRepository = $this->getMock(
            'Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository',
            array('findOneByIdentifier', 'remove'),
            array(),
            '',
            false
        );
        $entryRepository->expects($this->any())->method('findOneByIdentifier')->will($this->returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $configuration);
        $this->assertTrue($this->restriction->isClientRestricted());
    }

    /**
     * @test
     */
    public function isClientRestrictedWithFailuresAndTimeout()
    {
        $configuration = $this->getMock('Aoe\FeloginBruteforceProtection\System\Configuration', array(), array(), '', false);
        $configuration->expects($this->any())->method('getMaximumNumerOfFailures')->will($this->returnValue(10));
        $configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
        $configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));
        $entry = $this->getMock('Aoe\FeloginBruteforceProtection\Domain\Model\Entry', array(), array(), '', false);
        $entry->expects($this->any())->method('getFailures')->will($this->returnValue(10));
        $entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time() - 400));
        $entry->expects($this->any())->method('getTstamp')->will($this->returnValue(time() - 4000));
        $entryRepository = $this->getMock(
            'Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository',
            array('findOneByIdentifier', 'remove'),
            array(),
            '',
            false
        );
        $entryRepository->expects($this->any())->method('findOneByIdentifier')->will($this->returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $configuration);
        $this->assertFalse($this->restriction->isClientRestricted());
    }

    /**
     * @test
     */
    public function isClientRestrictedWithLessFailures()
    {
        $configuration = $this->getMock('Aoe\FeloginBruteforceProtection\System\Configuration', array(), array(), '', false);
        $configuration->expects($this->any())->method('getMaximumNumerOfFailures')->will($this->returnValue(10));
        $configuration->expects($this->any())->method('getResetTime')->will($this->returnValue(300));
        $configuration->expects($this->any())->method('getRestrictionTime')->will($this->returnValue(3000));
        $entry = $this->getMock('Aoe\FeloginBruteforceProtection\Domain\Model\Entry', array(), array(), '', false);
        $entry->expects($this->any())->method('getFailures')->will($this->returnValue(5));
        $entry->expects($this->any())->method('getCrdate')->will($this->returnValue(time() - 400));
        $entry->expects($this->any())->method('getTstamp')->will($this->returnValue(time() - 400));
        $entryRepository = $this->getMock(
            'Aoe\FeloginBruteforceProtection\Domain\Repository\EntryRepository',
            array('findOneByIdentifier', 'remove'),
            array(),
            '',
            false
        );
        $entryRepository->expects($this->any())->method('findOneByIdentifier')->will($this->returnValue($entry));
        $this->inject($this->restriction, 'entryRepository', $entryRepository);
        $this->inject($this->restriction, 'configuration', $configuration);
        $this->assertFalse($this->restriction->isClientRestricted());
    }
}