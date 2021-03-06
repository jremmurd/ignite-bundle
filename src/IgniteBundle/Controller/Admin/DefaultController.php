<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 17.04.2018
 * Time: 21:02
 */

namespace JRemmurd\IgniteBundle\Controller\Admin;


use JRemmurd\IgniteBundle\Ignite\Radio;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Log\ApplicationLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class DefaultController
 * @package JRemmurd\IgniteBundle\Controller\Admin
 *
 * @Route("/admin/ignite")
 */
class DefaultController extends AdminController
{
    /**
     * @Route("/init")
     *
     * @param Request $request
     * @param Radio $radio
     * @return JsonResponse
     * @throws \Exception
     */
    public function initAction(Request $request, Radio $radio)
    {
        try {
            $radio
                ->getPresenceChannel("user")
                ->subscribe();

            $radio
                ->getPrivateChannel("user_notifications", [
                    "id" => $this->getUser()->getId()
                ])
                ->subscribe();

            $csrfToken = $request->get("csrf");

            $script = $radio->getScript(true, [
                "auth" => [
                    "params" => [
                        "csrfToken" => $csrfToken,
                        "driver" => "pusher"
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            ApplicationLogger::getInstance("Ignite", true)
                ->info("Could not initialize IgniteBundle. -> " . $e->getMessage() . ", {$e->getFile()}, {$e->getLine()}");

            return new JsonResponse([
                "success" => false
            ]);
        }

        return new JsonResponse([
            "script" => $script
        ]);
    }
}