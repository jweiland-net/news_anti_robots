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
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;


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
    public function modify($params)
    {
        /** @var array $newsNamespace */
        if ($newsNamespace = $this->getTxNewsNamespace())
        {
            if ($this->isUsingNewsDetailView($newsNamespace))
            {
                $newsId = $newsNamespace['news'];
                
                $newsRepository = $this->getNewsRepository();
                
                /** @var News $newsArticle */
                $newsArticle = $newsRepository->findByUid($newsId);
                
                if ($newsArticle->getAntiRobotsNoIndex()) {
                    $htmlParser = $this->getHtmlParser();
                    
                    $foundMetaRobots = false;
                    
                    foreach ($params['metaTags'] as &$metaTag) {
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
     * will return null if not existing
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
     *
     *
     * @return object|HtmlParser
     */
    protected function getHtmlParser()
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Html\\HtmlParser');
    }
}