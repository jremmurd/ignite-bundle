<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:18
 */

namespace JRemmurd\IgniteBundle\Ignite\Channel;


use JRemmurd\IgniteBundle\Ignite\Driver\DriverInterface;

interface ChannelInterface
{

    public function subscribe();

    public function unsubscribe();

    /**
     * @param EventInterface $event
     * @param string $socketId
     * @return ChannelInterface
     */
    public function publish(EventInterface $event, string $socketId = ""): ChannelInterface;

    /**
     * @return DriverInterface[]
     */
    public function getDrivers(): array;

    /**
     * @param string $name
     * @return DriverInterface|null
     */
    public function getDriver(string $name);

    /**
     * @return string
     */
    public function getSignature(): string;

}