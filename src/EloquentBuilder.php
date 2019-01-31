<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 11:10
 */

namespace W7\Laravel\CacheModel;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class EloquentBuilder extends Builder
{
	public function __construct(QueryBuilder $query)
	{
		parent::__construct($query);
	}
}