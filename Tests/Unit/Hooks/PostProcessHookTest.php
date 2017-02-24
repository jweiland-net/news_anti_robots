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
use JWeiland\NewsAntiRobots\Hooks\PostProcessHook;
use TYPO3\CMS\Core\Html\HtmlParser;
use TYPO3\CMS\Core\Tests\AccessibleObjectInterface;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class PreRenderHookTest
 *
 * @package JWeiland\NewsAntiRobots\Hooks
 */
class PostProcessHookTest extends UnitTestCase
{
    /**
     * @var PostProcessHook|\PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface
     */
    protected $subject;
    
    /**
     * @var array
     */
    protected $unmodifiedParams = array(
        'headerData' => array(
            '<meta charset="UTF-8"><meta name="robots" content="index, follow"><meta name="description" content="test">'
        ),
        'metaTags' => array(
            0 => '<meta charset="UTF-8">',
            1 => '<meta name="robots" content="index, follow">',
            2 => '<meta name="description" content="test">'
        )
    );
    
    /**
     * SetUp
     */
    public function setUp()
    {
        $this->subject = $this->getMock(
            'JWeiland\\NewsAntiRobots\\Hooks\\PostProcessHook',
            array(
                'getTxNewsNamespace',
                'getNewsId',
                'getNewsRepository',
                'getHtmlParser'
            )
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
    public function modifyWithNoValidTxNewsNamespaceWillDoNothing()
    {
        $params = array(
            'headerData' => array(
                '<meta charset="UTF-8"><meta name="robots" content="index, follow"><meta name="description" content="test">'
            ),
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="index, follow">',
                2 => '<meta name="description" content="test">'
            )
        );
        
        $this->subject->expects($this->once())->method('getTxNewsNamespace');
        $this->subject->expects($this->never())->method('getNewsId');
        
        $this->subject->modify($params);
        
        $this->assertEquals($this->unmodifiedParams, $params);
    }
    
    /**
     * @test
     */
    public function modifyWithNoNewsIdInTxNewsNamespaceWillDoNothing()
    {
        $params = array(
            'headerData' => array(
                '<meta charset="UTF-8"><meta name="robots" content="index, follow"><meta name="description" content="test">'
            ),
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="index, follow">',
                2 => '<meta name="description" content="test">'
            )
        );

        $txNewsNamespace = array('news' => '');
        
        $this->subject->expects($this->once())->method('getTxNewsNamespace')->willReturn($txNewsNamespace);
        $this->subject->expects($this->once())->method('getNewsId')->with($txNewsNamespace);
        $this->subject->expects($this->never())->method('getNewsRepository');

        $this->subject->modify($params);
    
        $this->assertEquals($this->unmodifiedParams, $params);
    }
    
    /**
     * @test
     */
    public function modifyWithNotNumericNewsIdWillDoNothing()
    {
        $params = array(
            'headerData' => array(
                '<meta charset="UTF-8"><meta name="robots" content="index, follow"><meta name="description" content="test">'
            ),
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="index, follow">',
                2 => '<meta name="description" content="test">'
            )
        );
    
        $txNewsNamespace = array('news' => 'test');
    
        $this->subject->expects($this->once())->method('getTxNewsNamespace')->willReturn($txNewsNamespace);
        $this->subject->expects($this->once())->method('getNewsId')->with($txNewsNamespace);
        $this->subject->expects($this->never())->method('getNewsRepository');
    
        $this->subject->modify($params);
    
