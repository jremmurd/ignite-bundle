<?php
namespace JRemmurd\IgniteBundle\Model\Notification;


use JRemmurd\IgniteBundle\Model\Notification;
use Pimcore\Model;

/**
 * @method Dao getDao()
 * @method Notification[] load()
 */
class Listing extends Model\Listing\AbstractListing implements \Zend_Paginator_Adapter_Interface, \Zend_Paginator_AdapterAggregate, \Iterator
{
    /**
     * Contains the results of the list.
     *
     * @var Notification[]|null
     */
    public $notifications = null;

    /**
     * Tests if the given key is an valid order key to sort the results
     *
     * @param $key
     * @return boolean
     */
    public function isValidOrderKey($key)
    {
        return true;
    }

    /**
     * @return Notification[]
     */
    public function getNotifications()
    {
        if ($this->notifications === null) {
            $this->load();
        }
        return $this->notifications;
    }

    /**
     * @param array $notifications
     *
     * @return $this
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
        return $this;
    }

    /**
     * Returns the total items count.
     *
     * @return int
     */
    public function count()
    {
        return $this->getTotalCount();
    }

    /**
     * Returns the listing based on defined offset and limit as parameters.
     *
     * @param int $offset
     * @param int $itemCountPerPage
     *
     * @return Listing
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->setOffset($offset);
        $this->setLimit($itemCountPerPage);
        return $this;
    }

    /**
     * @return $this
     */
    public function getPaginatorAdapter()
    {
        return $this;
    }

    /**
     * Rewind the listing back to te start.
     *
     * @return void
     */
    public function rewind()
    {
        $this->getNotifications();
        reset($this->notifications);
    }

    /**
     * Returns the current listing row.
     *
     * @return Notification
     */
    public function current()
    {
        $this->getNotifications();
        $var = current($this->notifications);
        return $var;
    }

    /**
     * Returns the current listing row key.
     *
     * @return Notification|null
     */
    public function key()
    {
        $this->getNotifications();
        $var = key($this->notifications);
        return $var;
    }

    /**
     * Returns the next listing row key.
     *
     * @return Notification|false
     */
    public function next()
    {
        $this->getNotifications();
        $var = next($this->notifications);
        return $var;
    }

    /**
     * Checks whether the listing contains more entries.
     *
     * @return bool
     */
    public function valid()
    {
        $this->getNotifications();
        $var = $this->current() !== false;
        return $var;
    }
}