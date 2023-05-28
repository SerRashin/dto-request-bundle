<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle;

use Closure;
use Ser\DtoRequestBundle\Reflection\ReflectedClass;

class DtoCache
{
    private static array $cache = [];

    /**
     * Returns cached object
     *
     * @param string $class
     * @param Closure $closure
     *
     * @return ReflectedClass
     */
    public static function resolve(string $class, Closure $closure): ReflectedClass
    {
        if (!isset(self::$cache[$class])) {
            self::$cache[$class] = $closure($class);
        }

        return self::$cache[$class];
    }
}
