<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'brute force protection',
	'description' => 'Protect TYPO3´s system extension "fe_login" for brute force attacks.',
	'category' => 'services',
	'author' => 'Kevin Schu',
	'author_email' => 'kevin.schu@aoe.com',
	'author_company' => 'AOE GmbH',
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
	'version' => '0.4.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0',
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
);