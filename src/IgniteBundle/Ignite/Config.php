<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 16:02
 */

namespace JRemmurd\IgniteBundle\Ignite;


use JRemmurd\IgniteBundle\Constant\ChannelType;

class Config
{
    /* @var Radio $radio */
    protected $radio;

    /* @var array $config */
    protected $config;

    /**
     * Config constructor.
     * @param $config
     * @param Radio $radio
     */
    public function __construct($config, Radio $radio)
    {
        $this->config = $config;
        $this->radio = $radio;
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->config;
    }

    /**
     * @return Radio
     */
    protected function getRadio()
    {
        return $this->radio;
    }

    /**
     * @return array
     */
    public function getChannelNamespaces()
    {
        return @array_keys($this->getChannelsConfigs()["namespaces"]);
    }

    /**
     * @return string
     */
    public function getCurrentChannelNamespaceName()
    {
        $currentChannelNamespace = $this->getRadio()->getChannelNamespace();
        return $currentChannelNamespace;
    }

    /**
     * @return mixed
     */
    public function getCurrentChannelNamespace()
    {
        return $this->getChannelNamespace($this->getCurrentChannelNamespaceName());
    }

    /**
     * @param string $namespace
     * @return mixed
     */
    public function getChannelNamespace(string $namespace)
    {
        $config = $this->get()["channels"]["namespaces"][$namespace];
        $config["namespace"] = $namespace;
        return $config;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getChannelConfig(string $name)
    {
        foreach (ChannelType::getAll() as $type) {
            try {
                $typeConfigs = $this->getChannelConfigsByType($type);
            } catch (\Exception $e) {
                // nothing to do here
            }
            if (array_key_exists($name, $typeConfigs)) {
                $typeConfig = $typeConfigs[$name];
                $typeConfig["type"] = $type;
                return $typeConfig;
            }
        }

        return [];
    }

    /**
     * @return array
     */
    public function getChannelNames()
    {
        return array_keys($this->getChannelConfigs());
    }

    /**
     * @return array
     */
    public function getChannelsConfigs()
    {
        return $this->get()["channels"];
    }

    /**
     * @return array
     */
    public function getChannelConfigs()
    {
        $typeConfigs = $this->getCurrentChannelNamespace()["channels"];

        $configs = [];
        foreach ($typeConfigs as $type => $typeConfig) {
            $configs = array_merge($configs, $typeConfig);
        }

        return $configs;
    }

    /**
     * @param $name
     * @return array
     */
    public function getChannelParameters($name)
    {
        return @$this->getChannelConfig($name)["parameters"] ?: [];
    }

    /**
     * @param $type
     * @return array
     * @throws \Exception
     */
    public function getChannelConfigsByType($type): array
    {
        if (!in_array($type, ChannelType::getAll())) {
            throw new \Exception("Invalid channel type.");
        }

        $config = $this->get();
        $namespace = $this->getCurrentChannelNamespaceName();

        $typeConfigs = $config["channels"]["namespaces"][$namespace]["channels"][$type];

        if (!is_array($typeConfigs)) {
            return [];
        }

        return $typeConfigs;
    }

    /**
     * @param $name
     * @param $type
     * @return bool
     * @throws \Exception
     */
    protected function isChannelType($name, $type)
    {
        return $this->getChannelConfig($name)["type"] == $type;
    }

    /**
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function isPresenceChannel(string $name)
    {
        return $this->isChannelType($name, ChannelType::PRESENCE);
    }

    /**
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function isPrivateChannel(string $name)
    {
        return $this->isChannelType($name, ChannelType::PRIVATE);
    }

    /**
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function isPublicChannel(string $name)
    {
        return $this->isChannelType($name, ChannelType::PUBLIC) ||
            (!$this->isPresenceChannel($name) && !$this->isPrivateChannel($name));
    }

    /**
     * @param string $driver
     * @return mixed
     * @throws \Exception
     */
    public function getDriverConfig($driver = "")
    {
        $config = $this->get();

        $driver = $driver ?: $this->getDefaultDriver();
        if (!array_key_exists($driver, $config["drivers"])) {
            throw new \Exception("No config found for specified driver [{$driver}].");
        } else {
            $driverConfig = $config["drivers"][$driver];
        }

        return $driverConfig;
    }

    /**
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->getCurrentChannelNamespace()["default_driver_name"] ?: $this->get()["channels"]["default_driver_name"];
    }
}