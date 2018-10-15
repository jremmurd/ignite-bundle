<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 03.10.2018
 * Time: 19:57
 */

namespace Juup\IgniteBundle\Tools;

use Juup\IgniteBundle\Constant\Permission;
use Juup\IgniteBundle\Model\Notification;
use Pimcore\Db;
use Pimcore\Model\Translation\Admin;
use Pimcore\Model\User\Permission\Definition;

class Installer extends \Pimcore\Extension\Bundle\Installer\AbstractInstaller
{
    protected $permissionsToInstall = [
        Permission::PRESENCE,
        Permission::NOTIFICATIONS
    ];

    /**
     * @return bool
     */
    public function canBeInstalled()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canBeUninstalled()
    {
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isInstalled()
    {
        foreach ($this->permissionsToInstall as $permission) {
            $definition = Definition::getByKey($permission);

            if (!$definition) {
                return false;
            }
        }
        return true;
    }

    public function install()
    {
        $this->installPermissions();
        $this->installTranslations();

        $notificationTable = Notification\Dao::TABLE_NAME;
        $notificationDataTable = Notification\Dao::TABLE_NAME_DATA;

        $db = Db::get();
        $createStatement = <<<SQL
DROP TABLE IF EXISTS `{$notificationTable}`;

CREATE TABLE `{$notificationTable}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `creationDate` int(11) DEFAULT NULL,
  `modificationDate` int(11) DEFAULT NULL,
  `read` int(1) DEFAULT NULL,
  `sourceUser` int(11) DEFAULT NULL,
  `targetUser` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` longtext,
  PRIMARY KEY (`id`),
  KEY `sourceUser` (`sourceUser`),
  KEY `targetUser` (`targetUser`),
  KEY `creationDate` (`creationDate`),
  KEY `modificationDate` (`modificationDate`)
) DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `{$notificationDataTable}`;

CREATE TABLE `{$notificationDataTable}` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `type` enum('text','date','document','asset','object','bool') DEFAULT NULL,
  `data` text,
  KEY `id` (`id`)
) DEFAULT CHARSET=utf8mb4;
SQL;

        $db->exec($createStatement);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function uninstall()
    {
        $notificationTable = Notification\Dao::TABLE_NAME;
        $notificationDataTable = Notification\Dao::TABLE_NAME_DATA;

        $db = Db::get();

        $db->exec("
DROP TABLE IF EXISTS `{$notificationDataTable}`;
DROP TABLE IF EXISTS `{$notificationTable}`;"
        );

        $this->uninstallPermissions();
    }


    /**
     * @return bool
     */
    public function needsReloadAfterInstall()
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    private function installPermissions()
    {
        foreach ($this->permissionsToInstall as $permission) {
            $definition = Definition::getByKey($permission);

            if ($definition) {
                continue;
            }

            Definition::create($permission);
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function uninstallPermissions()
    {
        foreach ($this->permissionsToInstall as $permission) {
            $db = \Pimcore\Db::get();
            $db->exec("DELETE FROM users_permission_definitions WHERE key = '$permission'");
        }
    }
}