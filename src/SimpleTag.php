<?php
/**
 * Created by PhpStorm.
 * User: gorden
 * Date: 19-2-15
 * Time: 下午2:54
 */

namespace W7\Laravel\CacheModel;


class SimpleTag
{
	private $db;
	private $table;
	
	public function __construct($db, $table)
	{
		$this->db    = $db;
		$this->table = $table;
	}
	
	public function db()
	{
		return $this->db;
	}
	
	public function table()
	{
		return $this->db . ':' . $this->table;
	}
	
	public function dbHash()
	{
		return $this->hash($this->db());
	}
	
	public function tableHash($dbHash)
	{
		return $this->hash($dbHash . ':' . $this->table());
	}
	
	public function hash($content)
	{
		return md5(uniqid($content));
	}
}