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
	 * @throws \W7\Laravel\CacheModel\Exceptions\InvalidArgumentException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function testCacheResolver()
	{
		$cache = new CacheResolver();
//		$cache->set('a', 'aa');
//		$cache->set('b', 'bb');
		
		$cache->clear();
		
		dd($cache->getMultiple(['a', 'b']));
	}
	
	/**
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function testTag()
	{
		$tag = new Tag('', 'settings');
		
		$this->assertEquals('model_cache:default', $tag->getDBKey());
		$this->assertEquals('model_cache:default:settings', $tag->getTableKey());
		
		$dbValue  = $tag->getDBValue();
		$dbValue2 = $tag->getDBValue();
		$this->assertEquals($dbValue, $dbValue2);
		
		$tableValue  = $tag->getTableValue();
		$tableValue2 = $tag->getTableValue();
		$this->assertEquals($tableValue, $tableValue2);
		
		$tag->flushTableValue();
		$dbValue3    = $tag->getDBValue();
		$tableValue3 = $tag->getDBValue();
		$this->assertEquals($dbValue3, $dbValue2);
		$this->assertNotEquals($tableValue3, $tableValue2);
		
		$tag->flushDBValue();
		$dbValue4    = $tag->getDBValue();
		$tableValue4 = $tag->getTableValue();
		$this->assertNotEquals($dbValue4, $dbValue3);
		$this->assertNotEquals($tableValue4, $tableValue3);
	}
}