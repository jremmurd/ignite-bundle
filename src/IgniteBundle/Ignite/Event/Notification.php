<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 17:53
 */

namespace JRemmurd\IgniteBundle\Ignite\Event;

use Carbon\Carbon;
use JRemmurd\IgniteBundle\Constant\NotificationType;
use JRemmurd\IgniteBundle\Ignite\Channel\EventInterface;
use JRemmurd\IgniteBundle\Ignite\Driver\DriverInterface;
use Pimcore\Model\Element\ElementInterface;

/**
 * Class NotificationEvent
 * @package JRemmurd\IgniteBundle\Ignite\Channel
 */
class Notification implements EventInterface
{

    const DATA_TYPE_OBJECT = "object";
    const DATA_TYPE_TEXT = "text";
    const DATA_TYPE_ASSET = "asset";
    const DATA_TYPE_DOCUMENT = "document";
    const DATA_TYPE_DATE = "date";
    const DATA_TYPE_BOOL = "bool";

    /* @var \JRemmurd\IgniteBundle\Model\Notification $notification */
    protected $notification;

    /* @var array $data */
    protected $data = [];

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

    /* @var ElementInterface $element */
    protected $element;

    /**
     * NotificationEvent constructor.
     * @param string $title
     * @param string $message
     * @param string $type
     * @param int $targetUser
     * @param ElementInterface|null $element
     * @param int $sourceUser
     */
    public function __construct(string $title, string $message, ElementInterface $element = null, string $type = "", $targetUser = null, $sourceUser = null)
    {
        $this->name = "notification";

        $this->sourceUser = $sourceUser;
        $this->targetUser = $targetUser;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type ?: NotificationType::INFO;
        $this->createdAt = Carbon::now();
        $this->element = $element;
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
            "creationDate" => $this->getCreatedAt()->timestamp,
            "elementId" => $this->getElement() ? $this->getElement()->getId() : "",
            "elementType" => $this->getElement() ? $this->getElement()->getType() : "",
            "notificationId" => $this->getNotification() ? $this->getNotification()->getId() : "",
            "modificationDate" => $this->getNotification() ? (string)$this->getNotification()->getModificationDate() : ""
        ];

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
     * @return null|\JRemmurd\IgniteBundle\Model\Notification
     */
    public function getNotification(): ?\JRemmurd\IgniteBundle\Model\Notification
    {
        return $this->notification;
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
     * @param \JRemmurd\IgniteBundle\Model\Notification $notification
     */
    public function setNotification(\JRemmurd\IgniteBundle\Model\Notification $notification)
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

    /**
     * @return ElementInterface
     */
    public function getElement(): ?ElementInterface
    {
        return $this->element;
    }

    /**
     * @param ElementInterface $element
     * @return Notification
     */
    public function setElement(ElementInterface $element)
    {
        $this->element = $element;
        return $this;
    }


}