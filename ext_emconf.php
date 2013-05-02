<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'brute force protection',
	'description' => 'Protect TYPO3´s system extension "fe_login" for brute force attacks.',
	'category' => 'services',
	'author' => 'Kevin Schu',
	'author_email' => 'kevin.schu@aoemedia.de',
	'author_company' => 'AOE media GmbH',
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
	'version' => '0.5.0',
	'constraints' => array(
		'depends' => array(
			'extbase' => '1.3',
			'typo3' => '4.5',
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
);