<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 15.04.2018
 * Time: 16:04
 */

namespace JRemmurd\IgniteBundle\Constant;


class ChannelType extends AbstractConstant
{
    const PRIVATE = "private";
    const PRESENCE = "presence";
    const PUBLIC = "public";

    /**
     * @param $type
     * @return string
     */
    public static function getPrefix($type)
    {
        return "$type-";
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    private static function stringStartsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * @param $name
     * @return bool|string
     */
    public static function removePrefix($name)
    {
        foreach (ChannelType::getAllPrefixes() as $prefix) {
            if (self::stringStartsWith($name, $prefix)) {
                return substr($name, strlen($prefix));
            }
        }

        return $name;
    }

    /**
     * @return array
     */
    public static function getAllPrefixes()
    {
        $prefixes = self::getAll();

        array_walk($prefixes, function (&$type) {
            $type = self::getPrefix($type);
        });

        return $prefixes;
    }
}