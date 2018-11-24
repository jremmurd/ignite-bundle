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
    /**
     * @param string $name
     * @param string $signature
     * @param array $parameters
     * @param string $type
     * @return ChannelInterface|null
     */
    public function createByConfig(string $name = "", string $signature = "", $parameters = [], string $type = ""): ?ChannelInterface;

}