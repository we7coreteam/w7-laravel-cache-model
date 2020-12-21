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

namespace W7\CacheModel;

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use W7\Contract\Cache\CacheFactoryInterface;
use W7\Core\Provider\ProviderAbstract;
use Illuminate\Config\Repository;
use W7\CacheModel\Store\RedisStore;

class CacheModelProvider extends ProviderAbstract {
	public function register() {
		$this->publishConfig('model-cache.php');
		$this->registerConfig('model-cache.php', 'model-cache');
		$this->registerCommand();

		$this->container->set('model-cache-store', function () {
			$config = $this->config->get('model-cache', []);
			return new RedisStore(
				$this->container->singleton(CacheFactoryInterface::class),
				$config['cache-prefix'],
				$config['channel']
			);
		});

		Container::getInstance()->singleton('config', function () {
			$config = $this->config->get('model-cache', []);
			$config['store'] = 'icache';

			return new Repository([
			'laravel-model-caching' => $config,
			'cache.stores.icache' => [
				'driver' => 'icache'
			]]);
		});
		Container::getInstance()->singleton('db', function () {
			return $this->getContainer()->singleton('db-factory');
		});
		Container::getInstance()->singleton('cache', function ($app) {
			return new CacheManager($app);
		});
	}

	public function boot() {
		$container = $this->container;
		Container::getInstance()->make('cache')->extend('icache', function ($app) use ($container) {
			return Container::getInstance()->make('cache')->repository(
				$container->singleton('model-cache-store')
			);
		});

		//放在boot的原因是在boot执行的时候,数据库需要的条件才会准备好
		$this->registerListener();
	}

	private function registerListener() {
		//处理在事物中使用缓存问题
		Model::getEventDispatcher()->listen(TransactionCommitted::class, function (TransactionCommitted $instance) {
			if ($instance->connection->transactionLevel() !== 0 || empty($instance->connection->transCallback)) {
				return false;
			}

			$callbacks = $instance->connection->transCallback;
			$instance->connection->transCallback = [];
			foreach ((array)$callbacks as $callback) {
				is_callable($callback) && $callback();
			}
		});
		Model::getEventDispatcher()->listen(TransactionRolledBack::class, function (TransactionRolledBack $instance) {
			if ($instance->connection->transactionLevel() !== 0) {
				return false;
			}

			$instance->connection->transCallback = [];
		});
	}
}
