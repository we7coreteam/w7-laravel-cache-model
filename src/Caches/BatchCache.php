<?php


namespace W7\Laravel\CacheModel\Caches;


use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;

class BatchCache
{
	private $cache = null;
	
	private $namespace = 'batch_cache';
	
	/**
	 * BatchCache constructor.
	 * @param Cache  $cache
	 * @param string $namespace
	 */
	public function __construct(Cache $cache, $namespace = 'batch_cache')
	{
		$this->cache     = $cache;
		$this->namespace = $namespace;
	}
	
	protected function getCache()
	{
		return $this->cache;
	}
	
	/**
	 * @param string $key
	 * @return string
	 * @throws InvalidArgumentException
	 */
	protected function getCacheKey($key)
	{
		return Tag::getCacheKey($key, $this->namespace);
	}
	
	/**
	 * @param $key
	 * @return string
	 * @throws InvalidArgumentException
	 */
	protected function getSizeCacheKey($key)
	{
		return $this->getCacheKey("{$key}:size");
	}
	
	/**
	 * @param $key
	 * @param $index
	 * @return string
	 * @throws InvalidArgumentException
	 */
	protected function getIndexCacheKey($key, $index)
	{
		return $this->getCacheKey("{$key}:{$index}");
	}
	
	/**
	 * @param string $key
	 * @return int
	 * @throws InvalidArgumentException
	 */
	protected function getSize($key)
	{
		$sizeCacheKey = $this->getSizeCacheKey($key);
		
		return $this->getCache()->get($sizeCacheKey);
	}
	
	/**
	 * @param $key
	 * @param $value
	 * @throws InvalidArgumentException
	 */
	protected function setSize($key, $value)
	{
		$sizeCacheKey = $this->getSizeCacheKey($key);
		
		$this->getCache()->set($sizeCacheKey, $value);
	}
	
	/**
	 * @param $key
	 * @param $i
	 * @param $item
	 * @throws InvalidArgumentException
	 */
	protected function setItem($key, $i, $item)
	{
		$indexCacheKey = $this->getIndexCacheKey($key, $i);
		
		$this->getCache()->set($indexCacheKey, $item);
	}
	
	/**
	 * @param $key
	 * @param $index
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	protected function getItem($key, $index)
	{
		$indexCacheKey = $this->getIndexCacheKey($key, $index);
		
		return $this->getCache()->get($indexCacheKey);
	}
	
	/**
	 * @param mixed $key
	 * @param array $array
	 * @throws InvalidArgumentException
	 */
	public function set($key, $array)
	{
		$size = count($array);
		
		$this->setSize($key, $size);
		
		// TODO:
		// keys: 0, 1 , 2, ...
		// keys: 'a', 'b', 'c'
		
		$cache = $this->getCache();
		foreach ($array as $index => $item) {
			$cacheKey = $this->getIndexCacheKey($key, $index);
			
			$cache->set($cacheKey, $item);
		}
	}
	
	/**
	 * @param $key
	 * @throws InvalidArgumentException
	 */
	public function flush($key)
	{
		$this->setSize($key, 0);
	}
	
	/**
	 * 返回值为 false, 数据损坏
	 * @param $key
	 * @return bool|Collection|array
	 * @throws InvalidArgumentException
	 */
	public function get($key)
	{
		$cacheValue = [];
		
		$size = $this->getSize($key);
		if (empty($size)) {
			return null;
		}
		
		$realSize = 0;
		for ($i = 0; $i < $size; $i++) {
			$cacheItemKey   = $this->getIndexCacheKey($key, $i);
			$cacheItemValue = $this->getCache()->get($cacheItemKey);
			if (empty($cacheItemValue)) {
				continue;
			}
			$realSize++;
			$cacheValue[] = $cacheItemValue;
		}
		
		if ($realSize != $size) {
			return false;
		}
		
		return $cacheValue;
	}
	
	/**
	 * @param $key
	 * @throws InvalidArgumentException
	 */
	public function delete($key)
	{
		$this->getCache()->del($this->getSizeCacheKey($key));
	}
}