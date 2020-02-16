<?php

namespace atk4\ui\GridPlugin;

use atk4\core\Pluginable;

class ActionButtons extends \atk4\ui\TableColumn\Generic implements Pluginable
{
    public $short_name = 'actionButtons';
    
    public $decorator = 'ActionButtons';

	public function init()
	{
	    parent::init();

	    // Add a new buton to the Grid Menu with a given text.
	    $this->owner->addMethod('addButton', function($grid, $text) {
	        return $this->addItem($text);
	    });
	}
	
	public function setUrlArgs($args) {}
	
	public function setActivationMethods($grid) {
	    $grid->addMethod('addActionButton', function($grid, $button, $action = null, $confirm = false, $isDisabeld = false) {
	        if (!$grid->hasElement($this->short_name)) {
	            $grid->add($this);
	            
	            $this->decorator = $grid->table->addColumn(null, $this->decorator);
	        }
	        
	        return $grid->getElement($this->short_name)->addButton($button, $action, $confirm, $isDisabeld);
	    });
	}
	
	public function __call($name, $args) {
	    if (method_exists($this->decorator, $name)) {
	        $this->decorator->{$name}(...$args);
	    }
	}
}
