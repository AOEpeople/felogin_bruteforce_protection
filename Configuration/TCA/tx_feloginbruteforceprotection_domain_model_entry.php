<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:felogin_bruteforce_protection/Resources/Private/Language/locallang_db.xml' .
            ':tx_feloginbruteforceprotection_domain_model_entry',
        'label' => 'identifier',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'searchFields' => 'identifier,failures,',
        'iconfile' => 'EXT:felogin_bruteforce_protection/Resources/Public/Icons/TCA/Entry.gif',
    ],
    'types' => [
        '1' => ['showitem' => 'identifier, failures'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'tstamp' => [
            'exclude' => 1,
            'label' => 'Update date',
            'config' => [
                'type' => 'none',
                'format' => 'date',
                'eval' => 'date',
            ],
        ],
        'crdate' => [
            'exclude' => 1,
            'label' => 'Creation date',
            'config' => [
                'type' => 'none',
                'format' => 'date',
                'eval' => 'date',
            ],
        ],
        'identifier' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:felogin_bruteforce_protection/Resources/Private/Language/locallang_db.xml' .
                ':tx_feloginbruteforceprotection_domain_model_entry.identifier',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'failures' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:felogin_bruteforce_protection/Resources/Private/Language/locallang_db.xml' .
                ':tx_feloginbruteforceprotection_domain_model_entry.failures',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int,required',
            ],
        ],
    ],
];
