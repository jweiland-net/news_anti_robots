<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = 'news_anti_robots';

/* If currently in news->detail check for existing robots meta tags and if needed replace them with the checkbox val of the news article */
$preRenderHook = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess']['news_anti_robots'] = 'JWeiland\\NewsAntiRobots\\Hooks\\PostProcessHook->modify';