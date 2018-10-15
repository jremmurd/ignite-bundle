<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 17.04.2018
 * Time: 21:02
 */

namespace Juup\IgniteBundle\Controller\Admin;


use Juup\IgniteBundle\Ignite\Radio;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class DefaultController
 * @package Juup\IgniteBundle\Controller\Admin
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
        $radio
            ->getPresenceChannel("user")
            ->subscribe();

        $radio
            ->getPrivateChannel("user_notifications", [
                "id" => $this->getUser()->getId()
            ])->subscribe();

        $csrfToken = $request->get("csrf");

        $script = $radio->getScript(true, [
            "auth" => [
                "params" => [
                    "csrfToken" => $csrfToken,
                    "driver" => "pusher"
                ]
            ]
        ]);

        return new JsonResponse([
            "script" => $script
        ]);
    }
}