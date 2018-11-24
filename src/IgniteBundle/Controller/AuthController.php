<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 19:03
 */

namespace JRemmurd\IgniteBundle\Controller;


use JRemmurd\IgniteBundle\Ignite\Authenticator\AuthenticatorInterface;
use JRemmurd\IgniteBundle\Ignite\Channel\AbstractAuthChannel;
use JRemmurd\IgniteBundle\Ignite\Channel\Encoder\ChannelSignatureEncoderInterface;
use JRemmurd\IgniteBundle\Ignite\Config;
use JRemmurd\IgniteBundle\Ignite\Constant;
use JRemmurd\IgniteBundle\Ignite\Radio;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends FrontendController
{

    /**
     * @param Request $request
     * @param Radio $radio
     * @param Config $config
     * @param ChannelSignatureEncoderInterface $signatureEncoder
     * @return Response
     *
     * @throws \Exception
     * @Route("/ignite/auth/{driver}")
     */
    public function authenticateAction(Request $request, Radio $radio, Config $config, ChannelSignatureEncoderInterface $signatureEncoder)
    {
        $channelSignature = $request->get(Constant::POST_PARAM_CHANNEL_NAME);

        /* @var AbstractAuthChannel $channel */
        $decodedChannelName = $signatureEncoder->decode($channelSignature);
        $channel = $radio->getChannel($decodedChannelName["name"], $decodedChannelName["parameters"]);

        if (!$channel instanceof AbstractAuthChannel) {
            throw  new \Exception("Invalid channel instance [{$channelSignature}].");
        }

        $name = $signatureEncoder->decode($channelSignature)["name"];
        $channelConfig = $config->getChannelConfig($name);

        $isAuthenticated = false;

        if ($authenticatorId = $channelConfig["authenticator"]) {
            /* @var AuthenticatorInterface $authenticator */
            $authenticator = \Pimcore::getContainer()->get($authenticatorId);
            $isAuthenticated = $authenticator->authenticateChannel($request);
        }

        if (isset($authenticator) && $isAuthenticated) {

            $driverName = $request->get("driver");
            $presenceData = $channel->getAuthResponseData($authenticator->getUser());

            $pusherDriver = $radio->getChannel($channelSignature)->getDriver($driverName);
            $successResponse = $pusherDriver->getAuthPresenceResponse($request, $isAuthenticated, $this->getUser()->getId(), $presenceData);

            return new Response((string)$successResponse);

        } else {
            return new Response("Forbidden", 403);
        }
    }
}