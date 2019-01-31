<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/30
 * Time: 10:20
 */

namespace W7\Laravel\CacheModel;


use Illuminate\Database\Eloquent\Model;

/**
 * 获取 cache 的键或标签
 * Class CacheModelMeta
 * @package W7\Laravel\Database\Eloquent\Cache
 */
class ModelMeta
{
	private $table;
	
	private $db;
	
	public function __construct(Model $model)
	{
		$this->table = $model->getTable();
		$this->db    = $model->getConnectionName() ?: 'default';
	}
	
	public function dbTag()
	{
		return $this->db;
	}
	
	public function tableTag()
	{
		return $this->db . ':' . $this->table;
	}
}