<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 30.09.2018
 * Time: 20:41
 */

namespace Juup\IgniteBundle\Ignite\Driver;


interface ScriptDriverInterface extends DriverInterface
{
    public function getInitScript(array $config = []);

    public function getScript($addInitScript = true, $initConfig = []);
}