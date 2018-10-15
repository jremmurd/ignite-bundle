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
use Pimcore\Tool\Authentication;

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

    /** @var array */
    public $data = [];

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

    /**
     * Notification constructor.
     * @param string $type
     * @param string $title
     * @param string $message
     * @param int $targetUser
     * @param array $data
     * @param int $sourceUser
     * @param int|null $creationDate
     */
    public function setNotificationData(string $type, string $title, string $message, int $targetUser = null, array $data = [], int $sourceUser = null, int $creationDate = null)
    {
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->sourceUser = $sourceUser;
        $this->targetUser = $targetUser;
        $this->creationDate = $creationDate ?: Carbon::now()->timestamp;
        $this->modificationDate = Carbon::now()->timestamp;
        $this->read = null;
    }

    /**
     * @param EventInterface $event
     * @return Notification
     */
    public static function createFromEvent(EventInterface $event)
    {
        /* @var \JRemmurd\IgniteBundle\Ignite\Event\Notification $event */
        $record = new self();
        $record->setNotificationData($event->getType(), $event->getTitle(), $event->getMessage(), $event->getTargetUser(), $event->getNotificationData(), $event->getSourceUser(), $event->getCreatedAt()->timestamp);
        return $record;
    }


    /**
     * @param int $id
     *
     * @return Notification|null
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
     * @param string $name
     * @param string $type
     * @param mixed $data
     */
    public function addData($name, $type, $data)
    {
        $this->data[$name] = [
            'type' => $type,
            'data' => $data
        ];
    }

    /**
     * @return $this
     */
    public static function create()
    {
        $notification = new self();
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
     * @return mixed|void
     */
    protected function getFrontendUser()
    {
        if (!\Pimcore::getContainer()->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = \Pimcore::getContainer()->get('security.token_storage')->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
     */
    public function setRead($read)
    {
        $this->read = $read;
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
     *
     * @return $this
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
     *
     * @return $this
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
     * @param $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

}