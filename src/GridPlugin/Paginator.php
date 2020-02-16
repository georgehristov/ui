<?php

namespace atk4\ui\GridPlugin;

use atk4\core\Plugin;

class Paginator extends Plugin
{
	public $short_name = 'paginator';
	
	public $ipp = 1;
	
	public function init() 
	{
	    parent::init();
	    
		/**
		 * @var \atk4\ui\Grid $grid
		 */
		$grid = $this->owner;
		
		$this->container = $grid->container->add(['View'], 'Paginator')->addStyle('text-align', 'center');
		$this->paginator = $this->container->add($grid->factory(['Paginator', 'reload' => $grid->container], $this->paginator, 'atk4\ui'));
		$grid->stickyGet($this->paginator->name);
		
		$grid->addHook('beforeRecursiveRender', function($grid) {
			$this->setModelLimitFromPaginator();
		});
	}
	
	public function setControlMethods() {
	    $grid->addMethod('setIpp', function($grid, $ipp, $label) {
	        $this->setIpp($ipp, $label);
	    });
	        
	    $grid->addMethod('addItemsPerPageSelector', function($grid, $items, $label) {
	       $this->addItemsPerPageSelector($items, $label);
	    });
	}
	
	public function setUrlArgs($args)
	{
		$this->paginator->addReloadArgs($args);
	}
	
	/**
	 * Set item per page value.
	 *
	 * if an array is passed, it will also add an ItemPerPageSelector to paginator.
	 *
	 * @param int|array $ipp
	 * @param string    $label
	 *
	 * @throws \Exception
	 */
	public function setIpp($ipp, $label = 'Item per pages:')
	{
		if (is_array($ipp)) {
			$this->addItemsPerPageSelector($ipp, $label);
			
			$ipp = $_GET['ipp'] ?? $ipp[0];
		} 
		
		$this->ipp = $ipp;
	}
	
	/**
	 * Add ItemsPerPageSelector View in grid menu or paginator in order to dynamically setup number of item per page.
	 *
	 * @param array  $items An array of item's per page value.
	 * @param string $label The memu item label.
	 *
	 * @throws \atk4\core\Exception
	 *
	 * @return $this
	 */
	public function addItemsPerPageSelector($items = [10, 25, 50, 100], $label = 'Item per pages:')
	{
		$grid = $this->owner;
		
		$this->ipp = $grid->container->stickyGet('ipp')?: $items[0];
		
		$this->selector = $this->paginator->add(['ItemsPerPageSelector', 'pageLengthItems' => $items, 'label' => $label, 'currentIpp' => $this->ipp], 'afterPaginator');
		$this->paginator->template->trySet('PaginatorType', 'ui grid');
		
		$this->selector->onPageLengthSelect(function ($ipp) use ($grid) {
			$this->ipp = $ipp;
			
			$this->setModelLimitFromPaginator();

			$grid->setUrlArgs(compact('ipp'));
			
			$grid->applySort();
			
			//return the view to reload.
			return $grid->container;
		});
			
		return $grid;
	}
	
	private function setModelLimitFromPaginator()
	{
		$this->paginator->setTotal(ceil($this->owner->model->action('count')->getOne() / $this->ipp));
		
		$this->owner->model->setLimit($this->ipp, ($this->paginator->page - 1) * $this->ipp);
	}
}
