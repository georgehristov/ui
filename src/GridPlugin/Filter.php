<?php

namespace atk4\ui\GridPlugin;

use atk4\ui\Filter as BaseFilter;

/**
 * Filter class to use with Grid
 */
class Filter extends BaseFilter
{
	public $region = 'Menu';
	
	public function init() {
	    parent::init();
	    
	    $this->reload = $this->reload ?: $this->owner->table->reload;
	}
}
