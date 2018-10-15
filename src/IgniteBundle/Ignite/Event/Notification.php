<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 17:53
 */

namespace Juup\IgniteBundle\Ignite\Event;

use Carbon\Carbon;
use Juup\IgniteBundle\Ignite\Channel\EventInterface;
use Juup\IgniteBundle\Ignite\Driver\DriverInterface;

/**
 * Class NotificationEvent
 * @package Juup\IgniteBundle\Ignite\Channel
 */
class Notification implements EventInterface
{

    const DATA_TYPE_OBJECT = "object";
    const DATA_TYPE_TEXT = "text";
    const DATA_TYPE_ASSET = "asset";
    const DATA_TYPE_DOCUMENT = "document";
    const DATA_TYPE_DATE = "date";
    const DATA_TYPE_BOOL = "bool";

    /* @var \Juup\IgniteBundle\Model\Notification $notification */
    protected $notification;

    /* @var array $data */
    protected $data = [];

    /* @var array $notificationData */
    protected $notificationData = [];

    /* @var Carbon $createdAt */
    protected $createdAt;

    /* @var string $notification */
    protected $name;

    /** @var string */
    public $type = 'info';

    /** @var string */
    public $title = '';

    /** @var string */
    public $message = '';

    /** @var null|int */
    public $sourceUser;

    /** @var int */
    public $targetUser;

    /* @var string $channelName */
    protected $channelName;

    /**
     * NotificationEvent constructor.
     * @param string $title
     * @param string $message
     * @param string $type
     * @param int $targetUser
     * @param array $data
     * @param int $sourceUser
     */
    public function __construct(string $title, string $message, string $type, array $data = [], int $targetUser = null, int $sourceUser = null)
    {
        $this->name = "notification";

        $this->sourceUser = $sourceUser;
        $this->targetUser = $targetUser;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->createdAt = Carbon::now();
        $this->notificationData = $data;

        if ($channelName = $this->getChannelName()) {
            $this->addNotificationData("channelName", Notification::DATA_TYPE_TEXT, $channelName);
        }
    }

    /**
     * @param string $name
     * @param string $type
     * @param mixed $data
     */
    public function addNotificationData($name, $type, $data)
    {
        $this->notificationData[$name] = [
            'type' => $type,
            'data' => $data
        ];

        if ($this->notification) {
            $this->notification->addData($name, $type, $data);
        }
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addData($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $this->data = [
            "title" => $this->getTitle(),
            "message" => $this->getMessage(),
            "type" => $this->getType(),
            "targetUser" => $this->getTargetUser(),
            "sourceUser" => $this->getSourceUser(),
            "notificationData" => $this->getNotificationData(),
            "creationDate" => $this->getCreatedAt()->timestamp,
        ];

        if ($notification = $this->getNotification()) {
            $this->data["notification_id"] = $notification->getId();
            $this->data["modificationDate"] = (string)$notification->getModificationDate();
        }

        return $this->data;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|\Juup\IgniteBundle\Model\Notification
     */
    public function getNotification(): ?\Juup\IgniteBundle\Model\Notification
    {
        return $this->notification;
    }

    /**
     * @return array
     */
    public function getNotificationData(): array
    {
        return $this->notificationData;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int|null
     */
    public function getSourceUser(): ?int
    {
        return $this->sourceUser;
    }

    /**
     * @return int
     */
    public function getTargetUser(): ?int
    {
        return $this->targetUser;
    }

    /**
     * @return string
     */
    public function getChannelName(): ?string
    {
        return $this->channelName;
    }

    /**
     * @param string $channelName
     */
    public function setChannelName(string $channelName): void
    {
        $this->channelName = $channelName;
    }

    /**
     * @param \Juup\IgniteBundle\Model\Notification $notification
     */
    public function setNotification(\Juup\IgniteBundle\Model\Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @param int $targetUser
     * @return Notification
     */
    public function setTargetUser($targetUser)
    {
        $this->targetUser = (int)$targetUser;
        return $this;
    }
}