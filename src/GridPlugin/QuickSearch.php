<?php

namespace atk4\ui\GridPlugin;

use atk4\core\Plugin;

class QuickSearch extends Plugin
{
    public $passive = true;
    
    public $short_name = 'quickSearch';
    
    public $fields = [];
    
	public $autoQuery = false;
	
	protected $jsSearch;
	
	public function init()
	{
	    parent::init();

	    $grid = $this->owner;
	    
	    if (!$grid->model) {
	        throw new \Exception(['Call setModel() before adding quick search addon']);
	    }
	    
	    if (! $grid->hasElement('menu')) return;

		$container = $grid->getElement('menu')->addMenuRight()->addItem()->setElement('div')->add('View');
		
		$this->jsSearch = $container->add(['jsSearch', 'reload' => $grid->container, 'autoQuery' => $this->autoQuery]);
		
		$this->search();
	}
	
	public function setUrlArgs($args)
	{
		foreach ($args as $key => $value) {
		    $this->owner->container->js(true, $this->jsSearch->js()->atkJsSearch('setUrlArgs', [$key, $value]));
		}
	}
	
	public function setActivationMethods($grid)
	{
	    $grid->addMethod('addQuickSearch', function($grid, $fields = [], $autoQuery = false) {
	        return $grid->add([static::class, 'fields' => $fields, 'autoQuery' => $autoQuery]);
	    });
	}
	
	public function search()
	{
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
