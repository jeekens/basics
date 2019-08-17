<?php declare(strict_types=1);


namespace Jeekens\Basics;


class Os
{

    private static $screenSize;

    private static $shell;

    /**
     * 获取环境变量值
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public static function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * 执行脚本或壳
     *
     * @param string $command
     * @param bool $returnStatus
     * @param string|null $cwd
     *
     * @return array|string
     */
    public static function script(string $command, bool $returnStatus = true, string $cwd = null)
    {
        $exitStatus = 1;

        if ($cwd) {
            chdir($cwd);
        }

        // system
        if (function_exists('system')) {
            ob_start();
            system($command, $exitStatus);
            $output = ob_get_clean();

            // passthru
        } elseif (function_exists('passthru')) {
            ob_start();
            passthru($command, $exitStatus);
            $output = ob_get_clean();

            //exec
        } elseif (function_exists('exec')) {
            exec($command, $output, $exitStatus);
            $output = implode("\n", $output);

            //shell_exec
        } elseif (function_exists('shell_exec')) {
            $output = shell_exec($command);
        } else {
            $output = 'Command execution not possible on this system';
            $exitStatus = 0;
        }

        if ($returnStatus) {
            return [
                'output' => trim($output),
                'status' => $exitStatus
            ];
        }

        return trim($output);
    }

    /**
     * 判断是否为cli环境
     *
     * @return bool
     */
    public static function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * 判断是否为win系统
     *
     * @return bool
     */
    public static function isWin(): bool
    {
        return stripos(PHP_OS, 'WIN') === 0;
    }

    /**
     * 判断是否为mac系统
     *
     * @return bool
     */
    public static function isMac(): bool
    {
        return stripos(PHP_OS, 'Darwin') !== false;
    }

    /**
     * 返回终端屏幕大小
     *
     * @param bool $refresh
     *
     * @return array|bool
     */
    public static function getScreenSize(bool $refresh = false)
    {
        if (self::$screenSize !== null && !$refresh) {
            return self::$screenSize;
        }

        if (self::getShell()) {
            // try stty if available
            $stty = [];

            if (exec('stty -a 2>&1', $stty)
                && preg_match('/rows\s+(\d+);\s*columns\s+(\d+);/mi', implode(' ', $stty), $matches)
            ) {
                return (self::$screenSize = [$matches[2], $matches[1]]);
            }

            // fallback to tput, which may not be updated on terminal resize
            if (($width = (int)exec('tput cols 2>&1')) > 0 && ($height = (int)exec('tput lines 2>&1')) > 0) {
                return (self::$screenSize = [$width, $height]);
            }

            // fallback to ENV variables, which may not be updated on terminal resize
            if (($width = (int)getenv('COLUMNS')) > 0 && ($height = (int)getenv('LINES')) > 0) {
                return (self::$screenSize = [$width, $height]);
            }
        }

        if (self::isWin()) {
            $output = [];
            exec('mode con', $output);

            if (isset($output[1]) && strpos($output[1], 'CON') !== false) {
                return (self::$screenSize = [
                    (int)preg_replace('~\D~', '', $output[3]),
                    (int)preg_replace('~\D~', '', $output[4])
                ]);
            }
        }

        return (self::$screenSize = false);
    }

    /**
     * 获取当前shell环境名称，非shell环境返回false
     *
     * @from symfony
     *
     * @return bool|mixed
     */
    public static function getShell()
    {
        if (null !== self::$shell) {
            return self::$shell;
        }

        self::$shell = false;

        if (file_exists('/usr/bin/env')) {
            // handle other OSs with bash/zsh/ksh/csh if available to hide the answer
            $test = "/usr/bin/env %s -c 'echo OK' 2> /dev/null";

            foreach (['sh', 'bash', 'zsh', 'ksh', 'csh'] as $sh) {
                if ('OK' === rtrim(shell_exec(sprintf($test, $sh)))) {
                    self::$shell = $sh;
                    break;
                }
            }

        }

        return self::$shell;
    }

    /**
     * 判断终端是否支持ascii字符
     *
     * Based on https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Console/Output/StreamOutput.php
     *
     * @return bool
     */
    public function systemHasAnsiSupport()
    {
        return (function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(STDOUT))
            || false !== getenv('ANSICON')
            || 'ON' === getenv('ConEmuANSI')
            || 'Hyper' === getenv('TERM_PROGRAM')
            || 'xterm' === getenv('TERM');
    }

}