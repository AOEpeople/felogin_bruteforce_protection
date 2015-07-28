<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE == 'BE') {
    if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers']) == false) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'] = array();
    }
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
        'Aoe\\FeloginBruteforceProtection\\Command\\CleanUpCommandController';
}

if (TYPO3_MODE == 'FE') {
    // postUserLookUp hookC
    $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][$_EXTKEY] =
        'EXT:' .
        $_EXTKEY .
        '/Classes/Hooks/UserAuth/PostUserLookUp.php:' .
        '\\Aoe\\FeloginBruteforceProtection\\Hooks\\UserAuth\\PostUserLookUp->handlePostUserLookUp';
}

/*
 * @todo implement instead of hook usage
 */
// Register base authentication service
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
//    $_EXTKEY,
//    'auth',
//    'Aoe\\FeloginBruteforceProtection\\Service\\AuthUser',
//    array(
//        'title' => 'brute force protection',
//        'description' => 'brute force protection for system extension felogin',
//        'subtype' => 'authUserFE,getUserFE',
//        'available' => true,
//        'priority' => 90,
//        'quality' => 90,
//        'os' => '',
//        'exec' => '',
//        'className' => 'Aoe\\FeloginBruteforceProtection\\Service\\AuthUser'
//    )
//);
