<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 15:40
 */

namespace Juup\IgniteBundle\Ignite\Channel;


use Juup\IgniteBundle\Constant\ChannelType;
use Juup\IgniteBundle\Ignite\Channel\Encoder\ChannelSignatureEncoderInterface;
use Juup\IgniteBundle\Ignite\Config;
use Psr\Container\ContainerInterface;

/**
 * Class ChannelFactory
 * @package Juup\IgniteBundle\Ignite\Channel
 */
class ChannelFactory implements ChannelFactoryInterface
{
    /* @var Config $config */
    protected $config;

    /* @var ChannelSignatureEncoderInterface $channelNameEncoder */
    protected $channelNameEncoder;

    /* @var ContainerInterface $driverLocator */
    protected $driverLocator;

    /**
     * ChannelFactory constructor.
     * @param Config $config
     * @param ChannelSignatureEncoderInterface $channelNameEncoder
     * @param ContainerInterface $driverLocator
     */
    public function __construct(Config $config, ChannelSignatureEncoderInterface $channelNameEncoder, ContainerInterface $driverLocator)
    {
        $this->config = $config;
        $this->channelNameEncoder = $channelNameEncoder;
        $this->driverLocator = $driverLocator;
    }

    /**
     * @param string|null $identifier
     * @param string $name
     * @param array $parameters
     * @param string $type
     * @return ChannelInterface|null
     * @throws \Exception
     */
    public function createByConfig(string $identifier = "", string $name = "", $parameters = [], string $type = ""): ?ChannelInterface
    {
        if (!$identifier && $name) {
            $identifier = $this->channelNameEncoder->decode($name)["identifier"];
        } elseif (!$name && ($identifier && $parameters)) {
            $name = $this->channelNameEncoder->encode($identifier, $parameters);
        } elseif (!$name && !$identifier) {
            throw new \Exception("Identifier or name of channel must be provided.");
        }

        $name = $name ?: $identifier;

        if (!$type) {
            if ($this->config->isPresenceChannel($identifier)) {
                return new PresenceChannel($name, $this->config, $this->driverLocator, $this->channelNameEncoder);
            } elseif ($this->config->isPrivateChannel($identifier)) {
                return new PrivateChannel($name, $this->config, $this->driverLocator, $this->channelNameEncoder);
            } else {
                return new PublicChannel($name, $this->config, $this->driverLocator, $this->channelNameEncoder);
            }
        } else if (!in_array($type, ChannelType::getAll())) {
            throw new \Exception("Invalid channel type [{$type}].");
        }

        switch ($type) {
            case ChannelType::PRESENCE:
                return new PresenceChannel($name, $this->config, $this->driverLocator, $this->channelNameEncoder);
            case ChannelType::PRIVATE:
                return new PrivateChannel($name, $this->config, $this->driverLocator, $this->channelNameEncoder);
            case ChannelType::PUBLIC:
            default:
                return new PublicChannel($name, $this->config, $this->driverLocator, $this->channelNameEncoder);
        }
    }
}