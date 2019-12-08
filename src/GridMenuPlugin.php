<?php

namespace atk4\ui;

use atk4\core\Plugin;

class GridMenuPlugin extends Plugin
{
	protected $target = Grid::class;
	
	public $alias = 'menu';

	protected function activate() {
		/**
		 * @var Grid $grid
		 */
		$grid = $this->owner;
		
		$this->menu = $grid->add($grid->factory(['Menu', 'activate_on_click' => false], $this->menu, 'atk4\ui'), 'Menu');
	}
}
