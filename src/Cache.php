<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 13:34
 */

namespace W7\Laravel\CacheModel;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache as CacheFacade;

/**
 * Class Cache
 * @package W7\Laravel\CacheModel
 */
class Cache
{
	/**
	 * @var ModelMeta
	 */
	private $meta;
	
	/**
	 * CacheRedis constructor.
	 * @param Model $model
	 */
	public function __construct(Model $model)
	{
		$this->meta = new ModelMeta($model);
	}
	
	public function set($key, $model)
	{
		CacheFacade::tags($this->meta->dbTag(), $this->meta->tableTag())->forever($key, $model);
	}
	
	public function get($key)
	{
		return CacheFacade::tags($this->meta->dbTag(), $this->meta->tableTag())->get($key);
	}
	
	/**
	 * 没有模型也可能有缓存，防止缓存击穿
	 * @param $key
	 * @return bool
	 */
	public function has($key)
	{
		return CacheFacade::tags($this->meta->dbTag(), $this->meta->tableTag())->has($key);
	}
	
	public function del($key)
	{
		CacheFacade::tags($this->meta->dbTag(), $this->meta->tableTag())->forget($key);
	}
	
	/**
	 * 清空当前表的缓存
	 */
	public function flush()
	{
		CacheFacade::tags($this->meta->tableTag())->flush();
	}
	
	/**
	 * 清空当前表所在数据库的缓存
	 */
	public function flushAll()
	{
		CacheFacade::tags($this->meta->dbTag())->flush();
	}
	
	/**
	 * @param                $key
	 * @param \stdClass|null $model
	 */
	public function setModel($key, $model)
	{
		$model = $model ?? 'nil&null';
		
		$this->set($key, $model);
	}
	
	/**
	 * 获取缓存中键为 $key 的记录
	 * @param $key
	 * @return \stdClass|null
	 */
	public function getModel($key)
	{
		$model = $this->get($key);
		if ($model === 'nil&null') {
			$model = null;
		}
		return $model;
	}
	
	/**
	 * @param string $key
	 */
	public function delModel($key)
	{
		$this->del($key);
	}
	
	/**
	 * 缓存中是否存在主键为 key 的记录
	 * @param $key
	 * @return bool
	 */
	public function hasModel($key)
	{
		$model = $this->getModel($key);
		
		return isset($model);
	}
}