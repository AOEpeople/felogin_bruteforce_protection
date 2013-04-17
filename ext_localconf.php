<?php

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

t3lib_extMgm::addService(
    $_EXTKEY, 'auth', 'Tx_FeloginBruteforceProtection_Service_AuthUser',
    array(
        'title' => 'brute force protection',
        'description' => 'brute force protection for system extension felogin',
        'subtype' => 'authUserFE,getUserFE',
        'available' => TRUE,
        'priority' => 100,
        'quality' => 100,
        'os' => '',
        'exec' => '',
        'classFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Service/AuthUser.php',
        'className' => 'Tx_FeloginBruteforceProtection_Service_AuthUser',
    )
);

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/UserAuth/PostUserLookUp.php:Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUp->handlePostUserLookUp';