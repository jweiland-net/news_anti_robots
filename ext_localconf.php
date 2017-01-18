<?php

$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = 'news_anti_robots';

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect(
    'TYPO3\\CMS\\Extbase\\Mvc\\Dispatcher',
    'afterRequestDispatch',
    'JWeiland\\NewsAntiRobots\\Slots\\DispatcherSlot',
    'afterRequestDispatchSlot',
    TRUE
);