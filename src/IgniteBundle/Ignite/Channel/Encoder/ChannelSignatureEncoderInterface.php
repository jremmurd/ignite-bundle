<?php
/**
 * Created by PhpStorm.
 * User: Julian Raab
 * Date: 30.09.2018
 * Time: 19:05
 */

namespace Juup\IgniteBundle\Ignite\Channel\Encoder;

interface ChannelSignatureEncoderInterface
{
    /**
     * @param $identifier
     * @param array $parameters
     * @param bool $excludePrefix
     * @return string
     */
    public function encode($identifier, $parameters = [], $excludePrefix = false): string;

    /**
     * @param $channelName
     * @return array|null
     */
    public function decode($channelName): ?array;
}