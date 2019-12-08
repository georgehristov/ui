<?php

namespace atk4\ui;

use atk4\core\Plugin;

class GridQuickSearchPlugin extends Plugin
{
	protected $target = Grid::class;
	
	protected $require = GridMenuPlugin::class;
	
	public $alias = 'quickSearch';
	
	public $fields = [];
	public $hasAutoQuery = false;
	
	protected function activate() {
		/**
		 * @var Grid $grid
		 */
		$grid = $this->owner;
		
		if (!$grid->model) {
			throw new Exception(['Call setModel() before adding quick search plugin']);
		}

		$this->container = $view = $grid->getPluginSeed('menu')->addMenuRight()->addItem()->setElement('div')->add('View');
		
		$this->quickSearch = $view->add(['jsSearch', 'reload' => $grid->container, 'autoQuery' => $this->hasAutoQuery]);
		
		$this->search();
	}
	
	public function setUrlArgs($args) {
		foreach ($args as $key => $value) {
			$this->owner->container->js(true, $this->quickSearch->js()->atkJsSearch('setUrlArgs', [$key, $value]));
		}
	}
	
	public function search() {
		$grid = $this->owner;
		
		if (! $q = $grid->stickyGet('_q')) return;
		
		$fields = $this->fields?: [$grid->model->title_field];
		$conditions = [];
		foreach ($fields as $field) {
			$conditions[] = [$field, 'like', '%'.$q.'%'];
		}
			
		$grid->model->addCondition($conditions);
	}
}
