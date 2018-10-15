<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 22:07
 */

namespace Juup\IgniteBundle\Ignite\Authenticator;


use Juup\IgniteBundle\Ignite\Channel\Encoder\ChannelSignatureEncoderInterface;
use Juup\IgniteBundle\Ignite\Constant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class User
 * @package Juup\IgniteBundle\Ignite\Authenticator
 */
class User implements AuthenticatorInterface
{
    /* @var ChannelSignatureEncoderInterface $channelNameEncoder */
    protected $channelNameEncoder;

    /* @var mixed $user */
    protected $user;

    /**
     * User constructor.
     * @param TokenStorage $tokenStorage
     * @param ChannelSignatureEncoderInterface $channelNameEncoder
     */
    public function __construct(TokenStorage $tokenStorage, ChannelSignatureEncoderInterface $channelNameEncoder)
    {
        $this->user = $tokenStorage->getToken()->getUser();
        $this->channelFactory = $channelNameEncoder;
    }

    /**
     * @param Request $request
     * @return bool
     * @throws \Exception
     */
    public function authenticateChannel(Request $request): bool
    {
        $channelName = $request->get(Constant::POST_PARAM_CHANNEL_NAME);
        $parameters = $this->channelNameEncoder->decode($channelName)["parameters"];

        if ($this->getUser()->getId() == $parameters["id"]) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }


}