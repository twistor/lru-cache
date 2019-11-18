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
     * @param string|int $key
     * @return mixed|null
     *
     * @psalm-param TKey $key
     * @psalm-return TValue|null
     */
    public function get($key)
    {
        if ( ! $this->has($key)) {
            return null;
        }

        return $this->touch($key, $this->cache[$key]);
    }

    /**
     * @param string|int $key
     * @param mixed $value
     *
     * @psalm-param TKey $key
     * @psalm-param TValue $value
     */
    public function set($key, $value): void
    {
        if ($this->has($key)) {
            $this->touch($key, $value);
            return;
        }

        if ($this->size < $this->capacity) {
            ++$this->size;
            $this->cache[$key] = $value;
            return;
        }

        // We're at capacity; delete the least recently used cache entry.
        unset($this->cache[key($this->cache)]);
        $this->cache[$key] = $value;
    }

    /**
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
            return $this->touch($key, $this->cache[$key]);
        }

        $value = $callback($key);

        $this->set($key, $value);

        return $value;
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
    private function touch($key, $value)
    {
        unset($this->cache[$key]);

        return $this->cache[$key] = $value;
    }
}
