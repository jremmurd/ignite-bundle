<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 30.09.2018
 * Time: 19:05
 */

namespace JRemmurd\IgniteBundle\Ignite\Channel\Encoder;

use JRemmurd\IgniteBundle\Constant\ChannelType;
use JRemmurd\IgniteBundle\Ignite\Config;

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
     * @param $name
     * @param array $parameters
     * @param bool $excludePrefix
     * @param string $namespace
     * @return string
     * @throws \Exception
     */
    public function encode($name, $parameters = [], $excludePrefix = false, $namespace = ""): string
    {
        $prefix = $this->getChannelPrefix($name);
        $parameters = $parameters === null ? [] : $parameters;

        $configParameters = $this->config->getChannelParameters($name);
        $strictParameters = $this->config->getChannelsConfigs()["strict_parameters"];

        if ($strictParameters && count($parameters) != count($configParameters)) {
            throw new \Exception("Expected channel parameters " . json_encode($configParameters) . ", but got " . json_encode(array_keys($parameters)) . " for channel $name..");
        }

        if ($parameters) {
            foreach ($parameters as $parameter => $value) {
                $this->validateChannelSignature($parameter);
                $parameterIsConfigured = in_array($parameter, $configParameters);

                if ($strictParameters && !$parameterIsConfigured) {
                    throw new \Exception("Expected channel parameters " . json_encode($configParameters) . ", but got " . json_encode(array_keys($parameters)) . " for channel $name.");
                }
            }
        }

        $signature = !empty($parameters)
            ? ($name . "__" . implode("_", $parameters))
            : $name;

        $namespace = $namespace ?: $this->config->getCurrentChannelNamespaceName();

        return $excludePrefix
            ? ($namespace . self::NAMESPACE_DELIMITER . $signature)
            : ($prefix . $namespace . self::NAMESPACE_DELIMITER . $signature);
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

        $nameAndParameters = explode("__", $parts[1]);
        $name = $nameAndParameters[0];
        $data["name"] = $name;

        if ($nameAndParameters[1] && ($parameters = $this->config->getChannelConfig($name)["parameters"])) {
            $parameterValues = explode("_", $nameAndParameters[1]);

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
     * @param $name
     * @return null|string
     * @throws \Exception
     */
    protected function getChannelPrefix($name): ?string
    {
        $prefix = "";

        if ($this->config->isPrivateChannel($name)) {
            $prefix = ChannelType::getPrefix(ChannelType::PRIVATE);
        } elseif ($this->config->isPresenceChannel($name)) {
            $prefix = ChannelType::getPrefix(ChannelType::PRESENCE);
        }

        return $prefix;
    }

    /**
     * @param string $text
     * @throws \Exception
     */
    public function validateChannelSignature(string $text)
    {
        $regexInner = "\$_a-zA-z0-9.";
        $regex = "[^$regexInner]";

        if (preg_match("/$regex/", $text)) {
            throw new \Exception("Invalid channel name or parameter, only characters matching the regex [$regexInner] are allowed. Text was $text.");
        }
    }
}