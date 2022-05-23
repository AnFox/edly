<?php


namespace App\Traits;

use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Trait ClearsResponseCache
 * Leverage model events to clear the cache whenever a model is saved or deleted.
 * @package App\Traits
 */
trait ClearsResponseCache
{
    public static function bootClearsResponseCache()
    {
        self::created(function () {
            ResponseCache::clear([self::class]);
        });

        self::updated(function () {
            ResponseCache::clear([self::class]);
        });

        self::deleted(function () {
            ResponseCache::clear([self::class]);
        });
    }
}