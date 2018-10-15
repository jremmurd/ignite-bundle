<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 22:07
 */

namespace Juup\IgniteBundle\Ignite\Authenticator\Admin;


use Juup\IgniteBundle\Constant\Permission;
use Juup\IgniteBundle\Ignite\Authenticator\AuthenticatorInterface;
use Juup\IgniteBundle\Tools\Installer;
use Pimcore\Model\User;
use Pimcore\Tool\Authentication;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Pimcore
 * @package Juup\IgniteBundle\Ignite\Authenticator\Admin
 */
class Pimcore implements AuthenticatorInterface
{

    protected $user;

    public function __construct()
    {
        $this->user = Authentication::authenticateSession();
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function authenticateChannel(Request $request): bool
    {
        return !empty($this->getUser()->isAllowed(Permission::PRESENCE));
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}