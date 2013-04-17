<?php

if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/Classes/Hooks/UserAuth/PostUserLookUp.php:Tx_FeloginBruteforceProtection_Hooks_UserAuth_PostUserLookUp->handlePostUserLookUp';