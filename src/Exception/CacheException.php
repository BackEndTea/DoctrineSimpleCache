<?php
declare(strict_types = 1);

namespace Roave\DoctrineSimpleCache\Exception;

use Psr\SimpleCache\CacheException as PsrCacheException;
use Doctrine\Common\Cache\Cache as DoctrineCache;

final class CacheException extends \RuntimeException implements PsrCacheException
{
    public static function fromNonClearableCache(DoctrineCache $cache) : self
    {
        return new self(sprintf(
            'The given cache %s was not clearable, but you tried to use a feature that requires a clearable cache.',
            get_class($cache)
        ));
    }

    public static function fromNonMultiGetCache(DoctrineCache $cache) : self
    {
        return new self(sprintf(
            'The given cache %s cannot multi-get, but you tried to use a feature that requires a multi-get cache.',
            get_class($cache)
        ));
    }

    public static function fromNonMultiPutCache(DoctrineCache $cache) : self
    {
        return new self(sprintf(
            'The given cache %s cannot multi-put, but you tried to use a feature that requires a multi-put cache.',
            get_class($cache)
        ));
    }
}
