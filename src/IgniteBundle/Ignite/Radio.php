<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 13:51
 */

namespace JRemmurd\IgniteBundle\Ignite;


use JRemmurd\IgniteBundle\Constant\ChannelType;
use JRemmurd\IgniteBundle\Constant\Intent;
use JRemmurd\IgniteBundle\Ignite\Channel\ChannelFactory;
use JRemmurd\IgniteBundle\Ignite\Channel\ChannelInterface;
use JRemmurd\IgniteBundle\Ignite\Channel\Encoder\ChannelSignatureEncoderInterface;
use JRemmurd\IgniteBundle\Ignite\Driver\DriverInterface;
use JRemmurd\IgniteBundle\Ignite\Driver\ScriptDriverInterface;
use Psr\Container\ContainerInterface;

/**
 * Class Radio
 * @package JRemmurd\IgniteBundle\Ignite
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
     * @param string $name
     * @param array $parameters
     * @param string $type
     * @return ChannelInterface
     * @throws \Exception
     */
    public function getChannel(string $name, $parameters = [], string $type = ""): ChannelInterface
    {
        $name = ChannelType::removePrefix($name);

        $signature = $this->channelNameEncoder->encode($name, $parameters);
        $signatureWithoutPrefix = $this->channelNameEncoder->encode($name, $parameters, true);

        $this->channelNameEncoder->validateChannelSignature($signatureWithoutPrefix);

        if (!$this->channels[$signature]) {
            $this->channels[$signature] = $this->channelFactory->createByConfig($name, $signature);
        }

        return $this->channels[$signature];
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return ChannelInterface
     * @throws \Exception
     */
    public function getPublicChannel(string $name, $parameters = [])
    {
        return $this->getChannel($name, $parameters, ChannelType::PUBLIC);
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return ChannelInterface
     * @throws \Exception
     */
    public function getPrivateChannel(string $name, $parameters = [])
    {
        return $this->getChannel($name, $parameters, ChannelType::PRIVATE);
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return ChannelInterface
     * @throws \Exception
     */
    public function getPresenceChannel(string $name, $parameters = [])
    {
        return $this->getChannel($name, $parameters, ChannelType::PRESENCE);
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
            $intents[$intent][] = $channel->getSignature();
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