<?php

/**
 * Rangine Model Cache
 *
 * (c) We7Team 2019 <https://www.rangine.com/>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com/ for more details
 */

namespace W7\CacheModel;

trait Buildable {
	private $transCallback = [];

	use \GeneaLabs\LaravelModelCaching\Traits\Buildable;

	public function delete() {
		if ($this->getConnection()->transactionLevel() > 0) {
			$this->transCallback[] = function () {
				$this->cache($this->makeCacheTags())
					->flush();
			};
		} else {
			$this->cache($this->makeCacheTags())
				->flush();
		}

		return parent::delete();
	}

	public function insert(array $values) {
		if ($this->getConnection()->transactionLevel() > 0) {
			$this->transCallback[] = function () {
				$this->checkCooldownAndFlushAfterPersisting($this->model);
			};
		} else {
			$this->checkCooldownAndFlushAfterPersisting($this->model);
		}

		return parent::insert($values);
	}

	public function increment($column, $amount = 1, array $extra = []) {
		if ($this->getConnection()->transactionLevel() > 0) {
			$this->transCallback[] = function () {
				$this->cache($this->makeCacheTags())
					->flush();
			};
		} else {
			$this->cache($this->makeCacheTags())
				->flush();
		}

		return parent::decrement($column, $amount, $extra);
	}

	public function decrement($column, $amount = 1, array $extra = []) {
		if ($this->getConnection()->transactionLevel() > 0) {
			$this->transCallback[] = function () {
				$this->cache($this->makeCacheTags())
					->flush();
			};
		} else {
			$this->cache($this->makeCacheTags())
				->flush();
		}

		return parent::decrement($column, $amount, $extra);
	}

	public function update(array $values) {
		if ($this->getConnection()->transactionLevel() > 0) {
			$this->transCallback[] = function () {
				$this->checkCooldownAndFlushAfterPersisting($this->model);
			};
		} else {
			$this->checkCooldownAndFlushAfterPersisting($this->model);
		}

		return parent::update($values);
	}

	public function forceDelete() {
		if ($this->getConnection()->transactionLevel() > 0) {
			$this->transCallback[] = function () {
				$this->cache($this->makeCacheTags())
					->flush();
			};
		} else {
			$this->cache($this->makeCacheTags())
				->flush();
		}

		return parent::forceDelete();
	}
}
