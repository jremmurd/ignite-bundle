<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 21:33
 */

namespace JRemmurd\IgniteBundle\Ignite\Authenticator;


use Symfony\Component\HttpFoundation\Request;

interface AuthenticatorInterface
{

    public function authenticateChannel(Request $request):bool;

    public function getUser();

}