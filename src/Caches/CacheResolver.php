<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/17 0017
 * Time: 11:12
 */

namespace W7\Laravel\CacheModel\Caches;


use Psr\SimpleCache\CacheInterface;
use W7\Laravel\CacheModel\Exceptions\InvalidArgumentException;


/**
 * 缓存代理类，必须为代理类设置 CacheInterface
 * @package W7\Laravel\CacheModel
 */
class CacheResolver
{
	/**
	 * @var CacheInterface
	 */
	private static $cacheSingleton;
	
	/**
	 * @param CacheInterface $cache
	 */
	public static function setCacheResolver(CacheInterface $cache)
	{
		static::$cacheSingleton = $cache;
	}
	
	/**
	 * @return CacheInterface
	 * @throws InvalidArgumentException
	 */
	public static function getCacheResolver()
	{
		if (!static::$cacheSingleton instanceof CacheInterface) {
			throw new InvalidArgumentException('使用 Model Cache 必须先调用 \W7\Laravel\CacheModel\CacheResolver::setCacheResolver($cache)');
		}
		return static::$cacheSingleton;
	}
}