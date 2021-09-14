<?php

declare(strict_types=1);

namespace Sinbadxiii\PhalconAuthJWT\Support;

use Carbon\Carbon;
use Phalcon\Helper\Arr;

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

    public static function pull(&$array, $key, $default = null)
    {
        $value = Arr::get($array, $key, $default);

        static::forget($array, $key);

        return $value;
    }

    public static function forget(&$array, $keys)
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            if (Arr::has($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }
}