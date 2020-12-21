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

namespace W7\CacheModel\Traits;

use GeneaLabs\LaravelModelCaching\Traits\Caching;
use GeneaLabs\LaravelModelCaching\Traits\ModelCaching;
use GeneaLabs\LaravelPivotEvents\Traits\PivotEventTrait;

trait Cachable {
	use Caching;
	use ModelCaching;
	use PivotEventTrait {
		ModelCaching::newBelongsToMany insteadof PivotEventTrait;
	}
}
