<?php
namespace Juup\IgniteBundle\Model\Notification\Listing;

use Juup\IgniteBundle\Model\Notification;
use Pimcore\Model;

/**
 * @property Notification\Listing $model
 */
class Dao extends Model\Listing\Dao\AbstractDao
{

    /**
     * @var callable function
     */
    protected $onCreateQueryCallback;

    /**
     * @return array
     */
    public function load()
    {
        $notificationsData = $this->db->fetchCol('SELECT id FROM ' . Notification\Dao::TABLE_NAME . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        $notifications = [];
        foreach ($notificationsData as $notificationData) {
            if ($note = Notification::getById($notificationData)) {
                $notifications[] = $note;
            }
        }

        $this->model->setNotifications($notifications);

        return $notifications;
    }

    /**
     * @return array
     */
    public function loadIdList()
    {
        $notesIds = $this->db->fetchCol('SELECT id FROM ' . Notification\Dao::TABLE_NAME . $this->getCondition() . $this->getGroupBy() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        return $notesIds;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        try {
            $amount = (int)$this->db->fetchOne('SELECT COUNT(*) as amount FROM ' . Notification\Dao::TABLE_NAME . $this->getCondition(), $this->model->getConditionVariables());
        } catch (\Exception $e) {
        }

        return $amount;
    }

    /**
     * @param array $columns
     *
     * @return \Pimcore\Db\ZendCompatibility\QueryBuilder
     */
    public function getQuery($columns)
    {
        $select = $this->db->select();
        $select->from(
            [ "plugin_notifications" ], $columns
        );
        $this->addConditions($select);
        $this->addOrder($select);
        $this->addLimit($select);
        $this->addGroupBy($select);
        if ($this->onCreateQueryCallback) {
            $closure = $this->onCreateQueryCallback;
            $closure($select);
        }
        return $select;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $select = $this->getQuery([new \Zend_Db_Expr('COUNT(*)')]);
        $amount = (int)$this->db->fetchOne($select, $this->model->getConditionVariables());
        return $amount;
    }

    /**
     * @param callable $callback
     *
     * @return void
     */
    public function onCreateQuery(callable $callback)
    {
        $this->onCreateQueryCallback = $callback;
    }
}
