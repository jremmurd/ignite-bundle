<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:58
 */

namespace JRemmurd\IgniteBundle\Ignite\Channel;


class PresenceChannel extends AbstractAuthChannel
{

    /**
     * @param null $user
     * @return array
     */
    public function getAuthResponseData($user = null)
    {
        $user = $user ?: \Pimcore::getContainer()->get("security.token_storage")->getToken()->getUser();

        $presenceData = [
            "id" => $user->getId(),
            "name" => $user->getUsername(),
        ];

        return $presenceData;
    }

}