<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 22:07
 */

namespace JRemmurd\IgniteBundle\Ignite\Authenticator;


use JRemmurd\IgniteBundle\Ignite\Channel\Encoder\ChannelSignatureEncoderInterface;
use JRemmurd\IgniteBundle\Ignite\Constant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class User
 * @package JRemmurd\IgniteBundle\Ignite\Authenticator
 */
class User implements AuthenticatorInterface
{
    /* @var ChannelSignatureEncoderInterface $signatureEncoder */
    protected $signatureEncoder;

    /* @var mixed $user */
    protected $user;

    /**
     * User constructor.
     * @param TokenStorage $tokenStorage
     * @param ChannelSignatureEncoderInterface $signatureEncoder
     */
    public function __construct(TokenStorage $tokenStorage, ChannelSignatureEncoderInterface $signatureEncoder)
    {
        $this->user = $tokenStorage->getToken()->getUser();
        $this->signatureEncoder = $signatureEncoder;
    }

    /**
     * @param Request $request
     * @return bool
     * @throws \Exception
     */
    public function authenticateChannel(Request $request): bool
    {
        $channelName = $request->get(Constant::POST_PARAM_CHANNEL_NAME);
        $parameters = $this->signatureEncoder->decode($channelName)["parameters"];

        return $this->getUser()->getId() == $parameters["id"];
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}