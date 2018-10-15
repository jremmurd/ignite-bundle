<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:58
 */

namespace JRemmurd\IgniteBundle\Ignite\Channel;


class PrivateChannel extends AbstractAuthChannel
{
    /**
     * @param null $user
     * @return null
     */
    function getAuthResponseData($user = null)
    {
      return null;
    }
}