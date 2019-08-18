<?php declare(strict_types=1);


namespace Jeekens\Basics;


use function can_each;
use function get_object_vars;
use function is_array;
use function is_numeric;
use function is_object;
use function method_exists;
use function simplexml_load_string;

class Xml
{
    /**
     * 解码
     *
     * @param string $xml
     *
     * @return array
     */
    public static function decode(string $xml): array
    {
        $data = @(array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);

        if (isset($data[0]) && $data[0] === false) {
            $data = null;
        }

        if ($data) {
            $data = self::parseToArray($data);
        }

        return $data;
    }

    /**
     * 编码
     *
     * @param $data
     * @param string|null $rootNode
     * @param string|null $noNode
     * @param string|null $noNodeAttr
     * @param string|null $encoding
     * @param string|null $ver
     *
     * @return string
     */
    public static function encode($data, ?string $rootNode = null, ?string $noNode = null, string $noNodeAttr = null, string $encoding = null, string $ver = null): string
    {
        $root = $rootNode ?? 'xml';
        return '<?xml version="' . ($ver ?? '1.0') .
            '" encoding="' . ($encoding ?? 'utf-8') .
            '"?><' . $root . '>' . self::xmlAttr($data, $noNode ?? 'node', $noNodeAttr ?? 'id') . '</' . $root . '>';
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

        if (can_each($data)) {
            foreach ($data as $key => $val) {
                if (can_each($val)) {
                    $res[$key] = self::parseToArray($val);
                } else {
                    $res[$key] = $val;
                }
            }
        }

        return $res;
    }

    /**
     * @param $data
     * @param $noNode
     * @param $noNodeAttr
     *
     * @return array|string
     */
    private static function xmlAttr($data, $noNode, $noNodeAttr)
    {
        if (is_object($data)) {
            if (method_exists($data, '__toString')) {
                $data = $data->__toString();
            } else {
                $data = get_object_vars($data);
            }
        }

        if (is_array($data)) {
            $string = '';
            foreach ($data as $key => $val) {
                if (is_numeric($key)) {
                    $string .= "<{$noNode} {$noNodeAttr}=\"{$key}\">" . self::xmlAttr($val, $noNode, $noNodeAttr) . "</$noNode>";
                } else {
                    $string .= "<{$key}>" . self::xmlAttr($val, $noNode, $noNodeAttr) . "</{$key}>";
                }
            }
            return $string;
        } elseif (is_numeric($data)) {
            return $data;
        } elseif ($data === true) {
            return 'true';
        } elseif ($data === false) {
            return 'false';
        } elseif ($data === null) {
            return 'null';
        } else {
            return '<![CDATA[' . (string)$data . ']]>';
        }
    }

}