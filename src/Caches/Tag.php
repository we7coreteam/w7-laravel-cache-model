<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/17 0017
 * Time: 12:46
 */

namespace W7\Laravel\CacheModel\Caches;

/**
 * 实现数据库缓存命名空间
 * Class Tag
 * @package W7\Laravel\CacheModel
 */
class Tag
{
	const PREFIX = 'we7_model_cache';
	
	/**
	 * @param string $namespace
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public static function flush($namespace)
	{
		Cache::singleton()->del(static::getRootNamespace($namespace));
	}
	
	private static function getRootNamespace($namespace)
	{
		return join(':', [static::PREFIX, $namespace]);
	}
	
	/**
	 * 获取缓存的键值
	 * @param $key
	 * @param $namespace
	 * @return string
	 */
	public static function getCacheKey($key, $namespace)
	{
		$namespace = static::getRootNamespace($namespace);
		
		$pieces = explode(':', $namespace);
		
		return static::getPrefix($pieces) . ':' . $key;
	}
	
	public static function getPrefix($pieces)
	{
		$cache = Cache::singleton();
		
		$length = count($pieces);
		for ($i = 0; $i < $length; $i++) {
			if ($i == 0) {
				$key   = Tag::joinPieces($pieces, 1);
				$value = $cache->get($key);
				if (empty($value)) {
					$value = static::hash($key);
					$cache->set($key, $value);
				}
			} else {
				$key   = Tag::joinPieces($pieces, $i + 1);
				$value = $cache->get($key);
				if (empty($value)) {
					// 'a'
					$parent_key = Tag::joinPieces($pieces, $i);
					// 'a' => value
					$parent_value = $cache->get($parent_key);
					// 'b'
					$suffix = $pieces[$i];
					
					$value = static::hash($parent_value . ':' . $suffix);
					$cache->set($key, $value);
				}
			}
		}
		
		return $cache->get(join(':', $pieces));
	}
	
	/**
	 * 前 n 个元素用 ':' 拼接
	 * @param $pieces
	 * @param $n
	 * @return string
	 */
	private static function joinPieces($pieces, $n)
	{
		$length = count($pieces);
		$array  = [];
		for ($i = 0; $i < $n && $n <= $length && $i < $n; $i++) {
			$array[] = $pieces[$i];
		}
		return join(':', $array);
	}
	
	private static function hash($content)
	{
		return md5(uniqid($content . str_random(8)));
	}
}