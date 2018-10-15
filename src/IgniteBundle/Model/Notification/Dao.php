<?php
namespace Juup\IgniteBundle\Model\Notification;

use Juup\IgniteBundle\Model\Notification;
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
     * @throws \Exception
     */
    public function getById($id)
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ?', $id);

        if (!$data['id']) {
            throw new \Exception('Notification item with id ' . $id . ' not found');
        }
        $this->assignVariablesToModel($data);

        // get key-value data
        $keyValues = $this->db->fetchAll('SELECT * FROM ' . self::TABLE_NAME_DATA . ' WHERE id = ?', [$id]);
        $preparedData = [];

        foreach ($keyValues as $keyValue) {
            $data = $keyValue['data'];
            $type = $keyValue['type'];
            $name = $keyValue['name'];

            if ($type == 'document') {
                if ($data) {
                    $data = Document::getById($data);
                }
            } elseif ($type == 'asset') {
                if ($data) {
                    $data = Asset::getById($data);
                }
            } elseif ($type == 'object') {
                if ($data) {
                    $data = DataObject\AbstractObject::getById($data);
                }
            } elseif ($type == 'date') {
                if ($data > 0) {
                    $date = new \DateTime();
                    $date->setTimestamp($data);
                    $data = $date;
                }
            } elseif ($type == 'bool') {
                $data = (bool)$data;
            }

            $preparedData[$name] = [
                'data' => $data,
                'type' => $type
            ];
        }

        $this->model->setData($preparedData);
    }

    /**
     * Save object to database
     */
    public function save()
    {
        $version = get_object_vars($this->model);

        // save main table
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

        // save data table
        $this->deleteData();
        foreach ($this->model->getData() as $name => $meta) {
            $data = $meta['data'];
            $type = $meta['type'];

            if ($type == 'document') {
                if ($data instanceof Document) {
                    $data = $data->getId();
                }
            } elseif ($type == 'asset') {
                if ($data instanceof Asset) {
                    $data = $data->getId();
                }
            } elseif ($type == 'object') {
                if ($data instanceof DataObject\AbstractObject) {
                    $data = $data->getId();
                }
            } elseif ($type == 'date') {
                if ($data instanceof \DateTimeInterface) {
                    $data = $data->getTimestamp();
                }
            } elseif ($type == 'bool') {
                $data = (bool)$data;
            }

            $this->db->insert(self::TABLE_NAME_DATA, [
                'id' => $this->model->getId(),
                'name' => $name,
                'type' => $type,
                'data' => $data
            ]);
        }

        return true;
    }

    /**
     * Deletes object from database
     */
    public function delete()
    {
        $this->db->delete(self::TABLE_NAME, ['id' => $this->model->getId()]);
        $this->deleteData();
    }

    protected function deleteData()
    {
        $this->db->delete(self::TABLE_NAME_DATA, ['id' => $this->model->getId()]);
    }
}
