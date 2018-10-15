<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 17.04.2018
 * Time: 22:52
 */

namespace JRemmurd\IgniteBundle\EventListener;


use JRemmurd\IgniteBundle\Ignite\Config;
use JRemmurd\IgniteBundle\Ignite\Radio;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


/**
 * Class SetupListener
 * @package JRemmurd\IgniteBundle\EventListener
 */
class SetupListener
{
    /* @var Config $config */
    protected $config;

    /* @var Radio $radio */
    protected $radio;

    /**
     * SetupListener constructor.
     * @param Config $config
     * @param Radio $radio
     */
    public function __construct(Config $config, Radio $radio)
    {
        $this->config = $config;
        $this->radio = $radio;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->isMasterRequest()) {
            $this->setup($event->getRequest());
        }
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
    }

    /**
     * @param Request $request
     */
    protected function setup(Request $request)
    {
        if (!$channelNamespaces = $this->config->getChannelNamespaces()) {
            return;
        }

        $queryString = $request->getPathInfo();

        foreach ($channelNamespaces as $channelNamespace) {
            $namespaceConfig = $this->config->getChannelNamespace($channelNamespace);
            $pattern = $namespaceConfig["pattern"] ?: $namespaceConfig["namespace"];

            if (preg_match("#{$pattern}#", $queryString)) {
                $this->radio->setChannelNamespace($channelNamespace);
                break;
            }
        }
    }
}