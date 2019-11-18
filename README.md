# LRU Cache

[![Author](http://img.shields.io/badge/author-@chrisleppanen-blue.svg?style=flat-square)](https://twitter.com/chrisleppanen)
[![Build Status](https://img.shields.io/travis/twistor/lru-cache/master.svg?style=flat-square)](https://travis-ci.org/twistor/lru-cache)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/twistor/lru-cache.svg?style=flat-square)](https://packagist.org/packages/twistor/lru-cache)
[![Total Downloads](https://img.shields.io/packagist/dt/twistor/lru-cache.svg?style=flat-square)](https://packagist.org/packages/twistor/lru-cache)

An LRU (least recently used) cache allows you to keep an in memory cache of objects.
The oldest cache items will be removed once the capacity limit is reached.

## Installation

```bash
composer require twistor/lru-cache
```

## Usage

```php
use Twistor\LruCache;

$cache = new LruCache(100);

$cache->put('my_key', 1);

$cache->get('my_key');

$cache->getWith('new_key', function ($key) {
   return 2;
});
```
