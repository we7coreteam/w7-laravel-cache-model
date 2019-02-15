<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/28
 * Time: 16:33
 */

namespace W7\Laravel\CacheModel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * Class Model
 * @package W7\Laravel\CacheModel
 */
abstract class Model extends BaseModel
{
	/**
	 * 是否使用缓存
	 * @var bool
	 */
	protected $useCache = true;
	
	public function simpleTag()
	{
		return new SimpleTag($this->getConnectionName() ?? 'default', $this->table);
	}
	
	/**
	 * @return bool
	 */
	public static function getDefaultNeedCache()
	{
		return (new static())->needCache();
	}
	
	/**
	 * 是否启用缓存
	 * @return bool
	 */
	public function needCache()
	{
		return $this->useCache;
	}
	
	/**
	 * 清空当前表所在数据库的所有缓存
	 */
	public static function cacheFlushAll()
	{
		static::cacheResolver()->flushAll();
	}
	
	/**
	 * 清空当前表的所有缓存
	 */
	public static function cacheFlush()
	{
		static::cacheResolver()->flush();
	}
	
	/**
	 * 清空当前表指定键的缓存
	 * @param $key
	 */
	public static function cacheDeleteModel($key)
	{
		static::cacheResolver()->delModel($key);
	}
	
	/**
	 * 设置当前表主键缓存
	 * @param $key
	 * @param $value
	 */
	public static function cacheSetModel($key, $value)
	{
		static::cacheResolver()->setModel($key, $value);
	}
	
	/**
	 * @param $key
	 * @return bool
	 */
	public static function cacheHasModel($key)
	{
		return static::cacheResolver()->hasModel($key);
	}
	
	/**
	 * @param $key
	 * @return mixed|Model|Model
	 */
	public static function cacheGetModel($key)
	{
		return static::cacheResolver()->getModel($key);
	}
	
	/**
	 * 有缓存记录，未必是缓存对象
	 * @param $key
	 * @return bool
	 */
	public static function cacheHasKey($key)
	{
		return static::cacheResolver()->has($key);
	}
	
	/**
	 * @return SimpleCache
	 */
	public static function cacheResolver()
	{
		static $cacheResolver;
		if (empty($cacheResolver)) {
			$tag = (new static())->simpleTag();
			
			$cacheResolver = new SimpleCache();
			$cacheResolver->setSimpleTag($tag);
		}
		return $cacheResolver;
	}
	
	private static $cacheInterface;
	
	public static function setCacheInterface($cacheInterface)
	{
		self::$cacheInterface = $cacheInterface;
	}
	
	/**
	 * @return SimpleCache
	 */
	public function getCacheResolver()
	{
		return static::cacheResolver();
	}
	
	/**
	 * @param \Illuminate\Database\Query\Builder $query
	 * @return Builder|EloquentBuilder|static
	 */
	// public function newEloquentBuilder($query)
	// {
	// 	$builder = new EloquentBuilder($query);
	//
	// 	return $builder;
	// }
	
	/**
	 * @return \Illuminate\Database\Query\Builder|QueryBuilder
	 */
	protected function newBaseQueryBuilder()
	{
		$connection = $this->getConnection();
		$grammar    = $connection->getQueryGrammar();
		$processor  = $connection->getPostProcessor();
		
		$queryBuilder = new QueryBuilder($connection, $grammar, $processor);
		$queryBuilder->setCacheModel($this);
		
		return $queryBuilder;
	}
}