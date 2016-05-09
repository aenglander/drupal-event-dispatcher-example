<?php
/**
 * Copyright (c) 2016 Adam L. Englander
 * See LICENSE.txt file at the root of the project for licensing.
 */

namespace Drupal\Test\drupal_event_dispatcher_example;

use Drupal\drupal_event_dispatcher_example\BlackListService;

/**
 * Class BlackListServiceTest
 * @package Drupal\Test\drupal_event_dispatcher_example
 */
class BlackListServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlackListService
     */
    private $blackListService;

    protected function setUp()
    {
        $this->blackListService = new BlackListService(array('1.1.1.1', '2.2.2.2', '3.3.3.3', '4.4.4.4'));
    }

    protected function tearDown()
    {
        $this->blackListService = null;
    }

    public function testIsBlackListedIPReturnsTrueWhenNotInWhiteList()
    {
        $this->assertTrue($this->blackListService->isBlackListedIP('1.2.3.4'));
    }

    public function testIsBlackListedIPReturnsFalseWhenInWhiteList()
    {
        $this->assertFalse($this->blackListService->isBlackListedIP('2.2.2.2'));
    }
}
