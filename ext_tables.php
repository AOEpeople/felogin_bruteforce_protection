<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

//t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'brute force protection');

$TCA['tx_feloginbruteforceprotection_domain_model_entry'] = array(
    'ctrl' => array(
        'title' => 'LLL:EXT:felogin_bruteforce_protection/Resources/Private/Language/locallang_db.xml:tx_feloginbruteforceprotection_domain_model_entry',
        'label' => 'identifier',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'dividers2tabs' => TRUE,
        'searchFields' => 'identifier,failures,',
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Entry.php',
        'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/TCA/Entry.gif'
    ),
);