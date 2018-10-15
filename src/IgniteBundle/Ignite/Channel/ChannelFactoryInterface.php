<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 17.06.2018
 * Time: 17:02
 */

namespace JRemmurd\IgniteBundle\Ignite\Channel;


interface ChannelFactoryInterface
{
    public function createByConfig(string $identifier = "", string $name = "", $parameters = [], string $type = ""): ?ChannelInterface;

}