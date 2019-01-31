<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 15:42
 */

namespace W7\Laravel\CacheModel\Tests;


use Illuminate\Support\Facades\DB;
use W7\Laravel\CacheModel\Tests\Models\Member;
use W7\Laravel\CacheModel\Tests\Models\MemberCount;

class TestModel extends TestCase
{
	public function testSelect()
	{
		Member::cacheFlush();
		echo 'select query run twice', PHP_EOL;
		$user = Member::query()->find(1);
		$user = Member::query()->find(2);
		echo PHP_EOL;
		
		Member::cacheFlush();
		echo 'select query run twice', PHP_EOL;
		$user = Member::query()->find([1, 5], ['uid', 'username']);
		$user = Member::query()->find([1, 5], ['uid', 'username']);
		echo PHP_EOL;
		
		Member::cacheFlush();
		echo 'select query run once', PHP_EOL;
		$user = Member::query()->find([1, 5]);
		$user = Member::query()->find([1, 5]);
		echo PHP_EOL;
		
		Member::cacheFlush();
		echo 'select query run twice', PHP_EOL;
		$user = Member::query()->find([1, 2, 3, 4, 5], ['uid'])->keyBy('uid');
		$user = Member::query()->find([1, 2, 3, 4, 5], ['uid'])->keyBy('uid');
		echo PHP_EOL;
		
		Member::cacheFlush();
		echo 'select query run once', PHP_EOL;
		$user = Member::query()->find([1, 2, 3, 4, 5])->keyBy('uid');
		$user = Member::query()->find([1, 2, 3, 4, 5])->keyBy('uid');
		echo PHP_EOL;
		
		Member::cacheFlush();
		echo 'select query run 3 times', PHP_EOL;
		$user = Member::query()->find([1, 2, 3, 4, 5], ['uid'])->keyBy('uid');
		$user = Member::query()->find([1, 2, 3, 4, 5], ['uid'])->keyBy('uid');
		$user = Member::query()->find([1, 2, 3, 4, 5])->keyBy('uid');
		$user = Member::query()->find([1, 2, 3, 4, 5])->keyBy('uid');
		$this->assertTrue(true);
	}
	
	public function testSelectModelExist()
	{
		$uid = 1;
		
		Member::cacheDeleteModel($uid);
		echo "model exist", PHP_EOL;
		echo "member find {$uid}", PHP_EOL;
		
		$user = Member::query()->find($uid);
		$this->assertTrue(!empty($user));
		if (Member::getDefaultNeedCache()) {
			$this->assertTrue(Member::cacheHasKey($uid));
			$this->assertTrue(Member::cacheHasModel($uid));
		} else {
			$this->assertFalse(Member::cacheHasKey($uid));
			$this->assertFalse(Member::cacheHasModel($uid));
		}
		
		echo "member find {$uid} again", PHP_EOL;
		
		$user = Member::query()->find($uid);
		if (Member::getDefaultNeedCache()) {
			$this->assertTrue(!empty($user));
			$this->assertTrue(Member::cacheHasKey($uid));
			$this->assertTrue(Member::cacheHasModel($uid));
		}
	}
	
	public function testSelectModelNotExist()
	{
		$uid = 250049;
		
		Member::cacheDeleteModel($uid);
		echo "model not exist", PHP_EOL;
		echo "member find {$uid} ", PHP_EOL;
		
		$user = Member::query()->find($uid);
		$this->assertTrue(empty($user));
		if (Member::getDefaultNeedCache()) {
			$this->assertTrue(Member::cacheHasKey($uid));
			$this->assertFalse(Member::cacheHasModel($uid));
			
			
		} else {
			$this->assertFalse(Member::cacheHasKey($uid));
			$this->assertFalse(Member::cacheHasModel($uid));
		}
		
		echo "member find {$uid} again", PHP_EOL;
		
		$user = Member::query()->find($uid);
		if (Member::getDefaultNeedCache()) {
			$this->assertTrue(empty($user));
			$this->assertTrue(Member::cacheHasKey($uid));
			$this->assertFalse(Member::cacheHasModel($uid));
		}
	}
	
	public function testUpdate()
	{
		$uid = 1;
		
		/**
		 * @var $user Member
		 */
		$user = Member::query()->find($uid);
		$this->assertTrue(!empty($user));
		$this->assertTrue(Member::cacheHasKey($uid));
		$this->assertTrue(Member::cacheHasModel($uid));
		
		$user->invite_code = rand(1, 100000);
		$user->save();
		$this->assertFalse(Member::cacheHasKey($uid));
		$this->assertFalse(Member::cacheHasModel($uid));
	}
	
