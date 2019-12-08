<?php

namespace atk4\ui;

use atk4\core\Plugin;

/**
 * Set Popup action for table columns filtering.
 */
class TableFilterColumnPlugin extends Plugin
{
	protected $target = Table::class;
	
	public $alias = 'filterColumn';

	/**
	 * An array with column names that need filtering
	 * 
	 * @var array
	 */
	public $columns = [];
	
	protected function activate() {
		/**
		 * @var Table $table
		 */
		$table = $this->owner;
		
		if (! $table->model) {
			throw new Exception('Model need to be defined in order to use column filtering plugin.');
		}
		
		// create column popup.
		foreach ($this->getColumns() as $columnName) {
			if (! $column = $table->columns[$columnName]) continue;

			$pop = $column->addPopup(new TableColumn\FilterPopup(['field' => $table->model->getField($columnName), 'reload' => $table->reload, 'colTrigger' => '#'.$column->name.'_ac']));
			$pop->isFilterOn() ? $column->setHeaderPopupIcon('green caret square down') : null;
			$pop->form->onSubmit(function ($f) use ($pop, $table) {
				return new jsReload($table->reload);
			});
			
			//apply condition according to popup form.
			$table->model = $pop->setFilterCondition($table->model);
		}
	}
	
	protected function getColumns() 
	{
		// set filter to all column when no columns defined
		if (! $this->columns) {
			$table = $this->owner;

			foreach ($table->model->getFields() as $key => $field) {
				if (empty($table->columns[$key])) continue;
				
				$this->columns[] = $field->short_name;
			}
		}
		
		return $this->columns;
	}
}
