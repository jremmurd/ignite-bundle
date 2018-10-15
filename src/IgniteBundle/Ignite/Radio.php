<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 13:51
 */

namespace Juup\IgniteBundle\Ignite;


use Juup\IgniteBundle\Constant\ChannelType;
use Juup\IgniteBundle\Constant\Intent;
use Juup\IgniteBundle\Ignite\Channel\ChannelFactory;
use Juup\IgniteBundle\Ignite\Channel\ChannelInterface;
use Juup\IgniteBundle\Ignite\Channel\Encoder\ChannelSignatureEncoderInterface;
use Juup\IgniteBundle\Ignite\Driver\DriverInterface;
use Juup\IgniteBundle\Ignite\Driver\ScriptDriverInterface;
use Psr\Container\ContainerInterface;

/**
 * Class Radio
 * @package Juup\IgniteBundle\Ignite
 */
class Radio implements RadioInterface
{
    protected $channels = [];

    /* @var string $channelNamespace */
    protected $channelNamespace;

    /* @var ChannelFactory $channelFactory */
    protected $channelFactory;

    /* @var ChannelSignatureEncoderInterface */
    protected $channelNameEncoder;

    /* @var Config $config */
    protected $config;

    /* @var ContainerInterface $driverLocator */
    protected $driverLocator;

    /**
     * Radio constructor.
     * @param ChannelSignatureEncoderInterface $channelNameEncoder
     * @param ChannelFactory $channelFactory
     * @param Config $config
     * @param ContainerInterface $driverLocator
     */
    public function __construct(ChannelSignatureEncoderInterface $channelNameEncoder, ChannelFactory $channelFactory, Config $config, ContainerInterface $driverLocator)
    {
        $this->channelFactory = $channelFactory;
        $this->channelNameEncoder = $channelNameEncoder;
        $this->config = $config;
        $this->driverLocator = $driverLocator;
    }

    /**
     * @param string $identifier
     * @param array $parameters
     * @param string $type
     * @return ChannelInterface
     * @throws \Exception
     */
    public function getChannel(string $identifier, $parameters = [], string $type = ""): ChannelInterface
    {
        $identifier = ChannelType::removePrefix($identifier);

        $name = $this->channelNameEncoder->encode($identifier, $parameters);
        $nameWithoutPrefix = $this->channelNameEncoder->encode($identifier, $parameters, true);

        $this->channelNameEncoder->validateChannelName($nameWithoutPrefix);

        if (!$this->channels[$name]) {
            $this->channels[$name] = $this->channelFactory->createByConfig($identifier, $name);
        }

        return $this->channels[$name];
    }

    /**
     * @param string $identifier
     * @param array $parameters
     * @return ChannelInterface
     * @throws \Exception
     */
    public function getPublicChannel(string $identifier, $parameters = [])
    {
        return $this->getChannel($identifier, $parameters, ChannelType::PUBLIC);
    }

    /**
     * @param string $identifier
     * @param array $parameters
     * @return ChannelInterface
     * @throws \Exception
     */
    public function getPrivateChannel(string $identifier, $parameters = [])
    {
        return $this->getChannel($identifier, $parameters, ChannelType::PRIVATE);
    }

    /**
     * @param string $identifier
     * @param array $parameters
     * @return ChannelInterface
     * @throws \Exception
     */
    public function getPresenceChannel(string $identifier, $parameters = [])
    {
        return $this->getChannel($identifier, $parameters, ChannelType::PRESENCE);
    }

    /**
     * @return ChannelInterface[]
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * @return array
     */
    public function getChannelIntents(): array
    {
        $intents = [];
        $channels = $this->getChannels();

        foreach ($channels as $channel) {
            $intent = $channel->getIntent();
            $intents[$intent][] = $channel->getName();
        }

        return $intents;
    }

    /**
     * @return array
     */
    public function getSubscriptions()
    {
        return $this->getChannelIntents()[Intent::SUBSCRIBE] ?: [];
    }

    /**
     * @return array
     */
    public function getUnSubscriptions()
    {
        return $this->getChannelIntents()[Intent::UNSUBSCRIBE] ?: [];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDriversInUse()
    {
        $mainDriverName = $this->config->getDefaultDriver();
        $mainDriverConfig = $this->config->getDriverConfig($mainDriverName);

        $allDrivers = [
            $mainDriverName => $this->driverLocator->get($mainDriverConfig["service_id"])
        ];

        foreach ($this->getChannels() as $channel) {
            if (!$channel->hasIntent()) {
                continue;
            }

            $drivers = $channel->getDrivers();

            foreach ($drivers as $name => $driver) {
                if (!$driver instanceof ScriptDriverInterface) {
                    continue;
                }

                if ($allDrivers[$driver->getName()]) {
                    continue;
                }

                $allDrivers[$driver->getName()] = $driver;
            }
        }

        return $allDrivers;
    }

    /**
     * @return array|string
     * @throws \Exception
     */
    public function getDriverInitScripts()
    {
        $initEvaluations = [];
        $allDrivers = $this->getDriversInUse();

        foreach ($allDrivers as $driver) {
            /* @var DriverInterface $driver */
            if ($driver instanceof ScriptDriverInterface) {
                $initEvaluations[$driver->getName()] .= $driver->getInitScript();
            }
        }

        return $initEvaluations;
    }

    /**
     * @param bool $addInitScript
     * @param array $initConfig
     * @return array
     * @throws \Exception
     */
    public function getDriverScripts($addInitScript = true, $initConfig = [])
    {
        $allDrivers = $this->getDriversInUse();
        $scripts = [];

        foreach ($allDrivers as $driverName => $driver) {
            if ($driver instanceof ScriptDriverInterface) {
                $scripts[$driverName] = $driver->getScript($addInitScript, $initConfig);
            }
        }

        return $scripts;
    }

    /**
     * @param bool $addInitScript
     * @param array $initConfig
     * @return array|string
     * @throws \Exception
     */
    public function getScript($addInitScript = true, $initConfig = []): ?string
    {
        $totalScript = "";
        $scripts = $this->getDriverScripts($addInitScript, $initConfig);

        foreach ($scripts as $script) {
            $totalScript .= $script;
        }

        return $totalScript;
    }

    /**
     * @return string
     */
    public function getChannelNamespace()
    {
        return $this->channelNamespace;
    }

    /**
     * @param string $channelNamespace
     */
    public function setChannelNamespace($channelNamespace)
    {
        $this->channelNamespace = $channelNamespace;
    }
}