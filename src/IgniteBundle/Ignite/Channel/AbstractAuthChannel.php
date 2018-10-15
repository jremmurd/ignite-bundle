<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:57
 */

namespace JRemmurd\IgniteBundle\Ignite\Channel;


use Symfony\Component\HttpFoundation\Response;

abstract class AbstractAuthChannel extends AbstractChannel
{
    /**
     * @param null $user
     * @return Response
     */
    abstract function getAuthResponseData($user = null);
}