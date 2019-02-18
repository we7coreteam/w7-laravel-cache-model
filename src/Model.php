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
use W7\Laravel\CacheModel\Caches\Tag;

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
	
	/**
	 * 是否启用缓存
	 * @return bool
	 */
	public function needCache()
	{
		return $this->useCache;
	}
	
	public function getCacheModelNamespace()
	{
		return ($this->getConnectionName() ?: 'default') . ':' . $this->getTable();
	}
	
	public static function flush()
	{
		Tag::flush((new static())->getCacheModelNamespace());
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