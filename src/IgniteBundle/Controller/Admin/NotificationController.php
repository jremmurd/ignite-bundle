<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 17.04.2018
 * Time: 21:02
 */

namespace JRemmurd\IgniteBundle\Controller\Admin;


use JRemmurd\IgniteBundle\Ignite\Radio;
use JRemmurd\IgniteBundle\Model\Notification;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use JRemmurd\IgniteBundle\Model\Notification\Listing;


/**
 * Class DefaultController
 * @package JRemmurd\IgniteBundle\Controller\Admin
 *
 * @Route("/admin/ignite/notification")
 */
class NotificationController extends AdminController
{
    /**
     * @Route("/get")
     *
     * @param Request $request
     * @param Radio $radio
     * @return JsonResponse
     * @throws \Exception
     */
    public function getAction(Request $request, Radio $radio)
    {
        $notifications = new Listing();
        $notifications->setCondition("`read` IS NULL");
        $notifications->addConditionParam("`targetUser` = {$this->getUser()->getId()}");
        $notifications->setLimit(100);
        $notifications->setOrder("desc");
        $notifications->setOrderKey("creationDate");

        $json = [];

        foreach ($notifications as $notification) {
            $data = [
                "targetUser" => $notification->getTargetUser(),
                "sourceUser" => $notification->getSourceUser(),
                "notificationId" => $notification->getId(),
                "title" => $notification->getTitle(),
                "message" => $notification->getMessage(),
                "creationDate" => $notification->getCreationDate()->timestamp
            ];

            if ($element = $notification->getElement()) {
                /* @var ElementInterface $element */
                $data["elementType"] = $element->getType();
                $data["elementId"] = $element->getId();
            }
            $json[] = $data;
        }

        return new JsonResponse($json);
    }

    /**
     * @Route("/set-read")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setReadAction(Request $request)
    {
        if ($notificationId = $request->get("id")) {
            Notification::getById($notificationId)
                ->setRead(true)
                ->save();

            return new JsonResponse(["success" => true]);
        }

        return new JsonResponse(["success" => false]);
    }
}