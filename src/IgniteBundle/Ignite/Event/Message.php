<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 17:53
 */

namespace JRemmurd\IgniteBundle\Ignite\Channel;


class Message extends AbstractEvent
{
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->name = "message";
    }

}