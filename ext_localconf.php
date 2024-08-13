<?php

use Aoe\FeloginBruteforceProtection\Hooks\UserAuth\PostUserLookUp;
use Aoe\FeloginBruteforceProtection\Service\AuthUser;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']['felogin_bruteforce_protection'] = PostUserLookUp::class . '->handlePostUserLookUp';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postLoginFailureProcessing']['felogin_bruteforce_protection'] = PostUserLookUp::class . '->processFailedLogin';


ExtensionManagementUtility::addService(
    'felogin_bruteforce_protection',
    'auth',
    AuthUser::class,
    [
        'title' => 'brute force protection',
        'description' => 'brute force protection for system extension felogin',
        'subtype' => 'authUserFE,getUserFE',
        'available' => true,
        'priority' => 100,
        'quality' => 100,
        'os' => '',
        'exec' => '',
        'className' => AuthUser::class,
    ]
);
