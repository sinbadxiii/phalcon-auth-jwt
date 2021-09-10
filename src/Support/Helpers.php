<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Support;

use Carbon\Carbon;

class Helpers {

    public static function now()
    {
        return Carbon::now('UTC');
    }

    public static function timestamp(int $timestamp): Carbon
    {
        return Carbon::createFromTimestampUTC($timestamp)
            ->timezone('UTC');
    }

    /**
     * Checks if a timestamp is in the past.
     */
    public static function is_past(int $timestamp, int $leeway = 0): bool
    {
        return self::timestamp($timestamp)
            ->addSeconds($leeway)
            ->isPast();
    }

    public static function is_future(int $timestamp, int $leeway = 0): bool
    {
        return self::timestamp($timestamp)
            ->subSeconds($leeway)
            ->isFuture();
    }
}