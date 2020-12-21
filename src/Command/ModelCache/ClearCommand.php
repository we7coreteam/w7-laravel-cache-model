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

namespace W7\CacheModel\Command\ModelCache;

use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;
use W7\Console\Command\CommandAbstract;

class ClearCommand extends CommandAbstract {
	protected $description = 'Flush cache for a given model. If no model is given, entire model-cache is flushed.';

	protected function configure() {
		$this->addOption('--model', null, InputOption::VALUE_REQUIRED, 'model full name');
	}

	protected function handle($options) {
		$option = $options['model'];

		if (!$option) {
			if ($this->output->confirm('clear all model cache?')) {
				return $this->flushEntireCache();
			}
			$this->output->error("option model Can't be empty");
			return false;
		}

		return $this->flushModelCache($option);
	}

	protected function flushEntireCache() : int {
		$config = Container::getInstance()
			->make('config')
			->get('laravel-model-caching.store');

		Container::getInstance()
			->make('cache')
			->store($config)
			->flush();

		$this->output->info('✔︎ Entire model cache has been flushed.');

		return 0;
	}

	protected function flushModelCache(string $option) : int {
		$model = new $option;
		$usesCachableTrait = $this->getAllTraitsUsedByClass($option)
			->contains("GeneaLabs\LaravelModelCaching\Traits\Cachable");

		if (! $usesCachableTrait) {
			$this->output->error("'{$option}' is not an instance of CachedModel.");
			$this->output->warning('Only CachedModel instances can be flushed.');

			return 1;
		}

		$model->flushCache();
		$this->output->info("✔︎ Cache for model '{$option}' has been flushed.");

		return 0;
	}

	/** @SuppressWarnings(PHPMD.BooleanArgumentFlag) */
	protected function getAllTraitsUsedByClass(
		string $classname,
		bool $autoload = true
	) : Collection {
		$traits = collect();

		if (class_exists($classname, $autoload)) {
			$traits = collect(class_uses($classname, $autoload));
		}

		$parentClass = get_parent_class($classname);

		if ($parentClass) {
			$traits = $traits
				->merge($this->getAllTraitsUsedByClass($parentClass, $autoload));
		}

		return $traits;
	}
}
