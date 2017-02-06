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
class PostProcessHook
{
    /**
     * htmlParser
     *
     * @var HtmlParser
     */
    protected $htmlParser = null;
    
    /**
     * If current context is NewsDetailAction then check
     * antiRobotsNoIndex field and replace robots NOINDEX,FOLLOW depending on it
     *
     * @param array $params
     */
    public function modify(&$params)
    {
        $txNewsNamespace = $this->getTxNewsNamespace();
        
        if ($txNewsNamespace && $this->isUsingNewsDetailView($txNewsNamespace)) {
            $newsId = $txNewsNamespace['news'];
            
            /** @var News $newsArticle */
            $newsArticle = $this->getNewsRepository()->findByUid($newsId);
            if ($newsArticle && $newsArticle->getHasMetaRobotsNoIndexDefined()) {
                if (
                    !$this->replaceMetaTagInHeaderData(
                        $params['headerData'],
                        'name="robots"',
                        'meta',
                        array($this, 'addNoIndexToMetaTag')
                    ) &&
                    !$this->replaceMetaTagInArray(
                        $params['metaTags'],
                        'name="robots"',
                        array($this,'addNoIndexToMetaTag')
                    )
                ) {
                    $params['metaTags'][] = '<meta name="robots" content="NOINDEX">';
                }
            }
        }
    }
    
    /**
     * Returns modified metaTag
     *
     * @param string $metaRobotsTag
     *
     * @return string
     */
    protected function addNoIndexToMetaTag($metaRobotsTag)
    {
        $metaTag = '';
        
        $attributes = $this->getHtmlParser()->get_tag_attributes($metaRobotsTag);
        
        if (!stripos($content = &$attributes[0]['content'], 'noindex')) {
            $content = $this->overrideIndexToNoIndex($content);
            $metaTag = '<meta ' . $this->getHtmlParser()->compileTagAttribs($attributes[0]) . '>';
        }
        
        return $metaTag;
    }
    
    /**
     * Will return headerData with replaced tag or old if none found
     *
     * @param array $headerData
     * @param string $searchCriteria
     * @param string $tagName
     * @param string $metaModifyMethod
     *
     * @return bool
     */
    protected function replaceMetaTagInHeaderData(&$headerData, $searchCriteria, $tagName, $metaModifyMethod)
    {
        $metaRobots = '';
        
        for ($i = 0, $headerDataLength = count($headerData); !$metaRobots && $i < $headerDataLength; $i++) {
            if (stripos($headerData[$i], $searchCriteria)) {
                $tags = $this->getHtmlParser()->getAllParts($this->getHtmlParser()->splitTags($tagName, $headerData[$i]));
                for ($j = 0, $tagsLength = count($tags); !$metaRobots && $j < $tagsLength; $j++) {
                    if (stripos($tags[$j], $searchCriteria)) {
                        $metaRobots = $tags[$j];
                        $headerData[$i] = str_ireplace($metaRobots, call_user_func_array($metaModifyMethod, array($metaRobots)), $headerData[$i]);
                    }
                }
            }
        }
        return (bool)$metaRobots;
    }
    
    /**
     * Returns array with replaced tag or old of nothing found
     *
     * @param array $array
     * @param string $searchCriteria
     * @param string $metaModifyMethod
     *
     * @return bool
     */
    protected function replaceMetaTagInArray(&$array, $searchCriteria, $metaModifyMethod)
    {
        $metaRobots = '';
        
        for ($i = 0, $tagsLength = count($array); !$metaRobots && $i < $tagsLength; $i++) {
            if (stripos($array[$i], $searchCriteria)) {
                $metaRobots = $array[$i];
                $array[$i] = str_ireplace($metaRobots, call_user_func_array($metaModifyMethod, array($metaRobots)), $array[$i]);
            }
        }
    
        return (bool)$metaRobots;
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
        if (!$this->htmlParser) {
            $this->htmlParser = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Html\\HtmlParser');
        }
        return $this->htmlParser;
    }
}