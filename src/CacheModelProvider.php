<?php

/**
 * Rangine Model Cache
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Laravel\CacheModel;

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use W7\Core\Provider\ProviderAbstract;
use Illuminate\Config\Repository;
use W7\Laravel\CacheModel\Store\CacheStore;

class CacheModelProvider extends ProviderAbstract {
	public function register() {
		$this->publishConfig('model-cache.php');
		$this->registerConfig('model-cache.php', 'model-cache');
		$this->registerCommand('model:cache');

		Container::getInstance()->singleton('config', function () {
			$config = $this->config->getUserConfig('model-cache');
			$config['store'] = 'icache';

			return new Repository([
			'laravel-model-caching' => $config,
			'cache.stores.icache' => [
				'driver' => 'icache'
			]]);
		});
		Container::getInstance()->singleton('db', function () {
			return idb();
		});
		Container::getInstance()->singleton('cache', function ($app) {
			return new CacheManager($app);
		});
	}

	public function boot() {
		$config = $this->config->getUserConfig('model-cache');
		Container::getInstance()->make('cache')->extend('icache', function ($app) use ($config) {
			return Container::getInstance()->make('cache')->repository(
				new CacheStore(
					$config['cache-prefix'],
					$config['channel']
				)
			);
		});
	}
}
