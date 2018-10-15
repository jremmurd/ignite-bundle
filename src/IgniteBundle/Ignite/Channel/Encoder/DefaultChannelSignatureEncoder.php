<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 30.09.2018
 * Time: 19:05
 */

namespace Juup\IgniteBundle\Ignite\Channel\Encoder;

use Juup\IgniteBundle\Constant\ChannelType;
use Juup\IgniteBundle\Ignite\Config;

/**
 * Class DefaultChannelSignatureEncoder
 */
class DefaultChannelSignatureEncoder implements ChannelSignatureEncoderInterface
{

    const NAMESPACE_DELIMITER = "___";

    /* @var Config $config */
    protected $config;

    /**
     * ChannelFactory constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param $identifier
     * @param array $parameters
     * @param bool $excludePrefix
     * @param string $namespace
     * @return string
     * @throws \Exception
     */
    public function encode($identifier, $parameters = [], $excludePrefix = false, $namespace = ""): string
    {
        $prefix = $this->getChannelPrefix($identifier);
        $parameters = $parameters === null ? [] : $parameters;

        $configParameters = $this->config->getChannelParameters($identifier);
        $strictParameters = $this->config->getChannelsConfigs()["strict_parameters"];

        if ($strictParameters && count($parameters) != count($configParameters)) {
            throw new \Exception("Expected channel parameters " . json_encode($configParameters) . ", but got " . json_encode(array_keys($parameters)) . " for channel $identifier..");
        }

        if ($parameters) {
            foreach ($parameters as $parameter => $value) {
                $this->validateChannelName($parameter);
                $parameterIsConfigured = in_array($parameter, $configParameters);

                if ($strictParameters && !$parameterIsConfigured) {
                    throw new \Exception("Expected channel parameters " . json_encode($configParameters) . ", but got " . json_encode(array_keys($parameters)) . " for channel $identifier.");
                }
            }
        }

        $name = !empty($parameters)
            ? ($identifier . "__" . implode("_", $parameters))
            : $identifier;

        $namespace = $namespace ?: $this->config->getCurrentChannelNamespaceName();

        return $excludePrefix
            ? ($namespace . self::NAMESPACE_DELIMITER . $name)
            : ($prefix . $namespace . self::NAMESPACE_DELIMITER . $name);
    }

    /**
     * @param $channelName
     * @return array|null
     * @throws \Exception
     */
    public function decode($channelName): ?array
    {
        $parts = explode(self::NAMESPACE_DELIMITER, $channelName);
        $prefixAndNamespace = explode("-", $parts[0]);

        if (count($prefixAndNamespace) > 1) {
            $prefix = $prefixAndNamespace[0];
            $namespace = $prefixAndNamespace[1];
        } else {
            $prefix = "";
            $namespace = $prefixAndNamespace[0];
        }
        $data = [];
        $data["type"] = trim($prefix, "-");
        $data["prefix"] = $prefix;
        $data["namespace"] = $namespace;

        $identifierAndParameters = explode("__", $parts[1]);
        $identifier = $identifierAndParameters[0];
        $data["identifier"] = $identifier;

        if ($identifierAndParameters[1] && ($parameters = $this->config->getChannelConfig($identifier)["parameters"])) {
            $parameterValues = explode("_", $identifierAndParameters[1]);

            foreach ($parameters as $index => $parameter) {
                if (!$parameterValues[$index]) {
                    continue;
                }
                $data["parameters"][$parameter] = $parameterValues[$index];
            }
        }

        return $data;
    }

    /**
     * @param $identifier
     * @return null|string
     * @throws \Exception
     */
    protected function getChannelPrefix($identifier): ?string
    {
        $prefix = "";

        if ($this->config->isPrivateChannel($identifier)) {
            $prefix = ChannelType::getPrefix(ChannelType::PRIVATE);
        } elseif ($this->config->isPresenceChannel($identifier)) {
            $prefix = ChannelType::getPrefix(ChannelType::PRESENCE);
        }

        return $prefix;
    }

    /**
     * @param string $text
     * @throws \Exception
     */
    public function validateChannelName(string $text)
    {
        $regexInner = "\$_a-zA-z0-9.";
        $regex = "[^$regexInner]";

        if (preg_match("/$regex/", $text)) {
            throw new \Exception("Invalid channel name or parameter, only characters matching the regex [$regexInner] are allowed. Text was $text.");
        }
    }
}