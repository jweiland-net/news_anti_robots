<?php
namespace JWeiland\NewsAntiRobots\Hooks;

/*
* This file is part of the TYPO3 CMS project.
*
* It is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License, either version 2
* of the License, or any later version.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*
* The TYPO3 project - inspiring people to share!
*/

use GeorgRinger\News\Domain\Repository\NewsRepository;
use JWeiland\NewsAntiRobots\Domain\Model\News;
use TYPO3\CMS\Core\Html\HtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PreRenderHook
 *
 * @package JWeiland\NewsAntiRobots\Hooks
 */
class PreRenderHook
{
    /**
     * If current context is NewsDetailAction then check
     * antiRobotsNoIndex field and replace robots NOINDEX,FOLLOW depending on it
     *
     * @param array $params
     */
    public function modify(&$params)
    {
        if (!$params['metaTags']) {
            return;
        }
        
        $txNewsNamespace = $this->getTxNewsNamespace();
        
        if (!$txNewsNamespace) {
            return;
        }
        
        if ($this->isUsingNewsDetailView($txNewsNamespace)) {
            $newsId = $txNewsNamespace['news'];
            
            /** @var News $newsArticle */
            $newsArticle = $this->getNewsRepository()->findByUid($newsId);
            
            if ($newsArticle && $newsArticle->getAntiRobotsNoIndex()) {
                $htmlParser = $this->getHtmlParser();
                
                $foundMetaRobots = false;
                
                foreach ($params['metaTags'] as &$metaTag) {
                    /* Stop if metaTag was found. Multiple tags should not exist and even then,
                    the most restrictive robots tag should automatically be used     by robots */
                    if ($foundMetaRobots)
                        return;
                    
                    $attributes = $htmlParser->get_tag_attributes($metaTag);
                    if (strtolower($attributes[0]['name']) === 'robots') {
                        $foundMetaRobots = true;
                        if (!stripos($content = &$attributes[0]['content'], 'noindex')) {
                            $content = $this->overrideIndexToNoIndex($content);
                            $metaTag = '<meta ' . $htmlParser->compileTagAttribs($attributes[0]) . '>';
                        }
                    }
                }
                
                if (!$foundMetaRobots) {
                    $params['metaTags'][] = '<meta name="robots" content="NOINDEX">';
                }
            }
        }
    }
    
    /**
     * Replaces index with no index
     * or sets noindex as first content if index is not existing
     *
     * @param string $content
     *
     * @return string
     */
    protected function overrideIndexToNoIndex($content = '')
    {
        if (!stripos($content = str_ireplace('index', 'NOINDEX', $content), 'index')) {
            $content = 'NOINDEX' . $content;
        }
        
        return $content;
    }
    
    /**
     * Check if current context is using news detail view
     *
     * @param array $newsNamespace
     *
     * @return bool
     */
    protected function isUsingNewsDetailView($newsNamespace)
    {
        if ($newsNamespace['controller'] === 'News' && $newsNamespace['action'] === 'detail') {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * get news namespace
     *
     * @return array|null
     */
    protected function getTxNewsNamespace()
    {
        return GeneralUtility::_GET('tx_news_pi1');
    }
    
    /**
     * Get news repository
     *
     * @return object|NewsRepository
     */
    protected function getNewsRepository()
    {
        return GeneralUtility::makeInstance('GeorgRinger\\News\\Domain\\Repository\\NewsRepository');
    }
    
    /**
     * get html parser
     *
     * @return object|HtmlParser
     */
    protected function getHtmlParser()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Html\\HtmlParser');
    }
}