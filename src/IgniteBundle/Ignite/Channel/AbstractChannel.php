<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:57
 */

namespace JRemmurd\IgniteBundle\Ignite\Channel;


use JRemmurd\IgniteBundle\Constant\Intent;
use JRemmurd\IgniteBundle\Ignite\Channel\Encoder\ChannelSignatureEncoderInterface;
use JRemmurd\IgniteBundle\Ignite\Config;
use JRemmurd\IgniteBundle\Ignite\Driver\AbstractDriver;
use JRemmurd\IgniteBundle\Ignite\Driver\DriverInterface;
use JRemmurd\IgniteBundle\Ignite\Event\Notification;
use Psr\Container\ContainerInterface;

abstract class AbstractChannel implements ChannelInterface
{
    /* @var string $name */
    protected $name;

    /* @var DriverInterface[] $drivers */
    protected $drivers;

    /* @var array $config */
    protected $config;

    /* @var array|null $parentChannels */
    protected $parentChannels;

    /* @var string|null $intent */
    protected $intent;

    /* @var ContainerInterface $driverLocator */
    protected $driverLocator;

    /**
     * AbstractChannel constructor.
     * @param $name
     * @param Config $config
     * @param ContainerInterface $driverLocator
     * @param ChannelSignatureEncoderInterface $channelNameEncoder
     * @throws \Exception
     */
    public function __construct($name, Config $config, ContainerInterface $driverLocator, ChannelSignatureEncoderInterface $channelNameEncoder)
    {
        $this->name = $name;
        $this->config = $config;
        $this->driverLocator = $driverLocator;
        $this->drivers = [];

        $decodedChannelName = $channelNameEncoder->decode($name);
        $channelConfig = $config->getChannelConfig($decodedChannelName["identifier"]);

        if (method_exists($this, "validateChannelName") && !$this->validateChannelName()) {
            throw new \Exception("Invalid channel name: {$name}");
        }

        if (empty($channelConfig["drivers"])) {
            $defaultDriverName = $config->getDefaultDriver();
            $drivers = [$defaultDriverName];
        } else {
            $drivers = $channelConfig["drivers"];
        }

        $this->initializeDrivers($drivers);
        $this->initializeParentChannels();
    }

    /**
     * @param $drivers
     * @throws \Exception
     */
    protected function initializeDrivers($drivers)
    {
        $initializedDrivers = [];
        foreach ($drivers as $driver) {
            $initializedDrivers[$driver] = $this->locateDriverByName($driver);
        }

        $this->drivers = array_merge($this->drivers, $initializedDrivers);
        uasort($this->drivers, function (AbstractDriver $a, AbstractDriver $b) {
            return $a->getPriority() < $b->getPriority();
        });
    }

    /**
     * @param $driverName
     * @return mixed
     * @throws \Exception
     */
    protected function locateDriverByName($driverName)
    {
        $driverConfig = $this->config->getDriverConfig($driverName);
        $driverServiceId = $driverConfig["service_id"];

        /* @var DriverInterface $driverService */
        return $this->driverLocator->get($driverServiceId);
    }

    /**
     * @param string $channelName
     * @throws \Exception
     */
    protected function addDriversFromChannel(string $channelName)
    {
        $channelConfig = $this->config->getChannelConfig($channelName);
        $this->initializeDrivers($channelConfig["drivers"] ?: []);
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function initializeParentChannels()
    {
        $currentChannelParts = explode(".", $this->getName());

        if (!$this->parentChannels) {
            foreach ($this->config->getChannelNames() as $channelName) {
                foreach ($currentChannelParts as $i => $currentChannelPart) {
                    if ($channelName == $this->getName()) {
                        continue;
                    }

                    array_pop($currentChannelParts);
                    $tempParentChannel = implode(".", $currentChannelParts);

                    if ($tempParentChannel == $channelName) {
                        $this->parentChannels[] = $tempParentChannel;
                        $this->addDriversFromChannel($tempParentChannel);
                    }
                }
            }
        }

        return $this->parentChannels;
    }

    /**
     * @param bool $ignoreParents
     * @return array
     */
    protected function getRelevantChannels($ignoreParents = false)
    {
        $channels = [$this->getName()];

        if ($ignoreParents) {
            return $channels;
        }

        if ($parentChannels = $this->getParentChannels()) {
            $channels = array_merge($channels, $parentChannels);
        }

        return $channels;
    }

    /**
     * @param EventInterface $event
     * @param string $socketId
     * @param bool $ignoreParents
     * @return AbstractChannel
     */
    public function publish($event, string $socketId = "", $ignoreParents = false): ChannelInterface
    {
        $socketId = empty($socketId) ? null : $socketId;
        $channels = $this->getRelevantChannels($ignoreParents);

        if (method_exists($event, "addNotificationData")) {
            /* @var Notification $event */
            $event->addNotificationData("channelName", Notification::DATA_TYPE_TEXT, $this->getName());
        }

        foreach ($this->getDrivers() as $driver) {
            $driver->push($channels, $event, $socketId);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function subscribe()
    {
        $this->intent = Intent::SUBSCRIBE;

        foreach ($this->getDrivers() as $driverService) {
            $driverService->onSubscribe($this->getName());
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function unsubscribe()
    {
        $this->intent = Intent::UNSUBSCRIBE;

        foreach ($this->getDrivers() as $driverService) {
            $driverService->onUnSubscribe($this->getName());
        }

        return $this;
    }

    /**
     * @param string $name
     * @return DriverInterface|null
     */
    public function getDriver(string $name)
    {
        return $this->drivers[$name];
    }

    /**
     * @return DriverInterface[]
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array|null
     */
    public function getParentChannels(): ?array
    {
        return $this->parentChannels;
    }

    /**
     * @return bool
     */
    public function hasIntent(): bool
    {
        return !empty($this->intent);
    }

    /**
     * @return null|string
     */
    public function getIntent(): ?string
    {
        return $this->intent;
    }
}