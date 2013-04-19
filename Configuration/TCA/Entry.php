<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_feloginbruteforceprotection_domain_model_entry'] = array(
	'ctrl' => $TCA['tx_feloginbruteforceprotection_domain_model_entry']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'identifier,failures',
	),
	'types' => array(
		'1' => array('showitem' => 'identifier, failures'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
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