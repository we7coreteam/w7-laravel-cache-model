<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/17 0017
 * Time: 10:12
 */

namespace W7\Laravel\CacheModel\Tests;


use Illuminate\Support\Facades\Cache;
use W7\Laravel\CacheModel\CacheResolver;
use W7\Laravel\CacheModel\Tag;

class TestTag extends TestCase
{
	public function setUp()
	{
		parent::setUp();
		
		CacheResolver::setCacheResolver(Cache::store());
	}
	
	/**
	 */
	public function testTag()
	{
		new Tag();
	}
}