        $this->assertEquals($this->unmodifiedParams, $params);
    }
    
    /**
     * @test
     */
    public function modifyWithValidNewsIdButNoNewsIdFoundWillDoNothing()
    {
        $params = array(
            'headerData' => array(
                '<meta charset="UTF-8"><meta name="robots" content="index, follow"><meta name="description" content="test">'
            ),
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="index, follow">',
                2 => '<meta name="description" content="test">'
            )
        );
        
        $txNewsNamespace = array('news' => '10');
    
        $this->subject->expects($this->once())->method('getTxNewsNamespace')->willReturn($txNewsNamespace);
        $this->subject->expects($this->once())->method('getNewsId')->with($txNewsNamespace)->willReturn('10');
    
        /** @var NewsRepository|\PHPUnit_Framework_MockObject_MockObject $newsRepository */
        $newsRepository = $this->getMock(
            'GeorgRinger\\News\\Domain\\Repository\\NewsRepository',
            array('findByUid'),
            array(),
            '',
            false
        );
        
        $newsRepository->expects($this->once())->method('findByUid')->with($txNewsNamespace['news'])->willReturn(null);
    
        $this->subject->expects($this->once())->method('getNewsRepository')->willReturn($newsRepository);
        $this->subject->expects($this->never())->method('replaceMetaTagInHeaderData');
        
        $this->subject->modify($params);
    
        $this->assertEquals($this->unmodifiedParams, $params);
    }
    
    /**
     * @test
     */
    public function modifyWithValidNewsIdButRobotsNoIndexIsNotDefinedWillDoNothig()
    {
        $params = array(
            'headerData' => array(
                '<meta charset="UTF-8"><meta name="robots" content="index, follow"><meta name="description" content="test">'
            ),
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="index, follow">',
                2 => '<meta name="description" content="test">'
            )
        );
    
        $txNewsNamespace = array('news' => '10');
    
        $this->subject->expects($this->once())->method('getTxNewsNamespace')->willReturn($txNewsNamespace);
        $this->subject->expects($this->once())->method('getNewsId')->with($txNewsNamespace)->willReturn('10');
    
        /** @var NewsRepository|\PHPUnit_Framework_MockObject_MockObject $newsRepository */
        $newsRepository = $this->getMock(
            'GeorgRinger\\News\\Domain\\Repository\\NewsRepository',
            array('findByUid'),
            array(),
            '',
            false
        );
    
        /** @var News|\PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface $newsArticle */
        $newsArticle = $this->getMock(
            'JWeiland\\NewsAntiRobots\\Domain\\Model\\News',
            array('getHasMetaRobotsNoIndexDefined')
        );
    
        $newsArticle->expects($this->once())->method('getHasMetaRobotsNoIndexDefined');
        
        $newsRepository->expects($this->once())
            ->method('findByUid')
            ->with($txNewsNamespace['news'])
            ->willReturn($newsArticle);
    
        $this->subject->expects($this->once())->method('getNewsRepository')->willReturn($newsRepository);
        
        $this->subject->expects($this->never())->method('replaceMetaTagInHeaderData');
    
        $this->subject->modify($params);
    
        $this->assertEquals($this->unmodifiedParams, $params);
    }
    
    /**
     * @test
     */
    public function modifyWithNewsArticleNoIndexSetAndMetaTagInHeaderDataFoundWillReturnHeaderDataWithNoIndexSet()
    {
        /** @var PostProcessHook|\PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface $subject */
        $subject = $this->getMock(
            'JWeiland\\NewsAntiRobots\\Hooks\\PostProcessHook',
            array(
                'replaceMetaTagInArray',
                'getTxNewsNamespace',
                'getNewsId',
                'getNewsRepository',
                'getHtmlParser'
            )
        );
        
        $params = array(
            'headerData' => array(
                '<meta charset="UTF-8"><meta name="robots" content="index, follow"><meta name="description" content="test">'
            ),
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="index, follow">',
                2 => '<meta name="description" content="test">'
            )
        );
        
        $expectedParams = array(
            'headerData' => array(
                '<meta charset="UTF-8"><meta name="robots" content="NOINDEX, follow"><meta name="description" content="test">'
            ),
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="index, follow">',
                2 => '<meta name="description" content="test">'
            )
        );
    
        $txNewsNamespace = array('news' => '10');
    
        $subject->expects($this->once())->method('getTxNewsNamespace')->willReturn($txNewsNamespace);
        $subject->expects($this->once())->method('getNewsId')->with($txNewsNamespace)->willReturn('10');
    
        /** @var NewsRepository|\PHPUnit_Framework_MockObject_MockObject $newsRepository */
        $newsRepository = $this->getMock(
            'GeorgRinger\\News\\Domain\\Repository\\NewsRepository',
            array('findByUid'),
            array(),
            '',
            false
        );
    
        /** @var News|\PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface $newsArticle */
        $newsArticle = $this->getAccessibleMock('JWeiland\\NewsAntiRobots\\Domain\\Model\\News', array('dummy'));
        $newsArticle->_set('hasMetaRobotsNoIndexDefined', true);
    
        $newsRepository->expects($this->once())
            ->method('findByUid')
            ->with($txNewsNamespace['news'])
            ->willReturn($newsArticle);
    
        $subject->expects($this->once())->method('getNewsRepository')->willReturn($newsRepository);
    
        /** @var HtmlParser|\PHPUnit_Framework_MockObject_MockObject $htmlParser */
        $htmlParser = $this->getMock('TYPO3\\CMS\\Core\\Html\\HtmlParser', array('dummy'));
        
        $subject->expects($this->exactly(4))->method('getHtmlParser')->willReturn($htmlParser);
        
        $subject->expects($this->never())->method('replaceMetaTagInArray');
    
        $subject->modify($params);
        
        $this->assertEquals($expectedParams, $params);
    }
    
    /**
     * @test
     */
    public function modifyWithNewsArticleNoIndexSetAndMetaTagInMetaTagsFoundWillReturnHeaderDataWithNoIndexSet()
    {
        /** @var PostProcessHook|\PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface $subject */
        $subject = $this->getMock(
            'JWeiland\\NewsAntiRobots\\Hooks\\PostProcessHook',
            array(
                'getTxNewsNamespace',
                'getNewsId',
                'getNewsRepository',
                'getHtmlParser'
            )
        );
    
        $params = array(
            'headerData' => array(
                '<meta charset="UTF-8"><meta name="description" content="test">'
            ),
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="index, follow">',
                2 => '<meta name="description" content="test">'
            )
        );
    
        $expectedParams = array(
            'headerData' => array(
                '<meta charset="UTF-8"><meta name="description" content="test">'
            ),
            'metaTags' => array(
                0 => '<meta charset="UTF-8">',
                1 => '<meta name="robots" content="NOINDEX, follow">',
                2 => '<meta name="description" content="test">'
            )
        );
    
        $txNewsNamespace = array('news' => '10');
    
        $subject->expects($this->once())->method('getTxNewsNamespace')->willReturn($txNewsNamespace);
        $subject->expects($this->once())->method('getNewsId')->with($txNewsNamespace)->willReturn('10');
    
        /** @var NewsRepository|\PHPUnit_Framework_MockObject_MockObject $newsRepository */
        $newsRepository = $this->getMock(
            'GeorgRinger\\News\\Domain\\Repository\\NewsRepository',
            array('findByUid'),
            array(),
            '',
            false
        );
    
        /** @var News|\PHPUnit_Framework_MockObject_MockObject|AccessibleObjectInterface $newsArticle */
        $newsArticle = $this->getAccessibleMock('JWeiland\\NewsAntiRobots\\Domain\\Model\\News', array('dummy'));
        $newsArticle->_set('hasMetaRobotsNoIndexDefined', true);
    
        $newsRepository->expects($this->once())
            ->method('findByUid')
            ->with($txNewsNamespace['news'])
            ->willReturn($newsArticle);
    
        $subject->expects($this->once())->method('getNewsRepository')->willReturn($newsRepository);
    
        /** @var HtmlParser|\PHPUnit_Framework_MockObject_MockObject $htmlParser */
        $htmlParser = $this->getMock('TYPO3\\CMS\\Core\\Html\\HtmlParser', array('dummy'));
    
        $subject->expects($this->exactly(2))->method('getHtmlParser')->willReturn($htmlParser);
    
        $subject->modify($params);
    
        $this->assertEquals($expectedParams, $params);
    }
}