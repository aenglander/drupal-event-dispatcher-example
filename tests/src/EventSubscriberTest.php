<?php
/**
 * Copyright (c) 2016 Adam L. Englander
 * See LICENSE.txt file at the root of the project for licensing.
 */

namespace Drupal\Test\drupal_event_dispatcher_example;

use Drupal\drupal_event_dispatcher_example\EventSubscriber;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class EventSubscriberTest
 * @package Drupal\Test\drupal_event_dispatcher_example
 */
class EventSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Drupal\drupal_event_dispatcher_example\EventSubscriber
     */
    private $eventSubscriber;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Drupal\drupal_event_dispatcher_example\BlackListService
     */
    private $blackListService;

    /**
     * @var HttpKernelInterface
     */
    private $kernel;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var GetResponseEvent
     */
    private $event;

    public function testOnKernelRequestEventRequestNotMasterDoesNotCheckBlackList()
    {
        $this->eventSubscriber->onKernelRequestEvent(new GetResponseEvent(
            $this->kernel,
            $this->request,
            HttpKernelInterface::SUB_REQUEST
        ));
        \Phake::verify($this->blackListService, \Phake::never())->isBlackListedIP(\Phake::anyParameters());
    }

    public function testOnKernelRequestEventRequestMasterRequestDoesCheckBlackList()
    {
        $this->eventSubscriber->onKernelRequestEvent($this->event);
        \Phake::verify($this->blackListService)->isBlackListedIP(\Phake::anyParameters());
    }

    public function testOnKernelRequestEventRequestPathIsAdminDoesNotCheckBlackList()
    {
        \Phake::when($this->request)->getPathInfo()->thenReturn('/admin');
        $this->eventSubscriber->onKernelRequestEvent($this->event);
        \Phake::verify($this->blackListService, \Phake::never())->isBlackListedIP(\Phake::anyParameters());
    }

    public function testOnKernelRequestEventRequestPathIsNotAdminDoesCheckBlackList()
    {
        $this->eventSubscriber->onKernelRequestEvent($this->event);
        \Phake::verify($this->blackListService)->isBlackListedIP(\Phake::anyParameters());
    }


    public function testOnKernelRequestEventPassesRequestIPToIsBlackListCheck()
    {
        $expected = "1.2.3.4";
        \Phake::when($this->request)->getClientIp()->thenReturn($expected);
        $this->eventSubscriber->onKernelRequestEvent($this->event);
        \Phake::verify($this->blackListService)->isBlackListedIP($expected);
    }

    public function testOnKernelRequestThrowsUnauthorizedHttpExceptionWhenIpIsBlackListed()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException');
        \Phake::when($this->blackListService)->isBlackListedIP(\Phake::anyParameters())->thenReturn(true);
        $this->eventSubscriber->onKernelRequestEvent($this->event);
    }

    public function testOnKernelRequestLogsAlertMessageWhenIpIsBlackListed()
    {
        try {
            \Phake::when($this->blackListService)->isBlackListedIP(\Phake::anyParameters())->thenReturn(true);
            $clientIP = "1.1.1.1";
            \Phake::when($this->request)->getClientIp()->thenReturn($clientIP);
            $this->eventSubscriber->onKernelRequestEvent($this->event);
            \Phake::verify($this->logger)->alert("Requester's IP address {$clientIP} is blacklisted");

        } catch (AccessDeniedHttpException $e) {
            return;
        }
    }

    public function testOnKernelRequestDoesNotLogAlertMessageWhenIpIsNotBlackListed()
    {
        \Phake::when($this->blackListService)->isBlackListedIP(\Phake::anyParameters())->thenReturn(false);
        $this->eventSubscriber->onKernelRequestEvent($this->event);
        \Phake::verify($this->logger, \Phake::never())->alert(\Phake::anyParameters());
    }

    public function testGetSubscribedEventsIncludesTheKernelRequestEventMappedToOnKernelRequestEvent()
    {
        $actual = EventSubscriber::getSubscribedEvents();
        $this->assertArraySubset(array(KernelEvents::REQUEST => 'onKernelRequestEvent'), $actual);
    }

    protected function setUp()
    {
        $this->logger = \Phake::mock('\Psr\Log\LoggerInterface');
        $this->blackListService = \Phake::mock('\Drupal\drupal_event_dispatcher_example\BlackListService');
        $this->eventSubscriber = new EventSubscriber($this->logger, $this->blackListService);
        $this->kernel = \Phake::mock('\Symfony\Component\HttpKernel\HttpKernelInterface');
        $this->request = \Phake::mock('\Symfony\Component\HttpFoundation\Request');
        $this->event = new GetResponseEvent($this->kernel, $this->request, HttpKernelInterface::MASTER_REQUEST);
    }

    protected function tearDown()
    {
        $this->logger = null;
        $this->blackListService = null;
        $this->eventSubscriber = null;
        $this->kernel = null;
        $this->request = null;
        $this->event = null;
    }
}
