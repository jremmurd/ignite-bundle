<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 16.04.2018
 * Time: 21:47
 */

namespace Juup\IgniteBundle\Templating\Helper;


use Juup\IgniteBundle\Ignite\Radio;
use Juup\IgniteBundle\Ignite\RadioInterface;
use Symfony\Component\Templating\Helper\Helper;

/**
 * Class Ignite
 * @package Juup\IgniteBundle\Templating\Helper
 */
class Ignite extends Helper
{

    /* @var RadioInterface $radio */
    protected $radio;

    /**
     * Ignite constructor.
     *
     * @param Radio $radio
     */
    public function __construct(Radio $radio)
    {
        $this->radio = $radio;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "ignite";
    }

    public function output()
    {
        echo (string)$this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function __toString(): string
    {
        return $this->radio->getScript();
    }
}