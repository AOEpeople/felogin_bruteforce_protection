<?php

defined('TYPO3') or die();

if (TYPO3_MODE == 'FE') {
    // postUserLookUp hookC
    $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['felogin_bruteforce_protection'] = \Aoe\FeloginBruteforceProtection\Hooks\UserAuth\PostUserLookUp::class . '->handlePostUserLookUp';
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'felogin_bruteforce_protection',
    'auth',
    \Aoe\FeloginBruteforceProtection\Service\AuthUser::class,
    [
        'title' => 'brute force protection',
        'description' => 'brute force protection for system extension felogin',
        'subtype' => 'authUserFE,getUserFE',
        'available' => true,
        'priority' => 100,
        'quality' => 100,
        'os' => '',
        'exec' => '',
        'className' => \Aoe\FeloginBruteforceProtection\Service\AuthUser::class,
    ]
);
