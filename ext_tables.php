<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'brute force protection');

t3lib_extMgm::addLLrefForTCAdescr('tx_feloginbruteforceprotection_domain_model_entry', 'EXT:felogin_bruteforce_protection/Resources/Private/Language/locallang_csh_tx_feloginbruteforceprotection_domain_model_entry.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_feloginbruteforceprotection_domain_model_entry');
$TCA['tx_feloginbruteforceprotection_domain_model_entry'] = array(
    'ctrl' => array(
        'title' => 'LLL:EXT:felogin_bruteforce_protection/Resources/Private/Language/locallang_db.xml:tx_feloginbruteforceprotection_domain_model_entry',
        'label' => 'identifier',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => TRUE,

        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ),
        'searchFields' => 'identifier,failures,',
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Entry.php',
        'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_feloginbruteforceprotection_domain_model_entry.gif'
    ),
);