<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 07.10.2018
 * Time: 20:26
 */

namespace Juup\IgniteBundle\Ignite\Driver;


abstract class AbstractDriver implements DriverInterface
{
    /* @var int $priority */
    protected $priority = 0;

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     * @return AbstractDriver
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
        return $this;
    }

}