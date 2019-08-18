<?php declare(strict_types = 1);

namespace Jeekens\Basics;


use Jeekens\Basics\Exception\JsonDecodeException;
use Jeekens\Basics\Exception\JsonEncodeException;
use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;

class Json
{

    /**
     * @param string $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     *
     * @return mixed
     *
     * @throws JsonDecodeException
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