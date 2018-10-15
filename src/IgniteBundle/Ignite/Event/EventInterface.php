<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:18
 */

namespace JRemmurd\IgniteBundle\Ignite\Channel;


use Carbon\Carbon;
use JRemmurd\IgniteBundle\Ignite\Driver\DriverInterface;

interface EventInterface
{

    public function getName();

    public function getData();

    public function getCreatedAt(): Carbon;

}