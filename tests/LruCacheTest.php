<?php declare(strict_types=1);

namespace Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Twistor\LruCache;

class LruCacheTest extends TestCase
{
    /**
     * @var \Twistor\LruCache<int, string>
     */
    private $cache;

    public function setUp(): void
    {
        /** @var \Twistor\LruCache<int, string> cache */
        $this->cache = new LruCache(2);

        $this->cache->set(1, 'first');
        $this->cache->set(2, 'second');
    }

    public function testConstructorValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LruCache(0);
    }

    public function testHas(): void
    {
        $this->assertTrue($this->cache->has(1));
        $this->assertTrue($this->cache->has(2));
        $this->assertFalse($this->cache->has(3));
    }

    public function testGet(): void
    {
        $this->assertSame('first', $this->cache->get(1));
        $this->assertSame('second', $this->cache->get(2));
        $this->assertNull($this->cache->get(3));
    }

    public function testOldKeysRemoved(): void
    {
        $this->cache->set(3, 'third');

        $this->assertFalse($this->cache->has(1));
        $this->assertTrue($this->cache->has(2));
        $this->assertTrue($this->cache->has(3));

        $this->assertNull($this->cache->get(1));
        $this->assertSame('second', $this->cache->get(2));
        $this->assertSame('third', $this->cache->get(3));
    }

    public function testGettingExistingTouchesKey(): void
    {
        // This makes 1 a more recently used key.
        $this->assertSame('first', $this->cache->get(1));

        $this->cache->set(3, 'third');

        $this->assertSame('first', $this->cache->get(1));
        $this->assertNull($this->cache->get(2));
        $this->assertSame('third', $this->cache->get(3));
    }

    public function testSettingExistingTouchesKey(): void
    {
        // This makes 1 a more recently used key.
        $this->cache->set(1, 'first updated');

        // When we set third, second should be removed.
        $this->cache->set(3, 'third');

        $this->assertSame('first updated', $this->cache->get(1));
        $this->assertNull($this->cache->get(2));
        $this->assertSame('third', $this->cache->get(3));
    }

    public function testGetWith(): void
    {
        $throws = function(int $key): string {
            throw new InvalidArgumentException();
        };

        $this->assertSame('first', $this->cache->getWith(1, $throws));
        $this->assertSame('second', $this->cache->getWith(2, $throws));

        $returnsThree = function(int $key): string {
            return 'third';
        };

        $this->assertSame('third', $this->cache->getWith(3, $returnsThree));
        $this->assertTrue($this->cache->has(3));
        $this->assertSame('third', $this->cache->getWith(3, $throws));
    }
}
