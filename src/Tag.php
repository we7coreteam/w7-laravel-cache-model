<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/17 0017
 * Time: 12:46
 */

namespace W7\Laravel\CacheModel;

use W7\Laravel\CacheModel\Exceptions\InvalidArgumentException;

/**
 * 实现数据库缓存命名空间
 * Class Tag
 * @package W7\Laravel\CacheModel
 */
class Tag
{
	/**
	 * @var string
	 */
	protected $prefix = 'model_cache:tag';
	
	/**
	 * @var string
	 */
	protected $db;
	
	/**
	 * @var string
	 */
	protected $table;
	
	/**
	 * @var CacheResolver
	 */
	protected $cache;
	
	/**
	 * @var bool
	 */
	protected $debug = true;
	
	/**
	 * Tag constructor.
	 * @param $db
	 * @param $table
	 * @throws InvalidArgumentException
	 */
	public function __construct($db, $table)
	{
		$this->db    = $db ?: 'default';
		$this->table = $table;
		
		$this->cache = new CacheResolver();
	}
	
	/**
	 * @param $key
	 * @param $value
	 * @return bool
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	private function cacheSet($key, $value)
	{
		return $this->cache->set($key, $value);
	}
	
	/**
	 * @param string $key
	 * @param null   $default
	 * @return mixed
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	private function cacheGet($key, $default = null)
	{
		return $this->cache->get($key, $default);
	}
	
	/**
	 * md5(unique($str))
	 * @param $content
	 * @return string
	 */
	private function hashValue($content)
	{
		return md5(uniqid($content . str_random(8)));
	}
	
	/**
	 * @internal
	 * @return string
	 */
	public function getDBKey()
	{
		return "{$this->prefix}:{$this->db}";
	}
	
	/**
	 * @internal
	 * @return mixed
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function getDBValue()
	{
		$value = $this->cacheGet($this->getDBKey());
		if (empty($value)) {
			$value = $this->flushDBValue();
		}
		return $value;
	}
	
	/**
	 * @internal
	 * @return int|mixed|string
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function flushDBValue()
	{
		$newValue = $this->genDBValue();
		
		$this->cacheSet($this->getDBKey(), $newValue);
		
		return $newValue;
	}
	
	/**
	 * @internal
	 * @return int|mixed|string
	 */
	public function genDBValue()
	{
		return $this->hashValue($this->getDBKey());
	}
	
	/**
	 * @internal
	 * @return string
	 */
	public function getTableKey()
	{
		return "{$this->prefix}:{$this->db}:{$this->table}";
	}
	
	/**
	 * @internal
	 * @return mixed|string
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function getTableValue()
	{
		$value = $this->cacheGet($this->getTableKey());
		if (empty($value)) {
			$value = $this->flushTableValue();
		}
		return $value;
	}
	
	/**
	 * @internal
	 * @return string
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function genTableValue()
	{
		return $this->hashValue($this->getTableKey() . ':' . $this->getDBValue());
	}
	
	/**
	 * @internal
	 * @return string
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function flushTableValue()
	{
		$newValue = $this->genTableValue();
		
		$this->cacheSet($this->getTableKey(), $newValue);
		
		return $newValue;
	}
	
	/**
	 * 清空当前表缓存
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function flush()
	{
		$this->flushTableValue();
	}
	
	/**
	 * 清空当前数据库缓存
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function flushAll()
	{
		$this->flushDBValue();
	}
	
	/**
	 * 获取 primaryKey 对应的缓存里面的键
	 * @param $primaryKey
	 * @return string
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function getCacheKey($primaryKey)
	{
		return $this->getTableKey() . ':' . $this->getTableValue() . ':' . $primaryKey;
	}
	
	/**
	 * @param $primaryKey
	 * @param $value
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function setModel($primaryKey, $value)
	{
		$this->cacheSet($this->getCacheKey($primaryKey), $value);
	}
	
	/**
	 * @param $primaryKey
	 * @return mixed
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function getModel($primaryKey)
	{
		return $this->cacheGet($this->getCacheKey($primaryKey));
	}
	
	/**
	 * @param $primaryKey
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function delModel($primaryKey)
	{
		$this->cache->delete($this->getCacheKey($primaryKey));
	}
}