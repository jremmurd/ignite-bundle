<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 30.09.2018
 * Time: 20:57
 */

namespace JRemmurd\IgniteBundle\Ignite\Driver;

/**
 * Class AbstractScriptDriver
 * @package JRemmurd\IgniteBundle\Ignite\Driver
 */
abstract class AbstractScriptDriver extends AbstractDriver implements ScriptDriverInterface
{
    /* @var array $subscriptions */
    protected $subscriptions = [];

    /* @var array $unsubscriptions */
    protected $unsubscriptions = [];

    /**
     * @param string $channelName
     * @return bool
     */
    public function validateChannelName(string $channelName)
    {
        return (bool)preg_match("/^(.*)[a-zA-Z0-9_]$/", $channelName);
    }

    /**
     * @param $channelNames
     */
    public function onSubscribe($channelNames)
    {
        if (!is_array($channelNames)) {
            $channelNames = [$channelNames];
        }

        $this->subscriptions = array_merge($this->subscriptions, $channelNames);
    }

    /**
     * @param $channelNames
     */
    public function onUnsubscribe($channelNames)
    {
        $this->unsubscriptions = array_merge($this->unsubscriptions, $channelNames);
    }

    /**
     * @return array
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }

    /**
     * @return array
     */
    public function getUnsubscriptions(): array
    {
        return $this->unsubscriptions;
    }
}