<?php


namespace Jeekens;


use ArrayAccess;

class Arr
{

    /**
     * 获取数组中的元素，支持「.」语法获取子元素
     *
     * @param array $array
     * @param string|null $key
     * @param null $default
     *
     * @return array|mixed|null
     */
    public static function get(array $array, ?string $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        $tmp = explode('.', $key, 2);

        if (isset($array[$tmp[0]]) && is_array($array[$tmp[0]])) {
            return self::get($array[$tmp[0]], $tmp[1] ?? null, $default);
        }

        return $default;
    }

    /**
     * 函数将多维数组中所有的键平铺到一维数组中，新数组使用「.」符号表示层级包含关系
     *
     * @param array $array
     * @param string $prepend
     *
     * @return array
     */
    public static function dot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * 函数使用「.」符号从深度嵌套的数组中删除给定的键
     *
     * @param array $target
     * @param $key
     *
     * @return array
     */
    public static function unset(array &$target, $key)
    {
        if (!is_array($target)) {
            return $target;
        }

        $segments = is_array($key) ? $key : explode('.', $key);
        $segment = array_shift($segments);

        if ($segment == '*') {
            $target = [];
        } elseif ($segments) {
            if (array_key_exists($segment, $target)) {
                static::unset($target[$segment], $segments);
            }
        } elseif (array_key_exists($segment, $target)) {
            unset($target[$segment]);
        }

        return $target;
    }

    /**
     * 判断数组是否为关联数组
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }

    /**
     * 返回给定数组中指定的键／值对
     *
     * @param array $array
     * @param $keys
     *
     * @return array
     */
    public static function only(array $array, $keys): array
    {
        if (empty($array)) {
            return [];
        } else {
            return array_intersect_key($array, array_flip((array) $keys));
        }
    }

    /**
     * 判断当前变量是否为数组或ArrayAccess
     *
     * @param $value
     *
     * @return bool
     */
    public static function is($value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

}