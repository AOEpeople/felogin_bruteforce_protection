<?php

use TYPO3\CMS\Core\Http\ApplicationType;

defined('TYPO3') or die();

if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
    // postUserLookUp hookC
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['felogin_bruteforce_protection'] = \Aoe\FeloginBruteforceProtection\Hooks\UserAuth\PostUserLookUp::class . '->handlePostUserLookUp';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postLoginFailureProcessing']['felogin_bruteforce_protection'] = \Aoe\FeloginBruteforceProtection\Hooks\UserAuth\PostUserLookUp::class . '->processFailedLogin';
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
