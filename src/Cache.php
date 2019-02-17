<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 13:34
 */

namespace W7\Laravel\CacheModel;


use W7\Laravel\CacheModel\Exceptions\InvalidArgumentException;

/**
 * Class Cache
 * @package W7\Laravel\CacheModel
 */
class Cache
{
	public const NULL = 'nil&null';
	
	/**
	 * @var CacheResolver
	 */
	private $cache;
	
	/**
	 * @var Tag
	 */
	private $tag;
	
	/**
	 * Cache constructor.
	 * @throws InvalidArgumentException
	 */
	public function __construct()
	{
		$this->cache = new CacheResolver();
	}
	
	/**
	 * @param Tag $tag
	 * @return Cache
	 */
	public function tags($tag)
	{
		$this->tag = $tag;
		
		return $this;
	}
	
	/**
	 * @param $key
	 * @return string
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	private function cacheKey($key)
	{
		return $this->tag->getCacheKey($key);
	}
	
	/**
	 * @param $key
	 * @param $model
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function set($key, $model)
	{
		$this->cache->set($this->cacheKey($key), $model);
	}
	
	/**
	 * @param $key
	 * @return mixed
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function get($key)
	{
		return $this->cache->get($this->cacheKey($key));
	}
	
	/**
	 * 没有模型也可能有缓存，防止缓存击穿
	 * @param $key
	 * @return bool
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function has($key)
	{
		return $this->cache->has($this->cacheKey($key));
	}
	
	/**
	 * @param $key
	 * @return bool
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function del($key)
	{
		return $this->cache->delete($this->cacheKey($key));
	}
	
	/**
	 * 清空当前表的缓存
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function flush()
	{
		$this->tag->flush();
	}
	
	/**
	 * 清空当前表所在数据库的缓存
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function flushAll()
	{
		$this->tag->flushAll();
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
		return $this->cache->delete($this->cacheKey($key));
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