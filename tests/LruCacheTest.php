<?php declare(strict_types=1);

namespace Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Twistor\LruCache;

class LruCacheTest extends TestCase
{
    public function testConstructorValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LruCache(0);
    }

    public function testOldKeysRemoved(): void
    {
        /** @var \Twistor\LruCache<int, string> $cache */
        $cache = new LruCache(2);

        $cache->set(1, 'first');
        $cache->set(2, 'second');

        $this->assertTrue($cache->has(1));
        $this->assertTrue($cache->has(2));
        $this->assertFalse($cache->has(3));

        $this->assertSame('first', $cache->get(1));
        $this->assertSame('second', $cache->get(2));
        $this->assertNull($cache->get(3));

        $cache->set(3, 'third');

        $this->assertFalse($cache->has(1));
        $this->assertTrue($cache->has(2));
        $this->assertTrue($cache->has(3));

        $this->assertNull($cache->get(1));
        $this->assertSame('second', $cache->get(2));
        $this->assertSame('third', $cache->get(3));
    }

    public function testGettingExistingTouchesKey(): void
    {
        /** @var \Twistor\LruCache<int, string> $cache */
        $cache = new LruCache(2);

        $cache->set(1, 'first');
        $cache->set(2, 'second');

        $this->assertSame('first', $cache->get(1));

        $cache->set(3, 'third');

        $this->assertSame('first', $cache->get(1));
        $this->assertNull($cache->get(2));
        $this->assertSame('third', $cache->get(3));
    }

    public function testSettingExistingTouchesKey(): void
    {
        /** @var \Twistor\LruCache<int, string> $cache */
        $cache = new LruCache(2);

        $cache->set(1, 'first');
        $cache->set(2, 'second');

        $cache->set(1, 'first updated');

        // When we set third, second should be removed.
        $cache->set(3, 'third');

        $this->assertSame('first updated', $cache->get(1));
        $this->assertNull($cache->get(2));
        $this->assertSame('third', $cache->get(3));
    }
}
