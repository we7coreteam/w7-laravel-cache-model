<?php
/**
 * Created by PhpStorm.
 * User: gorden
 * Date: 19-2-15
 * Time: 下午2:51
 */

namespace W7\Laravel\CacheModel;


use Psr\SimpleCache\CacheInterface;

class SimpleCache
{
	const NULL = 'nil&null';
	
	const FOREVER = 3153600000; //86400 * 365 * 100;
	
	/**
	 * @var CacheInterface
	 */
	public static $cacheInterface;
	
	public static function setCacheInterface($cacheInterface)
	{
		static::$cacheInterface = $cacheInterface;
	}
	
	/**
	 * @var CacheInterface
	 */
	private $cache;
	
	/**
	 * @var SimpleTag
	 */
	private $tag;
	
	/**
	 * SimpleCache constructor.
	 */
	public function __construct()
	{
		$this->cache = static::$cacheInterface;
	}
	
	/**
	 * @param SimpleTag $simpleTag
	 */
	public function setSimpleTag($simpleTag)
	{
		$this->tag = $simpleTag;
	}
	
	private function forever($key, $value)
	{
		$this->cache->set($key, $value, static::FOREVER);
	}
	
	public function dbCacheValue()
	{
		$cacheKey   = $this->dbCacheKey();
		$cacheValue = $this->get($cacheKey);
		if (empty($cacheValue)) {
			$cacheValue = $this->dbNewCacheValue();
			$this->forever($cacheKey, $cacheValue);
		}
		return $cacheValue;
	}
	
	public function dbCacheKey()
	{
		return $this->tag->db();
	}
	
	public function dbNewCacheValue()
	{
		return $this->tag->dbHash();
	}
	
	public function dbFlush()
	{
		$this->forever($this->dbCacheKey(), $this->dbNewCacheValue());
	}
	
	public function flushAll()
	{
		$this->dbFlush();
	}
	
	public function tableCacheValue()
	{
		$cacheKey   = $this->tableCacheKey();
		$cacheValue = $this->get($cacheKey);
		if (empty($cacheValue)) {
			$cacheValue = $this->tableNewCacheValue();
			$this->forever($cacheKey, $cacheValue);
		}
		return $cacheValue;
	}
	
	public function tableCacheKey()
	{
		return $this->tag->table() . ':' . $this->dbCacheValue();
	}
	
	public function tableNewCacheValue()
	{
		return $this->tag->tableHash($this->dbCacheValue());
	}
	
	public function tableFlush()
	{
//		ll('table flush ' . $this->tableCacheKey());
		$this->forever($this->tableCacheKey(), $this->tableNewCacheValue());
	}
	
	public function flush()
	{
		$this->tableFlush();
	}
	
	public function getCacheKey($key)
	{
		//		return $this->tableCacheKey() . ':' . $this->tableCacheValue() . ':' . $key;
		return $this->tableCacheValue() . ':' . $key;
	}
	
	public function set($key, $value)
	{
		$this->forever($key, $value);
	}
	
	public function get($key)
	{
		ll($this->cache);;
		return $this->cache->get($key);
	}
	
	public function del($key)
	{
		return $this->cache->delete($key);
	}
	
	public function has($key)
	{
		return $this->cache->get($key) !== null;
	}
	
	public function setModel($key, $value)
	{
		if (is_null($value)) {
			$value = static::NULL;
		}
		$this->set($this->getCacheKey($key), $value);
	}
	
	public function getModel($key)
	{
		$model = $this->get($this->getCacheKey($key));
		if ($model === static::NULL) {
			$model = null;
		}
		return $model;
	}
	
	public function delModel($key)
	{
		$this->del($this->getCacheKey($key));
	}
	
	public function hasModel($key)
	{
		return $this->getModel($this->getCacheKey($key)) !== null;
	}
	
	public function hasModelKey($key)
	{
		$value = $this->get($this->getCacheKey($key));
		return $value !== static::NULL && $value !== null;
	}
}