<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 30.09.2018
 * Time: 19:05
 */

namespace JRemmurd\IgniteBundle\Ignite\Channel\Encoder;

interface ChannelSignatureEncoderInterface
{
    /**
     * @param $name
     * @param array $parameters
     * @param bool $excludePrefix
     * @return string
     */
    public function encode($name, $parameters = [], $excludePrefix = false): string;

    /**
     * @param $channelName
     * @return array|null
     */
    public function decode($channelName): ?array;

    public function validateChannelSignature(string $text);

}