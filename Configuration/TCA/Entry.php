<?php

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$TCA['tx_feloginbruteforceprotection_domain_model_entry'] = array(
    'ctrl' => $TCA['tx_feloginbruteforceprotection_domain_model_entry']['ctrl'],
    'interface' => array(
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, identifier, failures',
    ),
    'types' => array(
        '1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, identifier, failures,--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime'),
    ),
    'palettes' => array(
        '1' => array('showitem' => ''),
    ),
    'columns' => array(
        'sys_language_uid' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
            'config' => array(
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
                ),
            ),
        ),
        'l10n_parent' => array(
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
            'config' => array(
                'type' => 'select',
                'items' => array(
                    array('', 0),
                ),
                'foreign_table' => 'tx_feloginbruteforceprotection_domain_model_entry',
                'foreign_table_where' => 'AND tx_feloginbruteforceprotection_domain_model_entry.pid=###CURRENT_PID### AND tx_feloginbruteforceprotection_domain_model_entry.sys_language_uid IN (-1,0)',
            ),
        ),
        'l10n_diffsource' => array(
            'config' => array(
                'type' => 'passthrough',
            ),
        ),
        't3ver_label' => array(
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            )
        ),
        'tstamp' => Array(
            'exclude' => 1,
            'label' => 'Update date',
            'config' => Array(
                'type' => 'none',
                'format' => 'date',
                'eval' => 'date',
            )
        ),
        'crdate' => Array(
            'exclude' => 1,
            'label' => 'Creation date',
            'config' => Array(
                'type' => 'none',
                'format' => 'date',
                'eval' => 'date',
            )
        ),
        'hidden' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config' => array(
                'type' => 'check',
            ),
        ),
        'starttime' => array(
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
            'config' => array(
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => array(
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ),
            ),
        ),
        'endtime' => array(
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
            'config' => array(
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => array(
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ),
            ),
        ),
        'identifier' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:felogin_bruteforce_protection/Resources/Private/Language/locallang_db.xml:tx_feloginbruteforceprotection_domain_model_entry.identifier',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ),
        ),
        'failures' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:felogin_bruteforce_protection/Resources/Private/Language/locallang_db.xml:tx_feloginbruteforceprotection_domain_model_entry.failures',
            'config' => array(
                'type' => 'input',
                'size' => 4,
                'eval' => 'int,required'
            ),
        ),
    ),
);