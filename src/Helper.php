<?php declare(strict_types=1);

if (!function_exists('throw_unless')) {
    /**
     * 如果条件执行结果为false则抛出异常
     *
     * @param  mixed $condition
     * @param  \Throwable|string $exception
     * @param  array ...$parameters
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    function throw_unless($condition, $exception, ...$parameters)
    {
        if (!$condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }

        return $condition;
    }
}

if (!function_exists('throw_if')) {
    /**
     * 如果条件执行结果为true则抛出异常
     *
     * @param  mixed $condition
     * @param  \Throwable|string $exception
     * @param  array ...$parameters
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    function throw_if($condition, $exception, ...$parameters)
    {
        if ($condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }

        return $condition;
    }
}

if (!function_exists('tolerant_null')) {
    /**
     * 接受闭包作为其第二个参数。如果第一个参数提供的值不是 null，闭包将被调用并且传入第一个参数
     *
     * @param mixed $value
     * @param Closure $closure
     *
     * @return mixed
     */
    function tolerant_null($value, Closure $closure)
    {
        return is_null($value) ? $value : $closure($value);
    }
}

if (!function_exists('class_basename')) {
    /**
     * 获取对象或类名不带命名空间的类名
     *
     * @param  string|object $class
     *
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('can_each')) {
    /**
     * 判断变量是否可被foreach处理
     *
     * @param $value
     *
     * @return bool
     */
    function can_each($value): bool
    {
        return $value instanceof iterable;
    }
}

if (!function_exists('class_init')) {
    /**
     * 对象快速初始化助手函数
     *
     * @param object $object
     * @param array|Traversable $options
     *
     * @return object
     */
    function class_init(object $object, $options): object
    {
        if (can_each($options)) {
            foreach ($options as $property => $value) {
                if (is_numeric($property)) {
                    continue;
                }

                $setter = 'set' . $property;
                // has setter
                if (method_exists($object, $setter)) {
                    $object->$setter($value);
                } elseif (property_exists($object, $property)) {
                    $object->$property = $value;
                }
            }
        }
        return $object;
    }

}

if (!function_exists('class_get')) {
    /**
     * 快速获取对象属性值
     *
     * @param object $object
     * @param string $name
     * @param string $prefix
     *
     * @return mixed|null
     */
    function class_get(object $object, string $name, string $prefix = 'get')
    {
        $method = $prefix . $name;
        if (property_exists($object, $name)) {
            return $object->$name;
        } elseif (method_exists($object, $method)) {
            return $object->$method();
        }
        return null;
    }
}

if (!function_exists('get_class_from_file')) {
    /**
     * 获取php文件中的类名
     *
     * @param string $path_to_file
     * @param bool $interface
     *
     * @return string|null
     */
    function get_class_from_file(string $path_to_file, bool $interface = false): ?string
    {
        //Grab the contents of the file
        $contents = file_get_contents($path_to_file);
        //Start with a blank namespace and class
        $namespace = $class = "";
        //Set helper values to know that we have found the namespace/class token and need to collect the string values after them
        $getting_namespace = $getting_class = false;
        //Go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {
            //If this token is the namespace declaring, then flag that the next tokens will be the namespace name
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $getting_namespace = true;
            }
            //If this token is the class declaring, then flag that the next tokens will be the class name
            if (is_array($token) && $token[0] == T_CLASS || ($interface && $token[0] == T_INTERFACE)) {
                $getting_class = true;
            }
            //While we're grabbing the namespace name...
            if ($getting_namespace === true) {
                //If the token is a string or the namespace separator...
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                    //Append the token's value to the name of the namespace
                    $namespace .= $token[1];
                } else if ($token === ';') {
                    //If the token is the semicolon, then we're done with the namespace declaration
                    $getting_namespace = false;
                }
            }
            //While we're grabbing the class name...
            if ($getting_class === true) {
                //If the token is a string, it's the name of the class
                if (is_array($token) && $token[0] == T_STRING) {
                    //Store the token's value as the class name
                    $class = $token[1];
                    //Got what we need, stope here
                    break;
                }
            }
        }

        if (empty($class)) return null;
        //Build the fully-qualified class name and return it
        return $namespace ? $namespace . '\\' . $class : $class;
    }
}

if (!function_exists('is_regular_expression')) {
    /**
     * 判断字符串是否是一个合法的正则表达式
     *
     * @param string $string
     *
     * @return bool
     */
    function is_regular_expression(string $string): bool
    {
        set_error_handler(function () {
        }, E_WARNING);
        $bool = preg_match($string, "") !== false;
        restore_error_handler();
        return $bool;
    }
}

if (!function_exists('value')) {
    /**
     * 获取变量值，如果变量为一个闭包则执行后返回值
     *
     * @param mixed $value
     *
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('call')) {
    /**
     * 执行回调
     *
     * @param $callback
     * @param mixed ...$args
     *
     * @return mixed
     */
    function call($callback, ...$args)
    {
        if (is_string($callback)) {
            // className::method
            if (strpos($callback, '::') > 0) {
                $callback = explode('::', $callback, 2);
                // function
            } elseif (function_exists($callback)) {
                return $callback(...$args);
            }
        } elseif (is_object($callback) && method_exists($callback, '__invoke')) {
            return $callback(...$args);
        }

        if (is_array($callback)) {
            [$obj, $method] = $callback;

            return is_object($obj) ? $obj->$method(...$args) : $obj::$method(...$args);
        }

        return $callback(...$args);
    }
}