<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 07.10.2018
 * Time: 14:41
 */

namespace Juup\IgniteBundle\Constant;


class EventType extends AbstractConstant
{
    const NOTIFICATION_PRE_DELETE = "notification.preDelete";
    const NOTIFICATION_POST_DELETE = "notification.postDelete";
    const NOTIFICATION_PRE_UPDATE = "notification.preUpdate";
    const NOTIFICATION_POST_UPDATE = "notification.postUpdate";
    const NOTIFICATION_PRE_ADD = "notification.preAdd";
    const NOTIFICATION_POST_ADD = "notification.postAdd";
}