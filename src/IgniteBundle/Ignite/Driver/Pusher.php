<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 14:57
 */

namespace JRemmurd\IgniteBundle\Ignite\Driver;


use JRemmurd\IgniteBundle\Ignite\Channel\Encoder\ChannelSignatureEncoderInterface;
use JRemmurd\IgniteBundle\Ignite\Channel\EventInterface;
use JRemmurd\IgniteBundle\Ignite\Config;
use JRemmurd\IgniteBundle\Ignite\Constant;
use Symfony\Component\HttpFoundation\Request;

class Pusher extends AbstractScriptDriver
{

    /** @var \Pusher\Pusher $pusher */
    protected $pusher;

    /* @var Config $config */
    protected $config;

    /* @var ChannelSignatureEncoderInterface $channelNameEncoder */
    protected $channelNameEncoder;

    /**
     * Pusher constructor.
     * @param Config $config
     * @param ChannelSignatureEncoderInterface $channelNameEncoder
     */
    public function __construct(Config $config, ChannelSignatureEncoderInterface $channelNameEncoder)
    {
        $this->channelNameEncoder = $channelNameEncoder;
        $this->config = $config;
        $this->subscriptions = [];
        $this->unsubscriptions = [];
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    protected function getDriverConfig()
    {
        $config = $this->config->getDriverConfig($this->getName());

        $config = array_key_exists("config", $config)
            ? $config["config"]
            : [];

        return $config;
    }

    /**
     * @return \Pusher\Pusher
     */
    protected function getPusher()
    {
        try {
            $config = $this->getDriverConfig();

            if (!$this->pusher) {

                $pusher = new \Pusher\Pusher(
                    $config["key"],
                    $config["secret"],
                    $config["id"],
                    $config["options"]
                );

                $this->pusher = $pusher;
            }
        } catch (\Exception $e) {
            // TODO
        }

        return $this->pusher;
    }

    /**
     * @param string $channelName
     * @return string
     * @throws \Exception
     */
    protected function getJsChannelVar(string $channelName)
    {
        $decodedChannelName = $this->channelNameEncoder->decode($channelName);
        $useSlug = $this->config->getChannelConfig($decodedChannelName["identifier"])["useSlugForJS"];

        if (!$useSlug) {
            $channelName = $decodedChannelName["identifier"];
        }

        $channelName = str_replace(".", "$$", $channelName);

        return $channelName;
    }

    /**
     * @param array $config
     * @return string
     * @throws \Exception
     */
    public function getInitScript(array $config = [])
    {
        $script = "\nvar Ignite = Ignite || {};";

        $driverConfig = $this->getDriverConfig();

        $authEndpoint = $this->config->getCurrentChannelNamespace()["authEndpoint"];
        $authEndpoint = preg_replace("/{driver}/", $this->getName(), $authEndpoint);

        $config = array_merge([
            "cluster" => $driverConfig["options"]["cluster"],
            "encrypted" => true,
            "authEndpoint" => $authEndpoint
        ], $config);

        $config = json_encode($config);


        $script .= <<<JS
        
Ignite.connectionConfig = {$config};
Ignite.pusher = new Pusher('{$driverConfig["key"]}', Ignite.connectionConfig);

Ignite.channels = {};


JS;

        if ($driverConfig["log_to_console"]) {
            $script .= "Ignite.pusher.logToConsole = true;\n";
        }

        return $script;
    }

    /**
     * @param bool $addInitScript
     * @param array $initConfig
     * @return string
     * @throws \Exception
     */
    public function getScript($addInitScript = true, $initConfig = [])
    {
        $script = "";

        if ($addInitScript) {
            $script .= $this->getInitScript( $initConfig);
        } else {
            $script .= "Ignite.channels = {};\n";
        }

        foreach ($this->subscriptions as $channelName) {
            $script .= "Ignite.channels.{$this->getJsChannelVar($channelName)} = Ignite.{$this->getName()}.subscribe('{$channelName}');\n";
        }

        foreach ($this->unsubscriptions as $channelName) {
            $script .= "Ignite.{$this->getName()}.unsubscribe('{$channelName}');\n";
        }

        return $script;
    }


    /**
     * @param $channels
     * @param EventInterface $event
     * @param string $socketId
     * @throws \Exception
     */
    public function push($channels, EventInterface $event, $socketId = "")
    {
        $this->getPusher()->trigger($channels, $event->getName(), $event->getData(), $socketId);
    }

    /**
     * @param Request $request
     * @param bool $isAuthenticated
     * @param string $userId
     * @param null $presenceData
     * @return string|null
     * @throws \Exception
     */
    public function getAuthPresenceResponse(Request $request, bool $isAuthenticated, string $userId, $presenceData = null)
    {
        if ($isAuthenticated) {
            return $this->getPusher()->presence_auth(
                $request->get(Constant::POST_PARAM_CHANNEL_NAME),
                $request->get(Constant::POST_PARAM_SOCKET_ID),
                $userId,
                $presenceData
            );
        }
    }

    /**
     * @param Request $request
     * @param bool $isAuthenticated
     * @param string $userId
     * @return string|null
     * @throws \Exception
     */
    public function getAuthPrivateResponse(Request $request, bool $isAuthenticated, string $userId)
    {
        if ($isAuthenticated) {
            return $this->getPusher()->socket_auth(
                $request->get(Constant::POST_PARAM_CHANNEL_NAME),
                $request->get(Constant::POST_PARAM_SOCKET_ID)
            );
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "pusher";
    }
}