<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 17:52
 */

namespace Juup\IgniteBundle\Ignite\Channel;


use Carbon\Carbon;

abstract class AbstractEvent implements EventInterface
{

    /* @var null|array $data */
    protected $data;

    /* @var Carbon $createdAt */
    protected $createdAt;

    /* @var string $name */
    protected $name;

    /**
     * AbstractEvent constructor.
     * @param array $data
     */
    public function __construct($data = [])
    {
        if (!is_array($data)) {
            $data = ["data" => $data];
        }

        $this->data = $data;
        $this->createdAt = Carbon::now();
        $this->data["createdAt"] = (string)$this->getCreatedAt();
    }

    /**
     * @return array
     */
    public function getData()
    {
        if ($name = $this->getName()) {
            $this->data["name"] = $name;
        }

        return $this->data;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt():Carbon
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }



}