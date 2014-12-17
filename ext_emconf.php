<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Brute Force Protection',
    'description' => 'Protects TYPO3´s frontend authentication (e.g. fe_login) against brute force attacks.',
    'category' => 'services',
    'author' => 'Kevin Schu, Andre Wuttig',
    'author_email' => 'dev@aoe.com, wuttig@portrino.de',
    'author_company' => 'AOE GmbH, portrino GmbH',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '0.4.2',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2.0',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
