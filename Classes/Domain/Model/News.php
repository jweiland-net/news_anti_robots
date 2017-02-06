<?php
namespace JWeiland\NewsAntiRobots\Domain\Model;

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

/**
 * Class News
 *
 * @package JWeiland\NewsAntiRobots\Domain\Model
 */
class News {
    /**
     * @var bool
     */
    protected $hasMetaRobotsNoIndexDefined = false;
    
    /**
     * Set $antiRobotsNoIndex
     *
     * @param bool $hasMetaRobotsNoIndexDefined
     *
     * @return void
     */
    public function setHasMetaRobotsNoIndexDefined($hasMetaRobotsNoIndexDefined)
    {
        $this->hasMetaRobotsNoIndexDefined = (bool)$hasMetaRobotsNoIndexDefined;
    }
    
    /**
     * Get $antiRobotsNoIndex
     *
     * @return bool
     */
    public function getHasMetaRobotsNoIndexDefined()
    {
        return $this->hasMetaRobotsNoIndexDefined;
    }
}