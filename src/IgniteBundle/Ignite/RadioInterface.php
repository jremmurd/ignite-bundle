<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:07
 */

namespace JRemmurd\IgniteBundle\Ignite;


use JRemmurd\IgniteBundle\Ignite\Channel\ChannelInterface;

interface RadioInterface
{

    /**
     * @param string $name
     * @param array $parameters
     * @param string $type
     * @return ChannelInterface
     */
    public function getChannel(string $name, $parameters = [], string $type = ""): ChannelInterface;

    /**
     * @return ChannelInterface[]
     */
    public function getChannels(): array;

    /**
     * @return array
     */
    public function getDriverScripts();

    /**
     * @return array
     */
    public function getDriverInitScripts();

    /**
     * @return null|string
     */
    public function getScript(): ?string;

    /**
     * @param string $namespace
     */
    public function setChannelNamespace(string $namespace);
}