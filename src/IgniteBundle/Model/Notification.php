<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 07.10.2018
 * Time: 14:21
 */

namespace JRemmurd\IgniteBundle\Model;


use Carbon\Carbon;
use JRemmurd\IgniteBundle\Constant\EventType;
use JRemmurd\IgniteBundle\Ignite\Channel\EventInterface;
use JRemmurd\IgniteBundle\Model\Notification\Dao;
use Pimcore\Event\Model\ElementEvent;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Tool\Authentication;
use Pimcore\Model\Element\ElementInterface;

/**
 * Class Notification
 * @package JRemmurd\IgniteBundle\Model
 *
 * @method Dao getDao()
 */
class Notification extends AbstractModel
{
    /** @var int */
    public $id;

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

    /** @var bool */
    public $read = true;

    /** @var int */
    public $creationDate;

    /** @var int */
    public $modificationDate;

    /* @var string $elementId */
    public $elementId;

    /* @var string $elementType */
    public $elementType;

    /* @var string $channelName */
    public $channelName;

    /**
     * Notification constructor.
     * @param string $type
     * @param string $title
     * @param string $message
     * @param string $channelName
     * @param $targetUserId
     * @param ElementInterface|null $element
     * @param $sourceUserId
     */
    public function setNotificationData(string $type, string $title, string $message, string $channelName, ElementInterface $element = null, $targetUserId = null, $sourceUserId = null)
    {
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->channelName = $channelName;
        $this->sourceUser = $sourceUserId;
        $this->targetUser = $targetUserId;
        $this->creationDate = Carbon::now()->timestamp;
        $this->read = null;

        if ($element) {
            $this->setElement($element);
        }
    }

    /**
     * @param EventInterface $event
     * @return Notification
     */
    public static function createFromEvent(EventInterface $event)
    {
        /* @var \JRemmurd\IgniteBundle\Ignite\Event\Notification $event */
        $record = new self();
        $record->setNotificationData($event->getType(), $event->getTitle(), $event->getMessage(), $event->getChannelName(), $event->getElement(), $event->getTargetUser(),  $event->getSourceUser());
        return $record;
    }

    /**
     * @return ElementInterface
     */
    public function getElement()
    {
        return Concrete::getById($this->getElementId());
    }

    /**
     * @param ElementInterface $element
     * @return $this
     */
    public function setElement(ElementInterface $element)
    {
        $this->elementType = $element->getType();
        $this->elementId = $element->getId();
        return $this;
    }

    /**
     * @param int $id
     *
     * @return Notification|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function getById($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return null;
        }

        $cacheKey = "ignite_notification_" . $id;
        if (!$notification = \Pimcore\Cache::load($cacheKey)) {
            $notification = new Notification();
            $notification->getDao()->getById($id);
            \Pimcore\Cache::save($notification, $cacheKey);
        }

        return $notification;
    }

    /**
     * @return $this
     */
    public static function create()
    {
        $notification = new self();
        $notification->setCreationDate(Carbon::now()->timestamp);
        $notification->save();
        return $notification;
    }

    /**
     * @return Notification
     */
    public function clearCache()
    {
        \Pimcore\Cache::clearTags(["ignite_notification_" . $this->getId()]);
        return $this;
    }

    /**
     * @return void
     */
    public function delete()
    {
        \Pimcore::getEventDispatcher()->dispatch(EventType::NOTIFICATION_PRE_DELETE, new ElementEvent($this));
        $this->getDao()->delete();
        $this->clearCache();
        \Pimcore::getEventDispatcher()->dispatch(EventType::NOTIFICATION_POST_DELETE, new ElementEvent($this));
    }

    /**
     * @return mixed|null
     */
    protected function getFrontendUser()
    {
        if (!\Pimcore::getContainer()->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = \Pimcore::getContainer()->get('security.token_storage')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * @return Notification
     */
    public function save()
    {
        if (!$this->getSourceUser()) {
            if (\Pimcore::inAdmin()) {
                $user = Authentication::authenticateSession();
            } else {
                $user = $this->getFrontendUser();
            }
            if ($user) {
                $this->setSourceUser($user->getId());
            }
        }

        $this->modificationDate = Carbon::now()->timestamp;

        $isUpdated = false;
        if ($this->getId()) {
            $isUpdated = true;
            \Pimcore::getEventDispatcher()->dispatch(EventType::NOTIFICATION_PRE_UPDATE, new ElementEvent($this));
        } else {
            \Pimcore::getEventDispatcher()->dispatch(EventType::NOTIFICATION_PRE_ADD, new ElementEvent($this));
        }

        $this->getDao()->save();
        $this->clearCache();

        if ($isUpdated) {
            \Pimcore::getEventDispatcher()->dispatch(EventType::NOTIFICATION_POST_UPDATE, new ElementEvent($this));
        } else {
            \Pimcore::getEventDispatcher()->dispatch(EventType::NOTIFICATION_POST_ADD, new ElementEvent($this));
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Notification
     */
    public function setId($id)
    {
        $this->id = (int)$id;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Notification
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Notification
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Notification
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSourceUser()
    {
        return $this->sourceUser;
    }

    /**
     * @param int|null $sourceUser
     * @return Notification
     */
    public function setSourceUser($sourceUser)
    {
        $this->sourceUser = $sourceUser;
        return $this;
    }

    /**
     * @return int
     */
    public function getTargetUser()
    {
        return $this->targetUser;
    }

    /**
     * @param int $targetUser
     * @return Notification
     */
    public function setTargetUser($targetUser)
    {
        $this->targetUser = $targetUser;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRead()
    {
        return $this->read;
    }

    /**
     * @return boolean
     */
    public function isUnread()
    {
        return !$this->read;
    }

    /**
     * @param boolean $read
     * @return Notification
     */
    public function setRead($read)
    {
        $this->read = $read;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * @param string $elementId
     * @return Notification
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;
        return $this;
    }

    /**
     * @return string
     */
    public function getElementType(): string
    {
        return $this->elementType;
    }

    /**
     * @param string $elementType
     * @return Notification
     */
    public function setElementType($elementType)
    {
        $this->elementType = $elementType;
        return $this;
    }

    /**
     * @return Carbon
     */
    public function getCreationDate()
    {
        return Carbon::createFromTimestamp($this->creationDate);
    }

    /**
     * @param int|Carbon $creationDate
     * @return Notification
     */
    public function setCreationDate($creationDate)
    {
        if ($creationDate instanceof Carbon) {
            $creationDate = $creationDate->timestamp;
        }

        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return Carbon
     */
    public function getModificationDate()
    {
        return Carbon::createFromTimestamp($this->modificationDate);
    }

    /**
     * @param int|Carbon $modificationDate
     * @return Notification
     */
    public function setModificationDate($modificationDate)
    {
        if ($modificationDate instanceof Carbon) {
            $modificationDate = $modificationDate->timestamp;
        }

        $this->modificationDate = $modificationDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getChannelName(): string
    {
        return $this->channelName;
    }

    /**
     * @param string $channelName
     * @return Notification
     */
    public function setChannelName(string $channelName)
    {
        $this->channelName = $channelName;
        return $this;
    }

}