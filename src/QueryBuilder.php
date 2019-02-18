<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/28
 * Time: 16:36
 */

namespace W7\Laravel\CacheModel;

use Illuminate\Database\Query\Builder as DatabaseQueryBuilder;
use W7\Laravel\CacheModel\Caches\Cache;
use W7\Laravel\CacheModel\Caches\CacheFactory;
use W7\Laravel\CacheModel\Caches\Tag;
use W7\Laravel\CacheModel\Exceptions\CacheKeyNotExistsException;
use W7\Laravel\CacheModel\Exceptions\InvalidArgumentException;

class QueryBuilder extends DatabaseQueryBuilder
{
	/**
	 * @var Model
	 */
	private $cacheModel;
	
	/**
	 * @param Model $cacheModel
	 */
	public function setCacheModel($cacheModel)
	{
		$this->cacheModel = $cacheModel;
	}
	
	/**
	 * @return Model
	 */
	public function getCacheModel()
	{
		return $this->cacheModel;
	}
	
	public function needCache()
	{
		if (!empty($this->cacheModel)) {
			return $this->getCacheModel()->needCache();
		}
		return false;
	}
	
	/**
	 * @return Cache
	 * @throws InvalidArgumentException
	 */
	public function getCacheResolver()
	{
		$namespace = $this->getCacheModel()->getCacheModelNamespace();
		
		return CacheFactory::getInstance($namespace);
	}
	
	/**
	 * @param $key
	 * @param $model
	 * @throws InvalidArgumentException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function cacheSetModel($key, $model)
	{
		$key = $this->getCacheKey($key);
		
		$this->getCacheResolver()->setModel($key, $model);
	}
	
	/**
	 * @param $key
	 * @return \stdClass|null
	 * @throws InvalidArgumentException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function cacheGetModel($key)
	{
		$key = $this->getCacheKey($key);
		
		return $this->getCacheResolver()->getModel($key);
	}
	
	/**
	 * @param $key
	 * @return \stdClass|null
	 * @throws InvalidArgumentException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function cacheDelModel($key)
	{
		$key = $this->getCacheKey($key);
		
		return $this->getCacheResolver()->getModel($key);
	}
	
	/**
	 * @param $key
	 * @return bool
	 * @throws InvalidArgumentException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function cacheHasModel($key)
	{
		$key = $this->getCacheKey($key);
		
		return $this->getCacheResolver()->hasModel($key);
	}
	
	/**
	 * 获取缓存的键
	 * @param $key
	 * @return string
	 */
	public function getCacheKey($key)
	{
		return Tag::getCacheKey($key, $this->getCacheModel()->getCacheModelNamespace());
	}
	
	/**
	 * @param array $values
	 * @param null  $sequence
	 * @return int|void
	 * @throws InvalidArgumentException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function insertGetId(array $values, $sequence = null)
	{
		try {
			$id = parent::insertGetId($values, $sequence);
		} finally {
			if ($this->needCache() && !empty($id)) {
				$this->cacheDelModel($id);
			}
		}
	}
	
	/**
	 * @param null $id
	 * @return int
	 * @throws InvalidArgumentException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function delete($id = null)
	{
		try {
			return parent::delete($id);
		} finally {
			if ($this->getCacheModel()->exists && $this->needCache()) {
				$pk = $id ?? $this->getCacheModel()->getKey();
				$this->getCacheResolver()->delModel($this->getCacheKey($pk));
			}
		}
	}
	
	/**
	 * @param array $values
	 * @return int
	 * @throws InvalidArgumentException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function update(array $values)
	{
		try {
			return parent::update($values);
		} finally {
			if ($this->getCacheModel()->exists && $this->needCache()) {
				$pk = $this->cacheModel->getKey();
				$this->getCacheResolver()->delModel($this->getCacheKey($pk));
			}
		}
	}
	
	/**
	 * 是否未定义列名
	 * @return bool
	 */
	protected function isColumnsUndefined()
	{
		return $this->columns == ['*'];
	}
	
	/**
	 * Model::query()->find($id);
	 * @return bool
	 */
	protected function isFindOneQuery()
	{
		if (count($this->wheres) == 1 && ($current = current($this->wheres))) {
			return $current['type'] == 'Basic'
				&& $current['column'] == $this->cacheModel->getQualifiedKeyName()
				&& $current['operator'] == '='
				// && !empty($current['value'])
				&& $current['boolean'] == 'and';
		}
		return false;
	}
	
	/**
	 * Model::query()->find($ids);
	 * @return bool
	 */
	protected function isFindManyQuery()
	{
		if (count($this->wheres) == 1 && ($current = current($this->wheres))) {
			return $current['type'] == 'In'
				&& $current['column'] == $this->cacheModel->getQualifiedKeyName()
				&& is_array($current['values'])
				&& $current['boolean'] == 'and';
		}
		return false;
	}
	
	/**
	 * 获取查询主键
	 *
	 * @return \Illuminate\Support\Collection
	 */
	protected function getFindQueryPrimaryKeyValues()
	{
		$current = current($this->wheres);
		if ($this->isFindOneQuery()) {
			$ids = [$current['value']];
		} else {
			$ids = $current['values'];
		}
		return collect($ids);
	}
	
	protected function isFindQuery()
	{
		return $this->isFindOneQuery() || $this->isFindManyQuery();
	}
	
	protected function cacheFindFirst()
	{
		return $this->isFindQuery()
			&& $this->isColumnsUndefined()
			&& $this->needCache();
	}
	
	/**
	 * @return array
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	protected function runSelect()
	{
		$cacheFindFirst = $this->cacheFindFirst();
		if ($cacheFindFirst) {
			
			$ids = $this->getFindQueryPrimaryKeyValues();
			
			try {
				$ids->each(function ($id) {
					if (!$this->getCacheResolver()->has($this->getCacheKey($id))) {
						throw new CacheKeyNotExistsException('cache missing');
					}
				});
				
				$rows = $ids->map(function ($id) {
					return $this->getCacheResolver()->getModel($this->getCacheKey($id));
				})->filter(function ($row) {
					return !empty($row);
				});
				
				if ($rows->count() > 0) {
					return $rows->toArray();
				} else {
					return null;
				}
			} catch (CacheKeyNotExistsException $e) {
				// 防止缓存击穿
				// jd($e->getMessage());
			}
		}
		
		$rows = $this->connection->select(
			$this->toSql(), $this->getBindings(), !$this->useWritePdo
		);
		
		if ($cacheFindFirst) {
			$primaryKey = $this->getCacheModel()->getKeyName();
			foreach ($rows as $row) {
				$this->getCacheResolver()->setModel($this->getCacheKey($row->{$primaryKey}), $row);
			}
			if (!empty($ids)) {
				$ids->each(function ($id) {
					if (!$this->getCacheResolver()->has($this->getCacheKey($id))) {
						$this->getCacheResolver()->setModel($this->getCacheKey($id), null); // 防止缓存击穿
					}
				});
			}
		}
		
		return $rows;
	}
}