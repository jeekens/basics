<?php declare(strict_types = 1);


namespace Jeekens\Basics;


class Str
{

    /**
     * 函数将给定的字符串转换为 snake_case「蛇式」fooBar -> foo_bar
     *
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return $value;
    }

    /**
     * 生成指定长度的随机字符串
     *
     * @param int $length
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function random(int $length = 12): string
    {
        mt_srand();
        $length = ($length < 4) ? 4 : $length;
        return bin2hex(random_bytes(($length-($length%2))/2));
    }

    /**
     * 替换字符串中给定值的第一个给定值
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     *
     * @return string
     */
    public static function replaceFirst(string $search, string $replace, string $subject): string
    {
        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * 替换字符串中最后一次出现的给定值
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     *
     * @return string
     */
    public static function replaceLast(string $search, string $replace, string $subject): string
    {
        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * 判断给定的字符串的开头是否是指定值
     *
     * @param string $haystack
     * @param string $needles
     *
     * @return bool
     */
    public static function startsWith(string $haystack, $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断给定的字符串的末尾是否为指定值
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function endsWith(string $haystack, $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * 将给定的字符串转换为大写开头的驼峰
     *
     * @param string $value
     *
     * @return string
     */
    public static function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * 将给定的字符串转换为小写开头的驼峰
     *
     * @param string $value
     *
     * @return string
     */
    public static function camel(string $value): string
    {
        return lcfirst(static::studly($value));
    }

    /**
     * 将给定的字符串以给定的值结尾返回，例如路径末尾添加斜杠
     *
     * @param string $value
     * @param string $cap
     *
     * @return string
     */
    public static function finish(string $value, string $cap): string
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/u', '', $value).$cap;
    }

    /**
     * 返回字符串长度
     *
     * @param string $value
     * @param string|null
     *
     * @return int
     */
    public static function length(string $value, ?string $encoding = null): int
    {
        if (empty($encoding)) {
            $encoding = mb_internal_encoding();
        }
        return mb_strlen($value, $encoding);
    }

    /**
     * 字符串截断
     *
     * @param string $value
     * @param string|null $encoding
     * @param int $limit
     * @param string $end
     *
     * @return string
     */
    public static function limit(string $value, ?string $encoding = null, int $limit = 100, string $end = '...')
    {
        if (empty($encoding)) {
            $encoding = mb_internal_encoding();
        }

        if (mb_strwidth($value, $encoding) <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', $encoding)).$end;
    }

}