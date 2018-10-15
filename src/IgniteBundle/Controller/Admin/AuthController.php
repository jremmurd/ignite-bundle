<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 19:03
 */

namespace Juup\IgniteBundle\Controller\Admin;


use Juup\IgniteBundle\Ignite\Authenticator\AuthenticatorInterface;
use Juup\IgniteBundle\Ignite\Channel\AbstractAuthChannel;
use Juup\IgniteBundle\Ignite\Channel\Encoder\ChannelSignatureEncoderInterface;
use Juup\IgniteBundle\Ignite\Config;
use Juup\IgniteBundle\Ignite\Constant;
use Juup\IgniteBundle\Ignite\Radio;
use Pimcore\Controller\FrontendController;
use Pimcore\Tool;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends FrontendController
{

    /**
     * @param Request $request
     * @param Radio $radio
     * @param Config $config
     * @param ChannelSignatureEncoderInterface $channelNameEncoder
     * @return Response
     *
     * @Route("/admin/ignite/auth")
     * @throws \Exception
     */
    public function authenticateAction(Request $request, Radio $radio, Config $config, ChannelSignatureEncoderInterface $channelNameEncoder)
    {
        $channelName = $request->get(Constant::POST_PARAM_CHANNEL_NAME);

        /* @var AbstractAuthChannel $channel */
        $decodedChannelName = $channelNameEncoder->decode($channelName);
        $channel = $radio->getChannel($decodedChannelName["identifier"], $decodedChannelName["parameters"]);

        if (!$channel instanceof AbstractAuthChannel) {
            throw new \Exception("Invalid channel instance [{$channelName}].");
        }

        $identifier = $channelNameEncoder->decode($channelName)["identifier"];
        $channelConfig = $config->getChannelConfig($identifier);

        $isAuthenticated = false;

        if ($authenticatorId = $channelConfig["authenticator"]) {
            /* @var AuthenticatorInterface $authenticator */
            $authenticator = $this->get($authenticatorId);
            $isAuthenticated = $authenticator->authenticateChannel($request);
        }

        if (!$isAuthenticated) {
            return new Response("Forbidden", 403);
        }

        try {
            $driverName = $request->get("driver");
            $config->getDriverConfig($driverName);
        } catch (\Exception $e) {
            $driverName = $config->getDefaultDriver();
        }


        $presenceData = $channel->getAuthResponseData($authenticator->getUser());

        $pusherDriver = $radio->getChannel($channelName)->getDriver($driverName);
        $successResponse = $pusherDriver->getAuthPresenceResponse($request, $isAuthenticated, $this->getUser()->getId(), $presenceData);

        return new Response((string)$successResponse);
    }
}