<?php
/**
 * Copyright (c) 2016 Adam L. Englander
 * See LICENSE.txt file at the root of the project for licensing.
 * 
 * The BlackList Service is a very simple example of extracting
 * logic into a reusable service.
 */
namespace Drupal\drupal_event_dispatcher_example;

/**
 * Class BlackListService
 * @package Drupal\drupal_event_dispatcher_example
 */
class BlackListService
{
    /**
     * @var array
     */
    private $whiteList;

    /**
     * BlackListService constructor.
     * @param array $whiteList
     */
    public function __construct(array $whiteList)
    {
        $this->whiteList = $whiteList;
    }

    /**
     * Is the provided IP address black listed
     *
     * @param $clientIP String based IPv4 address. i.e. 127.0.0.1
     * @return bool
     */
    public function isBlackListedIP($clientIP)
    {
        return !in_array($clientIP, $this->whiteList);
    }
}