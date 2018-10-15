<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:18
 */

namespace Juup\IgniteBundle\Ignite\Channel;


use Carbon\Carbon;
use Juup\IgniteBundle\Ignite\Driver\DriverInterface;

interface EventInterface
{

    public function getName();

    public function getData();

    public function getCreatedAt(): Carbon;

}