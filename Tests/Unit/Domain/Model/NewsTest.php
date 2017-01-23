<?php
namespace JWeiland\NewsAntiRobots\Tests\Unit\Domain\Model;

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

use JWeiland\NewsAntiRobots\Domain\Model\News;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Class NewsTest
 *
 * @package JWeiland\NewsAntiRobots\Domain\Model
 */
class NewsTest extends UnitTestCase {
    /**
     * @var News
     */
    protected $subject;
    
    /**
     * SetUp
     */
    public function setUp()
    {
        $this->subject = new News();
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
    public function getAntiRobotsNoIndexInitiallyReturnsFalse()
    {
        $this->assertSame(
            false,
            $this->subject->getAntiRobotsNoIndex()
        );
    }
    
    /**
     * @test
     */
    public function setAntiRobotsNoIndexSetsAntiRobotsNoIndex()
    {
        $this->subject->setAntiRobotsNoIndex(true);
        
        $this->assertSame(
            true,
            $this->subject->getAntiRobotsNoIndex()
        );
    }
    
    /**
     * @test
     */
    public function setAntiRobotsNoIndexWithIntResultsInBool()
    {
        $this->subject->setAntiRobotsNoIndex(1);
        
        $this->assertSame(
            true,
            $this->subject->getAntiRobotsNoIndex()
        );
    }
    
    /**
     * @test
     */
    public function setAntiRobotsNoIndexWithEmptyStringResultsInBool()
    {
        $this->subject->setAntiRobotsNoIndex('');
    
        $this->assertSame(
            false,
            $this->subject->getAntiRobotsNoIndex()
        );
    }
    
    /**
     * @test
     */
    public function setAntiRobotsNoIndexWithStringResultsInBool()
    {
        $this->subject->setAntiRobotsNoIndex('test');
        
        $this->assertSame(
            true,
            $this->subject->getAntiRobotsNoIndex()
        );
    }
    
    /**
     * @test
     */
    public function setAntiRobotsNoIndexWithNullResultsInBool()
    {
        $this->subject->setAntiRobotsNoIndex(null);
    
        $this->assertSame(
            false,
            $this->subject->getAntiRobotsNoIndex()
        );
    }
}