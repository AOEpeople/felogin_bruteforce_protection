<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Brute Force Protection',
    'description' => 'Protects TYPO3 frontend authentication (e.g. fe_login) against brute force attacks.',
    'category' => 'services',
    'author' => 'Kevin Schu, Andre Wuttig',
    'author_email' => 'dev@aoe.com, wuttig@portrino.de',
    'author_company' => 'AOE GmbH, portrino GmbH',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '12.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
