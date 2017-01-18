<?php
namespace JWeiland\NewsAntiRobots\Slots;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class DispatcherSlot
 *
 * @package JWeiland\NewsAntiRobots\Domain\Model
 */
class DispatcherSlot
{
    /**
     * dispatchSlot to modify header tag
     *
     * @param RequestInterface|Request $request The request to dispatch
     * @param ResponseInterface|Response $response The response, to be modified by the controller
     *
     * @return void
     */
    public function afterRequestDispatchSlot(RequestInterface $request, ResponseInterface $response)
    {
        if (
            $request->getControllerExtensionName() === 'News' &&
            $request->getControllerName() === 'News' &&
            $request->getControllerActionName() === 'detail'
        ) {
            /** @var NewsRepository $newsRepository */
            $newsRepository = GeneralUtility::makeInstance('GeorgRinger\\News\\Domain\\Repository\\NewsRepository');
            
            /** @var News $newsObject */
            $newsObject = $newsRepository->findByUid($request->getArgument('news'));
            
            if ($newsObject->getAntiRobotsNoIndex()) {
                
            }
        }
    }
}