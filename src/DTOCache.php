<?php

declare(strict_types=1);

namespace Ser\DTORequestBundle;

use Closure;
use Ser\DTORequestBundle\Reflection\ReflectedClass;


class DTOCache
{
    private static array $cache = [];

    public static function resolve(string $class, Closure $closure): ReflectedClass
    {
        if (!isset(self::$cache[$class])) {
            self::$cache[$class] = $closure($class);
        }

        return self::$cache[$class];
    }
}
