<?php

namespace Aura;

use Closure;

class Aura
{
    /** @var Closure|null */
    protected static ?Closure $authCallback = null;

    public static function auth(Closure $callback): void
    {
        static::$authCallback = $callback;
    }

    public static function check($request): bool
    {
        if (static::$authCallback === null) {
            return app()->environment('local');
        }

        return (static::$authCallback)($request);
    }
}
