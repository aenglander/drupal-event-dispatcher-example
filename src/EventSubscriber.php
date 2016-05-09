<?php
/**
 * Copyright (c) 2016 Adam L. Englander
 * See LICENSE.txt file at the root of the project for licensing.
 * 
 * Event subscriber and request processor. It handles all of the request based
 * logic so the BlackList service can be very specific. This is also how you would
 * utilize a 3rd party library with your own event subscriber.
 */
namespace Drupal\drupal_event_dispatcher_example;


use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class EventSubscriber
 * @package Drupal\drupal_event_dispatcher_example
 */
class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var BlackListService
     */
    private $blackListService;

    /**
     * EventSubscriber constructor.
     * @param LoggerInterface $logger
     * @param BlackListService $blackListService
     */
    public function __construct(LoggerInterface $logger, BlackListService $blackListService)
    {
        $this->logger = $logger;
        $this->blackListService = $blackListService;
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => 'onKernelRequestEvent');
    }

    /**
     * Handle the kernel.request event. If the request is a "Master" request and the path is not an "admin" path
     * and the IP is black listed, throw an AccessDeniedHttpException to deny access to the page.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequestEvent(GetResponseEvent $event) {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            $this->logger->debug("Request is NOT MASTER. Not processing blacklist");
        } elseif (strpos($event->getRequest()->getPathInfo(), "/admin") !== false) {
            $this->logger->debug("Request path \"" . $event->getRequest()->getPathInfo() . " is admin. Not processing blacklist");
        } else {
            $this->logger->debug(
                "Request is MASTER and path \"" .
                $event->getRequest()->getPathInfo() .
                "\" is not admin, checking requester IP against blacklist"
            );
            $clientIP = $event->getRequest()->getClientIp();
            $blacklisted = $this->blackListService->isBlackListedIP($clientIP);
            if ($blacklisted) {
                $this->logger->notice("Requester's IP address {$clientIP} is blacklisted");
                throw new AccessDeniedHttpException();
            } else {
                $this->logger->debug("Requester's IP address {$clientIP} is not blacklisted");
            }
        }
    }
}