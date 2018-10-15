<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:57
 */

namespace JRemmurd\IgniteBundle\Ignite\Driver;


use JRemmurd\IgniteBundle\Ignite\Channel\Encoder\ChannelSignatureEncoderInterface;
use JRemmurd\IgniteBundle\Ignite\Channel\EventInterface;
use Pimcore\Log\ApplicationLogger;
use Symfony\Component\HttpFoundation\Request;

class Notification extends AbstractDriver
{
    /* @var \JRemmurd\IgniteBundle\Model\Notification[] $notifications */
    protected $notifications = [];

    protected $channelSignatureEncoder;

    /**
     * Notification constructor.
     * @param ChannelSignatureEncoderInterface $channelSignatureEncoder
     */
    public function __construct(ChannelSignatureEncoderInterface $channelSignatureEncoder)
    {
        $this->channelSignatureEncoder = $channelSignatureEncoder;
        $this->priority = 100;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "notification";
    }

    /**
     * @param $channels
     * @param EventInterface $event
     * @param null $socketId
     * @return \JRemmurd\IgniteBundle\Model\Notification
     */
    public function push($channels, EventInterface $event, $socketId = null)
    {
        $notification = \JRemmurd\IgniteBundle\Model\Notification::createFromEvent($event);
        $this->notifications[] = $notification;

        if (method_exists($event, "setNotification")) {
            /* @var \JRemmurd\IgniteBundle\Ignite\Event\Notification $event */
            $event->setNotification($notification);
        }

        if (method_exists($event, "setTargetUser")) {

            if (!$event->getTargetUser()) {
                /* @var \JRemmurd\IgniteBundle\Ignite\Event\Notification $event */
                $channels = is_array($channels) ? $channels : [$channels];
                foreach ($channels as $channel) {
                    $decoded = $this->channelSignatureEncoder->decode($channel);
                    if ($targetUserId = @$decoded["parameters"]["id"]) {
                        $notification->setTargetUser($targetUserId);
                        $event->setTargetUser($targetUserId);
                    }
                }
            }
        }

        $notification->save();

        return $notification;
    }

    /**
     * @param $channelNames
     */
    public function onSubscribe($channelNames)
    {
        // nothing to do here
    }

    /**
     * @param $channelNames
     */
    public function onUnsubscribe($channelNames)
    {
        // nothing to do here
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
     * @return bool
     */
    public function getAuthPrivateResponse(Request $request, bool $isAuthenticated, string $userId)
    {
        // nothing to do here
    }
}