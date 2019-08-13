<?php


namespace Jeekens\Basics;


class Xml
{
    /**
     * 解码
     *
     * @param string $xml
     *
     * @return array
     *
     * @throws Exception\JsonDecodeException
     * @throws Exception\JsonEncodeException
     */
    public static function decode(string $xml): array
    {
        return self::xmlToArray($xml);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public static function encode(array $data): string
    {
        $xml = '<xml>';
        $xml .= self::arrayToXml($data);
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * @param string $xml
     *
     * @return array
     *
     * @throws Exception\JsonDecodeException
     * @throws Exception\JsonEncodeException
     */
    public static function xmlToArray(string $xml): array
    {
        $string  = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        $jsonStr = Json::encode($string);
        $data    = Json::decode($jsonStr, true);
        if ($data === false) {
            return [];
        }

        return $data;
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected static function parseToArray($data): array
    {
        $res = null;
        if (is_object($data)) {
            $data = (array)$data;
        }
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_iterable($val)) {
                    $res[$key] = self::parseToArray($val);
                } else {
                    $res[$key] = $val;
                }
            }
        }
        return $res;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    public static function arrayToXml(array $data): string
    {
        $xml = '';
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $xml .= "<$key>";
                if (is_iterable($val)) {
                    $xml .= self::arrayToXml($val);
                } elseif (is_numeric($val)) {
                    $xml .= $val;
                } else {
                    $xml .= self::characterDataReplace($val);
                }
                $xml .= "</$key>";
            }
        }
        return $xml;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected static function characterDataReplace(string $string): string
    {
        return sprintf('<![CDATA[%s]]>', $string);
    }
}