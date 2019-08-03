<?php

namespace Jeekens;


use Jeekens\Exception\JsonDecodeException;
use Jeekens\Exception\JsonEncodeException;

class Json
{

    /**
     * decode
     *
     * @param string $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     *
     * @throws JsonDecodeException
     *
     * @return mixed
     */
    public static function decode(string $json, bool $assoc = false, int $depth = 512, int $options = 0)
    {
        $data = json_decode($json, $assoc, $depth, $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonDecodeException(
                'json_decode error: ' . json_last_error_msg()
            );
        }

        return $data;
    }

    /**
     * encode
     *
     * @param $value
     * @param int $options
     * @param int $depth
     *
     * @throws JsonEncodeException
     *
     * @return string
     */
    public static function encode($value, int $options = 0, int $depth = 512): string
    {
        $json = json_encode($value, $options, $depth);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonEncodeException(
                'json_encode error: ' . json_last_error_msg()
            );
        }

        return $json;
    }

}