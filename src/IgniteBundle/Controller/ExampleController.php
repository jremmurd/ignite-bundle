<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 19:03
 */

namespace JRemmurd\IgniteBundle\Controller;


use JRemmurd\IgniteBundle\Constant\NotificationType;
use JRemmurd\IgniteBundle\Ignite\Channel\Message;
use JRemmurd\IgniteBundle\Ignite\Event\Notification;
use JRemmurd\IgniteBundle\Ignite\Radio;
use JRemmurd\IgniteBundle\Model\Notification\Listing;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ExampleController
 * @package JRemmurd\IgniteBundle\Controller
 *
 * @Route("/ignite")
 */
class ExampleController extends FrontendController
{

    /**
     * @param Request $request
     * @param Radio $radio
     * @Route("")
     * @throws \Exception
     */
    public function indexAction(Request $request, Radio $radio)
    {
        $radio
            ->getChannel("user", ["id" => $this->getUser()->getId()])
            ->subscribe();

        $radio
            ->getChannel("global")
            ->subscribe();

        $radio
            ->getChannel("notifications")
            ->subscribe();

        $notifications = new Listing();
        $notifications->addConditionParam("`read` IS NULL");
        $notifications->setLimit(5);
        $notifications->setOrder("desc");
        $notifications->setOrderKey("creationDate");

        $this->view->notifications = $notifications;
    }

    /**
     * @Route("/publish/public")
     *
     * @param Request $request
     * @param Radio $radio
     * @return Response
     * @throws \Exception
     */
    public function publishToPublicChannel(Request $request, Radio $radio)
    {
        $globalChannel = $radio->getPublicChannel("global");
        $child_1 = $radio->getPublicChannel("global.child_1");
        $child_2 = $radio->getPublicChannel("global.child_2");

        $globalChannel->publish(new Message("Hello World!"));
        $child_1->publish(new Message("Hello Child Channel 1!"));
        $child_2->publish(new Message("Hello Child Channel 2!"));

        return new Response("Done.");
    }

    /**
     * @Route("/publish/presence")
     *
     * @param Request $request
     * @param Radio $radio
     * @return Response
     * @throws \Exception
     */
    public function publishToPresenceChannel(Request $request, Radio $radio)
    {
        $channel = $radio->getPresenceChannel("user", ["id" => $this->getUser()->getId()]);
        $channel->publish(new Message("hello"));

        return new Response("Done.");
    }

    /**
     * @Route("/publish/notification")
     *
     * @param Request $request
     * @param Radio $radio
     * @return Response
     * @throws \Exception
     */
    public function publishToNotificationChannel(Request $request, Radio $radio)
    {
        $radio->setChannelNamespace("admin");
        $notifiedChannel = $radio->getPrivateChannel("user_notifications", ["id" => 2]);

        $notifiedChannel
            ->publish(new Notification("Some Event", "Hello World!", NotificationType::INFO ))
            ->publish(new Notification("Some Title", "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.", NotificationType::INFO ,  [
                    "related_object" => ["type" => Notification::DATA_TYPE_OBJECT, "data" => 2]
                ])
            );

        return new Response("Done.");
    }

}