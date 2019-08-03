<?php declare(strict_types = 1);


namespace Jeekens\Basics;


class Data
{

    /**
     * 数据转xml格式串
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
    public static function toXml($data, ?string $rootNode = null, ?string $noNode = null, string $noNodeAttr = null, string $encoding = null, string $ver = null): string
    {
        $root = $rootNode ?? 'xml';
        return '<?xml version="'.($ver ?? '1.0').
            '" encoding="'.($encoding ?? 'utf-8').
            '"?><'.$root. '>'.self::xmlAttr($data, $noNode ?? 'node', $noNodeAttr ?? 'id').'</'.$root.'>';
    }

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
                if(is_numeric($key)){
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
            return '<![CDATA['.(string)$data.']]>';
        }
    }

}