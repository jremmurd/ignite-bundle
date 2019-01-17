<?php
namespace JRemmurd\IgniteBundle\Model\Notification;

use Carbon\Carbon;
use JRemmurd\IgniteBundle\Model\Notification;
use Pimcore\Model;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;

/**
 * @property Notification $model
 */
class Dao extends Model\Dao\AbstractDao
{
    const TABLE_NAME = "ignite_notification";
    const TABLE_NAME_DATA = "ignite_notification_data";

    /**
     * @param $id
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getById($id)
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ?', $id);

        if (!$data['id']) {
            throw new \Exception('Notification item with id ' . $id . ' not found');
        }
        $this->assignVariablesToModel($data);
    }

    /**
     * Save object to database
     */
    public function save()
    {
        $version = get_object_vars($this->model);
        $this->model->setModificationDate(Carbon::now()->timestamp);

        foreach ($version as $key => $value) {
            if (in_array($key, $this->getValidTableColumns(self::TABLE_NAME))) {
                $data[$key] = $value;
            }
        }

        $this->db->insertOrUpdate(self::TABLE_NAME, $data);

        $lastInsertId = $this->db->lastInsertId();
        if (!$this->model->getId() && $lastInsertId) {
            $this->model->setId($lastInsertId);
        }

        return true;
    }

    /**
     * Deletes object from database
     */
    public function delete()
    {
        $this->db->delete(self::TABLE_NAME, ['id' => $this->model->getId()]);
    }

}
