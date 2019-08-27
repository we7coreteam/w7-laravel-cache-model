<?php

/**
 * Rangine Model Cache
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Laravel\CacheModel\Store;

use Illuminate\Cache\RedisTaggedCache;
use Illuminate\Cache\TaggableStore;
use Illuminate\Cache\TagSet;

class CacheStore extends TaggableStore {
	protected $prefix;
	protected $connection;

	/**
	 * Create a new store.
	 *
	 * @param  string  $prefix
	 * @param  string  $connection
	 * @return void
	 */
	public function __construct($prefix = '', $connection = 'default') {
		if (empty($connection)) {
			$connection = 'default';
		}
		$this->setPrefix($prefix);
		$this->setConnection($connection);
	}

	/**
	 * Retrieve an item from the cache by key.
	 *
	 * @param  string|array  $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->connection()->get($this->prefix.$key);
	}

	/**
	 * Retrieve multiple items from the cache by key.
	 *
	 * Items not found in the cache will have a null value.
	 *
	 * @param  array  $keys
	 * @return array
	 */
	public function many(array $keys) {
		$values = $this->connection()->getMultiple(array_map(function ($key) {
			return $this->prefix.$key;
		}, $keys));

		return $values;
	}

	/**
	 * Store an item in the cache for a given number of seconds.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  int  $seconds
	 * @return bool
	 */
	public function put($key, $value, $seconds) {
		return (bool) $this->connection()->setex(
			$this->prefix.$key,
			(int) max(1, $seconds),
			$this->serialize($value)
		);
	}

	/**
	 * Store multiple items in the cache for a given number of seconds.
	 *
	 * @param  array  $values
	 * @param  int  $seconds
	 * @return bool
	 */
	public function putMany(array $values, $seconds) {
		$this->connection()->multi();

		$manyResult = null;

		foreach ($values as $key => $value) {
			$result = $this->put($key, $value, $seconds);

			$manyResult = is_null($manyResult) ? $result : $result && $manyResult;
		}

		$this->connection()->exec();

		return $manyResult ?: false;
	}

	/**
	 * Store an item in the cache if the key doesn't exist.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  int  $seconds
	 * @return bool
	 */
	public function add($key, $value, $seconds) {
		$lua = "return redis.call('exists',KEYS[1])<1 and redis.call('setex',KEYS[1],ARGV[2],ARGV[1])";

		return (bool) $this->connection()->eval(
			$lua,
			1,
			$this->prefix.$key,
			$this->serialize($value),
			(int) max(1, $seconds)
		);
	}

	/**
	 * Increment the value of an item in the cache.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return int
	 */
	public function increment($key, $value = 1) {
		return $this->connection()->incrby($this->prefix.$key, $value);
	}

	/**
	 * Decrement the value of an item in the cache.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return int
	 */
	public function decrement($key, $value = 1) {
		return $this->connection()->decrby($this->prefix.$key, $value);
	}

	/**
	 * Store an item in the cache indefinitely.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return bool
	 */
	public function forever($key, $value) {
		return (bool) $this->connection()->set($this->prefix.$key, $value);
	}

	/**
	 * Get a lock instance.
	 *
	 * @param  string $name
	 * @param  int $seconds
	 * @param  string|null $owner
	 * @return \Illuminate\Contracts\Cache\Lock
	 */
	public function lock($name, $seconds = 0, $owner = null) {
	}

	/**
	 * Restore a lock instance using the owner identifier.
	 *
	 * @param  string  $name
	 * @param  string  $owner
	 * @return \Illuminate\Contracts\Cache\Lock
	 */
	public function restoreLock($name, $owner) {
		return $this->lock($name, 0, $owner);
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function forget($key) {
		return (bool) $this->connection()->del($this->prefix.$key);
	}

	/**
	 * Remove all items from the cache.
	 *
	 * @return bool
	 */
	public function flush() {
		$this->connection()->clear();

		return true;
	}

	/**
	 * Begin executing a new tags operation.
	 *
	 * @param  array|mixed  $names
	 * @return \Illuminate\Cache\RedisTaggedCache
	 */
	public function tags($names) {
		return new RedisTaggedCache(
			$this,
			new TagSet($this, is_array($names) ? $names : func_get_args())
		);
	}

	/**
	 * Get the Redis connection instance.
	 *
	 * @return \Predis\ClientInterface
	 */
	public function connection() {
		return icache()->channel($this->connection);
	}

	/**
	 * Set the connection name to be used.
	 *
	 * @param  string  $connection
	 * @return void
	 */
	public function setConnection($connection) {
		$this->connection = $connection;
	}

	/**
	 * Get the Redis database instance.
	 *
	 * @return \Illuminate\Contracts\Redis\Factory
	 */
	public function getRedis() {
		return icache();
	}

	/**
	 * Get the cache key prefix.
	 *
	 * @return string
	 */
	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * Set the cache key prefix.
	 *
	 * @param  string  $prefix
	 * @return void
	 */
	public function setPrefix($prefix) {
		$this->prefix = ! empty($prefix) ? $prefix.':' : '';
	}

	/**
	 * Serialize the value.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function serialize($value) {
		return is_numeric($value) ? $value : serialize($value);
	}

	/**
	 * Unserialize the value.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function unserialize($value) {
		return is_numeric($value) ? $value : unserialize($value);
	}
}
