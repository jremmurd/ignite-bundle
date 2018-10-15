<?php

namespace JRemmurd\IgniteBundle\Controller;

use Pimcore\Controller\FrontendController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends FrontendController
{
    public function defaultAction(Request $request)
    {
    }

    /**
     * @param Request $request
     *
     * @Route("/private")
     */
    public function privateAction(Request $request)
    {

    }

}
