<?php declare(strict_types=1);

namespace Twistor;

use InvalidArgumentException;
use function array_key_exists;
use function key;

/**
 * @template TKey as array-key
 * @template TValue
 */
final class LruCache
{
    /**
     * @var array
     *
     * @psalm-var array<TKey, TValue>
     */
    private $cache = [];

    /**
     * @var int
     */
    private $capacity;

    /**
     * @var int
     */
    private $size = 0;

    public function __construct(int $capacity)
    {
        if ($capacity < 1) {
            throw new InvalidArgumentException('Capacity must be greater than 0.');
        }

        $this->capacity = $capacity;
    }

    /**
     * Checks if a key exists in the cache.
     *
     * @param string|int $key
     * @return bool
     *
     * @psalm-param TKey $key
     * @psalm-return bool
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->cache);
    }

    /**
     * Returns a cache entry if it exists.
     *
     * @param string|int $key
     * @return mixed|null
     *
     * @psalm-param TKey $key
     * @psalm-return TValue|null
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->putExisting($key, $this->cache[$key]);
        }

        return null;
    }

    /**
     * Returns an existing cache entry or calls the callback to retrieve it.
     *
     * @param string|int $key
     * @param callable $callback
     * @return mixed
     *
     * @psalm-param TKey $key
     * @psalm-param callable(TKey): TValue $callback
     * @psalm-return TValue
     */
    public function getWith($key, callable $callback)
    {
        if ($this->has($key)) {
            return $this->putExisting($key, $this->cache[$key]);
        }

        return $this->putNew($key, $callback($key));
    }

    /**
     * Sets a cache entry.
     *
     * @param string|int $key
     * @param mixed $value
     *
     * @psalm-param TKey $key
     * @psalm-param TValue $value
     */
    public function put($key, $value): void
    {
        if ($this->has($key)) {
            $this->putExisting($key, $value);
        } else {
            $this->putNew($key, $value);
        }
    }

    /**
     * @param string|int $key
     * @param mixed $value
     * @return mixed
     *
     * @psalm-param TKey $key
     * @psalm-param TValue $value
     * @psalm-return TValue
     */
    private function putExisting($key, $value)
    {
        unset($this->cache[$key]);

        return $this->cache[$key] = $value;
    }

    /**
     * @param string|int $key
     * @param mixed $value
     * @return mixed
     *
     * @psalm-param TKey $key
     * @psalm-param TValue $value
     * @psalm-return TValue
     */
    private function putNew($key, $value)
    {
        // We're at capacity; delete the least recently used cache entry.
        if ($this->size === $this->capacity) {
            unset($this->cache[key($this->cache)]);
        } else {
            ++$this->size;
        }

        return $this->cache[$key] = $value;
    }
}
