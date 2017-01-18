<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$antiRobotsNoIndex = array(
    'anti_robots_no_index' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:news_anti_robots/Resources/Private/Language/locallang_db.xlf:tx_news_anti_robots.title',
        'config' => array(
            'type' => 'check',
            'items' => array(
                '1' => array(
                    '0' => 'LLL:EXT:news_anti_robots/Resources/Private/Language/locallang_db.xlf:tx_news_anti_robots.preventIndexing',
                ),
            ),
        ),
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tx_news_domain_model_news',
    $antiRobotsNoIndex
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_news_domain_model_news',
    'anti_robots_no_index'
);