<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:18
 */

namespace Juup\IgniteBundle\Ignite\Driver;


use Juup\IgniteBundle\Ignite\Channel\AbstractChannel;
use Juup\IgniteBundle\Ignite\Channel\EventInterface;
use Symfony\Component\HttpFoundation\Request;

interface DriverInterface
{
    public function getName();

    public function push($channels, EventInterface $event, $socketId = "");

    public function onSubscribe($channelNames);

    public function onUnsubscribe($channelNames);

    public function getAuthPresenceResponse(Request $request, bool $isAuthenticated, string $userId, $presenceData = null);

    public function getAuthPrivateResponse(Request $request, bool $isAuthenticated, string $userId);

}