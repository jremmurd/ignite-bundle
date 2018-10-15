<?php

namespace JRemmurd\IgniteBundle;

use JRemmurd\IgniteBundle\Tools\Installer;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class IgniteBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/ignite/js/libs/pusher-4.1.min.js',
            '/bundles/ignite/js/pimcore/startup.js',
        ];
    }

    /**
     * @return array
     */
    public function getCssPaths()
    {
        return [
            '/bundles/ignite/css/icons.css',
        ];
    }

    public function getInstaller()
    {
        return $this->container->get(Installer::class);
    }

    public function getVersion()
    {
        return 0.2;
    }

    public function getNiceName()
    {
        return "Pimcore Ignite";
    }

    public function getDescription()
    {
        return "Add Web Realtime to your Pimcore application.";
    }
}
