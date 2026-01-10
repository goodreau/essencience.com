<?php

namespace App\Services\Modules;

class ModuleAutoloader
{
    protected static bool $registered = false;
    protected static array $maps = []; // [ 'Namespace\\' => ['/abs/path/src'] ]

    public static function register(): void
    {
        if (self::$registered) return;
        spl_autoload_register(function ($class) {
            foreach (self::$maps as $ns => $dirs) {
                if (str_starts_with($class, $ns)) {
                    $rel = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($ns))).'.php';
                    foreach ($dirs as $dir) {
                        $file = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$rel;
                        if (is_file($file)) {
                            require_once $file;
                            return true;
                        }
                    }
                }
            }
            return false;
        });
        self::$registered = true;
    }

    /**
     * @param array<string,string|array<int,string>> $psr4
     * @param string $basePath
     */
    public static function addPsr4(array $psr4, string $basePath): void
    {
        self::register();
        foreach ($psr4 as $ns => $paths) {
            $paths = (array) $paths;
            foreach ($paths as $p) {
                self::$maps[$ns][] = realpath($basePath.DIRECTORY_SEPARATOR.$p) ?: ($basePath.DIRECTORY_SEPARATOR.$p);
            }
        }
    }
}
