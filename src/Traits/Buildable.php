<?php

/**
 * Rangine model cache
 *
 * (c) We7Team 2019 <https://www.rangine.com>
 *
 * document http://s.w7.cc/index.php?c=wiki&do=view&id=317&list=2284
 *
 * visited https://www.rangine.com for more details
 */

namespace GeneaLabs\LaravelModelCaching\Traits;

use Illuminate\Pagination\Paginator;

trait Buildable {
	public function avg($column) {
		if (! $this->isCachable()) {
			return parent::avg($column);
		}

		$cacheKey = $this->makeCacheKey(['*'], null, "-avg_{$column}");

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	public function count($columns = '*') {
		if (! $this->isCachable()) {
			return parent::count($columns);
		}

		$cacheKey = $this->makeCacheKey([$columns], null, '-count');

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	/**
	 * @SuppressWarnings(PHPMD.ShortVariable)
	 */
	public function find($id, $columns = ['*']) {
		if (! $this->isCachable()) {
			return parent::find($id, $columns);
		}

		$idKey = collect($id)
			->implode('_');
		$preStr = is_array($id)
			? 'find_list'
			: 'find';
		$columns = collect($columns)->toArray();
		$cacheKey = $this->makeCacheKey($columns, null, "-{$preStr}_{$idKey}");

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	public function first($columns = ['*']) {
		if (! $this->isCachable()) {
			return parent::first($columns);
		}

		$columns = collect($columns)->toArray();
		$cacheKey = $this->makeCacheKey($columns, null, '-first');

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	public function get($columns = ['*']) {
		if (! $this->isCachable()) {
			return parent::get($columns);
		}

		$columns = collect($columns)->toArray();
		$cacheKey = $this->makeCacheKey($columns);

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	public function inRandomOrder($seed = '') {
		$this->isCachable = false;

		return parent::inRandomOrder($seed);
	}

	public function max($column) {
		if (! $this->isCachable()) {
			return parent::max($column);
		}

		$cacheKey = $this->makeCacheKey(['*'], null, "-max_{$column}");

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	public function min($column) {
		if (! $this->isCachable()) {
			return parent::min($column);
		}

		$cacheKey = $this->makeCacheKey(['*'], null, "-min_{$column}");

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	public function paginate(
		$perPage = null,
		$columns = ['*'],
		$pageName = 'page',
		$page = null
	) {
		if (! $this->isCachable()) {
			return parent::paginate($perPage, $columns, $pageName, $page);
		}

		$page = $page ?: Paginator::resolveCurrentPage($pageName);

		if (is_array($page)) {
			$page = $this->recursiveImplodeWithKey($page);
		}
		$columns = collect($columns)->toArray();
		$cacheKey = $this->makeCacheKey($columns, null, "-paginate_by_{$perPage}_{$pageName}_{$page}");

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	protected function recursiveImplodeWithKey(array $items, string $glue = '_') : string {
		$result = '';

		foreach ($items as $key => $value) {
			$result .= $glue . $key . $glue . $value;
		}

		return $result;
	}

	public function pluck($column, $key = null) {
		if (! $this->isCachable()) {
			return parent::pluck($column, $key);
		}

		$keyDifferentiator = "-pluck_{$column}" . ($key ? "_{$key}" : '');
		$cacheKey = $this->makeCacheKey([$column], null, $keyDifferentiator);

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	public function sum($column) {
		if (! $this->isCachable()) {
			return parent::sum($column);
		}

		$cacheKey = $this->makeCacheKey(['*'], null, "-sum_{$column}");

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	public function value($column) {
		if (! $this->isCachable()) {
			return parent::value($column);
		}

		$cacheKey = $this->makeCacheKey(['*'], null, "-value_{$column}");

		return $this->cachedValue(func_get_args(), $cacheKey);
	}

	public function cachedValue(array $arguments, string $cacheKey) {
		$method = debug_backtrace()[1]['function'];
		$cacheTags = $this->makeCacheTags();
		$hashedCacheKey = sha1($cacheKey);
		$result = $this->retrieveCachedValue(
			$arguments,
			$cacheKey,
			$cacheTags,
			$hashedCacheKey,
			$method
		);

		return $this->preventHashCollision(
			$result,
			$arguments,
			$cacheKey,
			$cacheTags,
			$hashedCacheKey,
			$method
		);
	}

	protected function preventHashCollision(
		array $result,
		array $arguments,
		string $cacheKey,
		array $cacheTags,
		string $hashedCacheKey,
		string $method
	) {
		if ($result['key'] === $cacheKey) {
			return $result['value'];
		}

		$this->cache()
			->tags($cacheTags)
			->forget($hashedCacheKey);

		return $this->retrieveCachedValue(
			$arguments,
			$cacheKey,
			$cacheTags,
			$hashedCacheKey,
			$method
		);
	}

	protected function retrieveCachedValue(
		array $arguments,
		string $cacheKey,
		array $cacheTags,
		string $hashedCacheKey,
		string $method
	) {
		if (property_exists($this, 'model')) {
			$this->checkCooldownAndRemoveIfExpired($this->model);
		}

		if (method_exists($this, 'getModel')) {
			$this->checkCooldownAndRemoveIfExpired($this->getModel());
		}

		return $this->cache($cacheTags)
			->rememberForever(
				$hashedCacheKey,
				function () use ($arguments, $cacheKey, $method) {
					return [
						'key' => $cacheKey,
						'value' => parent::{$method}(...$arguments),
					];
				}
			);
	}

	public function delete() {
		if (! $this->isCachable()) {
			return parent::delete();
		}

		$cacheTags = $this->makeCacheTags();
		if ($this->getConnection()->transactionLevel() > 0) {
			$this->getConnection()->transCallback[] = function () use ($cacheTags) {
				$this->cache($cacheTags)
					->flush();
			};
		} else {
			$this->cache($cacheTags)
				->flush();
		}

		return parent::delete();
	}

	public function insert(array $values) {
		if (! $this->isCachable()) {
			return parent::insert($values);
		}

		if ($this->getConnection()->transactionLevel() > 0) {
			$this->getConnection()->transCallback[] = function () {
				if (property_exists($this, 'model')) {
					$this->checkCooldownAndFlushAfterPersisting($this->model);
				}
			};
		} else {
			if (property_exists($this, 'model')) {
				$this->checkCooldownAndFlushAfterPersisting($this->model);
			}
		}

		return parent::insert($values);
	}

	public function increment($column, $amount = 1, array $extra = []) {
		if (!$this->isCachable()) {
			return parent::increment($column, $amount, $extra);
		}

		$cacheTags = $this->makeCacheTags();
		if ($this->getConnection()->transactionLevel() > 0) {
			$this->getConnection()->transCallback[] = function () use ($cacheTags) {
				$this->cache($cacheTags)
					->flush();
			};
		} else {
			$this->cache($cacheTags)
				->flush();
		}

		return parent::increment($column, $amount, $extra);
	}

	public function decrement($column, $amount = 1, array $extra = []) {
		if (! $this->isCachable()) {
			return parent::decrement($column, $amount, $extra);
		}

		$cacheTags = $this->makeCacheTags();
		if ($this->getConnection()->transactionLevel() > 0) {
			$this->getConnection()->transCallback[] = function () use ($cacheTags) {
				$this->cache($cacheTags)
					->flush();
			};
		} else {
			$this->cache($cacheTags)
				->flush();
		}

		return parent::decrement($column, $amount, $extra);
	}

	public function update(array $values) {
		if (! $this->isCachable()) {
			return parent::update($values);
		}

		if ($this->getConnection()->transactionLevel() > 0) {
			$this->getConnection()->transCallback[] = function () {
				if (property_exists($this, 'model')) {
					$this->checkCooldownAndFlushAfterPersisting($this->model);
				}
			};
		} else {
			if (property_exists($this, 'model')) {
				$this->checkCooldownAndFlushAfterPersisting($this->model);
			}
		}

		return parent::update($values);
	}

	public function forceDelete() {
		if (! $this->isCachable()) {
			return parent::forceDelete();
		}
		
		$cacheTags = $this->makeCacheTags();
		if ($this->getConnection()->transactionLevel() > 0) {
			$this->getConnection()->transCallback[] = function () use ($cacheTags) {
				$this->cache($cacheTags)
					->flush();
			};
		} else {
			$this->cache($cacheTags)
				->flush();
		}

		return parent::forceDelete();
	}
}
