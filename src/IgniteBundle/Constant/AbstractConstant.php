<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 28.09.2018
 * Time: 22:54
 */

namespace Juup\IgniteBundle\Constant;


class AbstractConstant
{
    /**
     * @return array
     */
    public static function getAll()
    {
        $refl = new \ReflectionClass(get_called_class());
        return $refl->getConstants();
    }
}