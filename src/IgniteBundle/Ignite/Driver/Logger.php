<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:57
 */

namespace JRemmurd\IgniteBundle\Ignite\Driver;


use JRemmurd\IgniteBundle\Ignite\Channel\EventInterface;
use JRemmurd\IgniteBundle\Ignite\Radio;
use Pimcore\Log\ApplicationLogger;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Logger
 * @package JRemmurd\IgniteBundle\Ignite\Driver
 */
class Logger extends AbstractDriver
{
    /* @var ApplicationLogger $logger */
    protected $logger;

    /* @var Radio $radio */
    protected $radio;

    /**
     * Logger constructor.
     * @param ApplicationLogger $logger
     * @param Radio $radio
     */
    public function __construct(ApplicationLogger $logger, Radio $radio)
    {
        $this->logger = $logger;
        $this->radio = $radio;
        $logger->setComponent($this->getComponent());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "logger";
    }

    protected function getComponent()
    {
        return "Ignite - Logger - {$this->radio->getChannelNamespace()}";
    }

    /**
     * @param $channels
     * @param EventInterface $event
     * @param null $socketId
     */
    public function push($channels, EventInterface $event, $socketId = null)
    {
        $this->logger
            ->info(json_encode($channels, JSON_PRETTY_PRINT) . " " . json_encode($event->getData(), JSON_PRETTY_PRINT),
                ["relatedObject" => @$event->getData()["targetUser"]]
            );
    }

    /**
     * @param $channelNames
     */
    public function onSubscribe($channelNames)
    {
        if (!is_array($channelNames)) {
            $channelNames = [$channelNames];
        }

        $this->logger->info("Subscribe: " . json_encode($channelNames, JSON_PRETTY_PRINT));
    }

    /**
     * @param string $channelName
     */
    public function onUnsubscribe($channelNames)
    {
        if (!is_array($channelNames)) {
            $channelNames = [$channelNames];
        }

        $this->logger->info("Unsubscribe: " . json_encode($channelNames, JSON_PRETTY_PRINT));
    }

    /**
     * @param Request $request
     * @param bool $isAuthenticated
     * @param string $userId
     * @param null $presenceData
     */
    public function getAuthPresenceResponse(Request $request, bool $isAuthenticated, string $userId, $presenceData = null)
    {
        // nothing to do here
    }

    /**
     * @param Request $request
     * @param bool $isAuthenticated
     * @param string $userId
     */
    public function getAuthPrivateResponse(Request $request, bool $isAuthenticated, string $userId)
    {
        // nothing to do here
    }
}