<?php

/**
 * Rangine Model Cache
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\CacheModel\Store;

use Illuminate\Cache\RedisTaggedCache;
use Illuminate\Cache\TaggableStore;
use Illuminate\Cache\TagSet;
use W7\Contract\Cache\CacheFactoryInterface;

class RedisStore extends TaggableStore {
	/**
	 * @var CacheFactoryInterface
	 */
	protected $cacheFactory;
	protected $prefix;
	protected $connection;

	public function __construct(CacheFactoryInterface $cacheFactory, $prefix = '', $connection = 'default') {
		if (empty($connection)) {
			$connection = 'default';
		}
		$this->cacheFactory = $cacheFactory;
		$this->setPrefix($prefix);
		$this->setConnection($connection);
	}

	public function get($key) {
		return $this->connection()->get($this->prefix.$key);
	}

	public function many(array $keys) {
		$values = $this->connection()->getMultiple(array_map(function ($key) {
			return $this->prefix.$key;
		}, $keys));

		return $values;
	}

	public function put($key, $value, $seconds) {
		return (bool) $this->connection()->setex(
			$this->prefix.$key,
			(int) max(1, $seconds),
			$this->serialize($value)
		);
	}

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

	public function increment($key, $value = 1) {
		return $this->connection()->incrby($this->prefix.$key, $value);
	}

	public function decrement($key, $value = 1) {
		return $this->connection()->decrby($this->prefix.$key, $value);
	}

	public function forever($key, $value) {
		return (bool) $this->connection()->set($this->prefix.$key, $value);
	}

	public function lock($name, $seconds = 0, $owner = null) {
	}

	public function restoreLock($name, $owner) {
		return $this->lock($name, 0, $owner);
	}

	public function forget($key) {
		return (bool) $this->connection()->delete($this->prefix.$key);
	}

	public function flush() {
		$this->connection()->clear();

		return true;
	}

	public function tags($names) {
		return new RedisTaggedCache(
			$this,
			new TagSet($this, is_array($names) ? $names : func_get_args())
		);
	}

	/**
	 * @return \Psr\SimpleCache\CacheInterface
	 */
	public function connection() {
		return $this->cacheFactory->channel($this->connection);
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
