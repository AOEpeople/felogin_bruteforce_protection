<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {

    if (TYPO3_MODE == 'BE') {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers']) == false) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'] = [];
        }
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
            \Aoe\FeloginBruteforceProtection\Tests\Unit\Command\CleanUpCommandController::class;
    }

    if (TYPO3_MODE == 'FE') {
        // postUserLookUp hookC
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][$_EXTKEY] =
            \Aoe\FeloginBruteforceProtection\Hooks\UserAuth\PostUserLookUp::class . '->handlePostUserLookUp';
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
        $_EXTKEY,
        'auth',
        '\Aoe\FeloginBruteforceProtection\Service\AuthUser',
        [
            'title' => 'brute force protection',
            'description' => 'brute force protection for system extension felogin',
            'subtype' => 'authUserFE,getUserFE',
            'available' => true,
            'priority' => 100,
            'quality' => 100,
            'os' => '',
            'exec' => '',
            'className' => \Aoe\FeloginBruteforceProtection\Service\AuthUser::class
        ]
    );
};

$boot($_EXTKEY);
unset($boot);
