<?php
namespace JWeiland\NewsAntiRobots\Tests\Unit\Hooks;

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
use JWeiland\NewsAntiRobots\Hooks\PreRenderHook;
use TYPO3\CMS\Core\Html\HtmlParser;
use TYPO3\CMS\Core\Tests\AccessibleObjectInterface;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class PreRenderHookTest
 *
 * @package JWeiland\NewsAntiRobots\Hooks
 */
class PreRenderHookTest extends UnitTestCase
{
    /**
     * @var PreRenderHook|\PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface
     */
    protected $subject;
    
    /**
     * SetUp
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock(
            'JWeiland\\NewsAntiRobots\\Hooks\\PreRenderHook',
            array(
                'getTxNewsNamespace',
                'isUsingNewsDetailView',
                'getNewsRepository',
                'getHtmlParser',
                'overrideIndexToNoIndex'
            ),
            array(),
            '',
            false
        );
    }
    
    /**
     * TearDown
     */
    public function tearDown()
    {
        unset($this->subject);
    }
    
    /**
     * @test
     */
    public function modifyWithNoMetaTagsKeyInParamsWillDoNothing()
    {
        $params = array(
            'test' => 'test',
            'test2' => 'test2'
        );
    
        $this->subject->expects($this->never())->method('getTxNewsNamespace');
    
        $this->subject->modify($params);
    }
    
    /**
     * @test
     */
    public function modifyWithTxNewsNamespaceNullWillDoNothing()
    {
        $params = array(
            'metaTags' => array('test')
        );
        
        $this->subject->expects($this->once())->method('getTxNewsNamespace');
        $this->subject->expects($this->never())->method('isUsingNewsDetailView');
        
        $this->subject->modify($params);
    }
    
    /**
     * @test
     */
    public function modifyWithNoDetailActionInTxNewsNamespaceWillDoNothing()
    {
        $params = array(
            'metaTags' => array('test')
        );
    
        $txNewsNamespace = array(
            'action' => 'list',
            'controller' => 'News',
            'news' => '2'
        );
    
        $this->subject->expects($this->once())->method('getTxNewsNamespace')->willReturn($txNewsNamespace);
        $this->subject->expects($this->once())->method('isUsingNewsDetailView')->with($txNewsNamespace)->willReturn(false);
        $this->subject->expects($this->never())->method('getNewsRepository');
    
        $this->subject->modify($params);
    }
    
    /**
     * @test
     */
    public function modifyWillDoNothingIfNoNewsArticleIsFound()
    {
        $params = array(
            'metaTags' => array('test')
        );
    
        $txNewsNamespace = array(
            'action' => 'detail',
            'controller' => 'News',
            'news' => '2'
        );
    
        $this->subject->expects($this->once())->method('getTxNewsNamespace')->willReturn($txNewsNamespace);
        $this->subject->expects($this->once())->method('isUsingNewsDetailView')->with($txNewsNamespace)->willReturn(true);
    
        /** @var NewsRepository|\PHPUnit_Framework_MockObject_MockObject $newsRepository */
        $newsRepository = $this->getMock(
            'GeorgRinger\\News\\Domain\\Repository\\NewsRepository',
            array('findByUid'),
            array(),
            '',
            false
        );
        
        $newsRepository->expects($this->once())->method('findByUid')->with('2')->willReturn(null);
        
        $this->subject->expects($this->once())->method('getNewsRepository')->willReturn($newsRepository);
        $this->subject->expects($this->never())->method('getHtmlParser');
    
        $this->subject->modify($params);
    }
    
    /**
     * @test
     */
    public function modifyWithValidEverythingWillReturnExpectedValue()
    {
        $params = array(
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="index, follow">',
                2 => '<meta name="description" content="test">'
            )
        );
        
        $expectedParams = array(
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="noindex, follow">',
                2 => '<meta name="description" content="test">'
            )
        );
    
        $txNewsNamespace = array(
            'action' => 'detail',
            'controller' => 'News',
            'news' => '2'
        );
        
        /** @var NewsRepository|\PHPUnit_Framework_MockObject_MockObject $newsRepository */
        $newsRepository = $this->getMock(
            'GeorgRinger\\News\\Domain\\Repository\\NewsRepository',
            array('findByUid'),
            array(),
            '',
            false
        );
    
        /** @var News|\PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface $newsArticle */
        $newsArticle = $this->getAccessibleMock('JWeiland\\NewsAntiRobots\\Domain\\Model\\News');
        $newsArticle->_set('antiRobotsNoIndex', true);
        $newsArticle->expects($this->once())->method('getAntiRobotsNoIndex')->willReturn(true);
    
        $newsRepository->expects($this->once())->method('findByUid')->with($txNewsNamespace['news'])->willReturn(
            $newsArticle
        );
        
        $attributesCall1 = array(
            array (
                'charset' => 'UTF-8'
            )
        );

        $attributesCall2 = array(
            array (
                'name' => 'robots',
                'content' => 'index, follow'
            )
        );
    
        $compiledRobotsAttribute = 'name="robots" content="noindex, follow"';
        
        /** @var HtmlParser|\PHPUnit_Framework_MockObject_MockObject $htmlParser */
        $htmlParser = $this->getMock('TYPO3\\CMS\\Core\\Html\\HtmlParser');
        $htmlParser->expects($this->at(0))->method('get_tag_attributes')->with($params['metaTags'][0], 0)->willReturn(
                $attributesCall1
        );
        $htmlParser->expects($this->at(1))->method('get_tag_attributes')->with($params['metaTags'][1], 0)->willReturn(
            $attributesCall2
        );
        $htmlParser->expects($this->exactly(2))->method('get_tag_attributes');
        
        $toCompile = array(
            'name' => 'robots',
            'content' => 'noindex, follow'
        );
        
        $htmlParser->expects($this->once())->method('compileTagAttribs')->with($toCompile)->willReturn(
            $compiledRobotsAttribute
        );
        
        $this->subject->expects($this->once())->method('getTxNewsNamespace')->willReturn($txNewsNamespace);
        $this->subject->expects($this->once())->method('isUsingNewsDetailView')->with($txNewsNamespace)->willReturn(
            true
        );
        $this->subject->expects($this->once())->method('getNewsRepository')->willReturn($newsRepository);
        $this->subject->expects($this->once())->method('getHtmlParser')->willReturn($htmlParser);
        
        $this->subject->expects($this->once())->method('overrideIndexToNoIndex')->with($attributesCall2[0]['content'])->willReturn(
            'noindex, follow'
        );
        
        $this->subject->modify($params);
        
        $this->assertEquals(
            $params,
            $expectedParams
        );
    }
}