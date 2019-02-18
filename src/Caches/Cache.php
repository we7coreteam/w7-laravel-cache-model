<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 13:34
 */

namespace W7\Laravel\CacheModel\Caches;


use W7\Laravel\CacheModel\Exceptions\InvalidArgumentException;

/**
 * Class Cache
 * @package W7\Laravel\CacheModel
 */
class Cache
{
	const FOREVER = 3153600000; //86400 * 365 * 100;
	
	const NULL = 'nil&null';
	
	private static $singleton;
	
	public static function singleton()
	{
		return static::$singleton ?? (static::$singleton = new static());
	}
	
	/**
	 * @var \Psr\SimpleCache\CacheInterface
	 */
	private $cache;
	
	/**
	 * Cache constructor.
	 * @throws InvalidArgumentException
	 */
	public function __construct()
	{
		$this->cache = CacheResolver::getCacheResolver();
	}
	
	/**
	 * @param $key
	 * @param $model
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function set($key, $model)
	{
		$this->cache->set($key, $model, static::FOREVER);
	}
	
	/**
	 * @param $key
	 * @return mixed
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function get($key)
	{
		return $this->cache->get($key);
	}
	
	/**
	 * 没有模型也可能有缓存，防止缓存击穿
	 * @param $key
	 * @return bool
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function has($key)
	{
		return $this->cache->has($key);
	}
	
	/**
	 * @param $key
	 * @return bool
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function del($key)
	{
		return $this->cache->delete($key);
	}
	
	/**
	 * 清空当前表的缓存
	 * @param string $namespace
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function flush($namespace = '')
	{
		Tag::flush($namespace);
	}
	
	/**
	 * @param string         $key
	 * @param \stdClass|null $model
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function setModel($key, $model)
	{
		$model = $model ?? static::NULL;
		
		$this->set($key, $model);
	}
	
	/**
	 * 获取缓存中键为 $key 的记录
	 * @param $key
	 * @return \stdClass|null
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function getModel($key)
	{
		$model = $this->get($key);
		if ($model === static::NULL) {
			$model = null;
		}
		return $model;
	}
	
	/**
	 * @param string $key
	 * @return bool
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function delModel($key)
	{
		return $this->del($key);
	}
	
	/**
	 * 缓存中是否存在主键为 key 的记录
	 * @param $key
	 * @return bool
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function hasModel($key)
	{
		$model = $this->getModel($key);
		
		return !empty($model);
	}
}