	public function testCreate()
	{
		$uid = 250050;
		DB::table('members')->where('uid', $uid)->delete();
		
		$user = Member::query()->find($uid);
		$this->assertTrue(Member::cacheHasKey($uid));
		$this->assertFalse(Member::cacheHasModel($uid));
		
		Member::query()->forceCreate([
			'uid'      => 250050,
			'username' => 'cache_model',
			'password' => str_random(8),
			'salt'     => str_random(6),
			'encrypt'  => str_random(8),
		]);
		$this->assertFalse(Member::cacheHasKey($uid));
		$this->assertFalse(Member::cacheHasModel($uid));
	}
	
	public function testInsertGetId()
	{
		$uid = 250050;
		
		$user = Member::query()->find($uid);
		if (!empty($user)) {
			$user->delete();
		}
		// Member::cacheDelete($uid);
		
		$value = [
			'uid'      => $uid,
			'username' => 'cache_model',
			'password' => str_random(8),
			'salt'     => str_random(6),
			'encrypt'  => str_random(8),
		];
		$id    = Member::query()->insertGetId($value);
		jd('iiid', $id);
	}
	
	private function createUser()
	{
		$user = Member::query()->forceCreate([
			'uid'      => 250050,
			'username' => 'cache_model',
			'password' => str_random(8),
			'salt'     => str_random(6),
			'encrypt'  => str_random(8),
		]);
		
		return $user;
	}
	
	
	public function testInsert()
	{
		$user = Member::query()->newModelInstance([
			'uid'      => 250050,
			'username' => 'cache_model',
			'password' => str_random(8),
			'salt'     => str_random(6),
			'encrypt'  => str_random(8),
		]);
		$user->save();
	}
	
	public function testLL()
	{
		ll('a', 'b');
	}
	
	public function testAll()
	{
		$uid = 250050;
		
		Member::cacheResolver();
		Member::cacheDeleteModel($uid);
		$user = DB::table('members')->where('uid', $uid)->first();
		if (!empty($user)) {
			try {
				$user->delete();
			} catch (\Exception $e) {
				jd($e->getMessage());
			}
		}
		
		echo 'delete ', $uid, PHP_EOL;
		
		//
		$user = Member::query()->find($uid);
		$this->assertNull($user, '用户' . $uid . '不应该存在');
		$this->assertFalse(Member::cacheHasModel($uid));
		$this->assertTrue(Member::cacheHasKey($uid));
		
		// jd($user, Member::cacheHasModel($uid), Member::cacheHasKey($uid));
		
		$user = Member::query()->newModelInstance([
			'uid'      => 250050,
			'username' => 'cache_model',
			'password' => str_random(8),
			'salt'     => str_random(6),
			'encrypt'  => str_random(8),
		]);
		$user->save();
		
		$this->assertFalse(Member::cacheHasModel($uid));
		$this->assertFalse(Member::cacheHasKey($uid));
		
		$user = Member::query()->find($uid);
		$this->assertTrue(!empty($user));
		$this->assertTrue(Member::cacheHasModel($uid));
		$this->assertTrue(Member::cacheHasKey($uid));
		
		$user->invite_code = str_random(8);
		$user->save();
		$this->assertFalse(Member::cacheHasModel($uid));
		$this->assertFalse(Member::cacheHasKey($uid));
		
		$user = Member::query()->find($uid);
		$this->assertTrue(!empty($user));
		$this->assertTrue(Member::cacheHasModel($uid));
		
		$user->delete();
		$this->assertFalse(Member::cacheHasModel($uid));
		$this->assertFalse(Member::cacheHasKey($uid));
	}
	
	public function testWith()
	{
		// Member::cacheFlushAll();
		$uid  = 1;
		$user = Member::query()->with(['apps'])->find($uid);
	}
	
	public function testFlush()
	{
		$user = Member::query()->with(['memberCount'])->find(1);
		
		MemberCount::cacheFlush();
		
		echo 'select query once', PHP_EOL;
		
		$user        = Member::query()->find(1);
		$memberCount = MemberCount::query()->find(1);
	}
	
	public function testFlushAll()
	{
		Member::query()->find(1);
		MemberCount::query()->find(1);
		
		MemberCount::cacheFlushAll();
		
		echo 'select query twice', PHP_EOL;
		
		Member::query()->find(1);
		MemberCount::query()->find(1);
		
		Member::query()->find(1);
		MemberCount::query()->find(1);
	}
}