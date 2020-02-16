<?php

namespace atk4\ui\GridPlugin;

use atk4\ui\Menu as BaseMenu;
use atk4\core\Pluginable;

class Menu extends BaseMenu implements Pluginable
{
    public $short_name = 'menu';
	
	public $region = 'Menu';
	
	public $activate_on_click = false;

	public function init()
	{
	    parent::init();

	    // Add a new buton to the Grid Menu with a given text.
	    $this->owner->addMethod('addButton', function($grid, $text) {
	        return $this->addItem($text);
	    });
	}
	
	public function setUrlArgs($args) {}
	
	public function setActivationMethods($owner) {}
}
