<?php declare(strict_types=1);


namespace Jeekens\Basics;


use function chdir;
use function exec;
use function file_exists;
use function fstat;
use function function_exists;
use function getenv;
use function implode;
use function ob_get_clean;
use function ob_start;
use function passthru;
use function rtrim;
use function shell_exec;
use function sprintf;
use function stream_isatty;
use function stripos;
use function strlen;
use function strtolower;
use function substr;
use function system;
use function trim;

class Os
{

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
     * 判断终端是否支持ansi字符
     *
     * @param bool $isWin
     *
     * Based on https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Console/Output/StreamOutput.php
     *
     * @return bool
     */
    public static function systemHasAnsiSupport(bool $isWin = false)
    {
        if ($isWin) {
            return (function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(STDOUT))
                || false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'Hyper' === getenv('TERM_PROGRAM')
                || 'xterm' === getenv('TERM');
        } else {
            if ('Hyper' === getenv('TERM_PROGRAM')) {
                return true;
            }

            $stream = STDOUT;

            if (function_exists('stream_isatty')) {
                return @stream_isatty($stream);
            }

            if (function_exists('posix_isatty')) {
                return @posix_isatty($stream);
            }

            $stat = @fstat($stream);
            // Check if formatted mode is S_IFCHR
            return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
        }
    }

}