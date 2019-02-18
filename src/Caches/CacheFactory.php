<?php
/**
 * Created by PhpStorm.
 * User: gorden
 * Date: 19-2-18
 * Time: 上午11:50
 */

namespace W7\Laravel\CacheModel\Caches;


class CacheFactory
{
	/**
	 * @var array
	 */
	private static $instances = [];
	
	/**
	 * @param $id
	 * @return Cache
	 * @throws \W7\Laravel\CacheModel\Exceptions\InvalidArgumentException
	 */
	public static function getInstance($id)
	{
		$cache = array_get(static::$instances, $id);
		if (empty($cache)) {
			static::$instances[$id] = $cache = new Cache();
		}
		return $cache;
	}
}