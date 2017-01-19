<?php
declare(strict_types = 1);

namespace Roave\DoctrineSimpleCache;

use Doctrine\Common\Cache\Cache as DoctrineCache;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\MultiGetCache;
use Doctrine\Common\Cache\MultiPutCache;
use Psr\SimpleCache\CacheInterface as PsrCache;

final class SimpleCacheAdapter implements PsrCache
{
    /**
     * @var DoctrineCache|ClearableCache|MultiGetCache|MultiPutCache
     */
    private $doctrineCache;

    /**
     * @param DoctrineCache $doctrineCache
     * @throws \Roave\DoctrineSimpleCache\Exception\CacheException
     */
    public function __construct(DoctrineCache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;

        if (!$this->doctrineCache instanceof ClearableCache) {
            throw Exception\CacheException::fromNonClearableCache($this->doctrineCache);
        }
        if (!$this->doctrineCache instanceof MultiGetCache) {
            throw Exception\CacheException::fromNonMultiGetCache($this->doctrineCache);
        }
        if (!$this->doctrineCache instanceof MultiPutCache) {
            throw Exception\CacheException::fromNonMultiPutCache($this->doctrineCache);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        $value = $this->doctrineCache->fetch($key);
        if ($value === false) {
            return $default;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null) : bool
    {
        if ($ttl !== null) {
            if ($ttl instanceof \DateInterval) {
                $ttl = (new \DateTime())->setTimeStamp(0)->add($ttl)->getTimeStamp();
            }

            if (!is_integer($ttl)) {
                throw new InvalidArgumentException;
            }

            if ($ttl <= 0) {
                return $this->doctrineCache->delete($key);
            }
        }

        return $this->doctrineCache->save($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key) : bool
    {
        return $this->doctrineCache->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear() : bool
    {
        return $this->doctrineCache->deleteAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getMultiple($keys, $default = null)
    {
        return array_merge(array_fill_keys($keys, $default), $this->doctrineCache->fetchMultiple($keys));
    }

    /**
     * {@inheritDoc}
     */
    public function setMultiple($values, $ttl = null) : bool
    {
        if ($ttl !== null) {
            if ($ttl instanceof \DateInterval) {
                $ttl = (new \DateTime())->setTimeStamp(0)->add($ttl)->getTimeStamp();
            }

            if (!is_integer($ttl)) {
                throw new InvalidArgumentException;
            }

            if ($ttl <= 0) {
                return $this->deleteMultiple(array_keys($values));
            }
        }

        return $this->doctrineCache->saveMultiple($values, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMultiple($keys) : bool
    {
        $success = true;

        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function has($key) : bool
    {
        return $this->doctrineCache->contains($key);
    }
}
