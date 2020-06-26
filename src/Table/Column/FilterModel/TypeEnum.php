<?php

declare(strict_types=1);

namespace atk4\ui\Table\Column\FilterModel;

use atk4\data\Model;
use atk4\ui\Table\Column;

class TypeEnum extends Column\FilterModel
{
    public function init(): void
    {
        //bypass parent init since we are not using op and value field but create them from
        //the lookup field value.
        Model::init();
        $this->afterInit();

        $this->op = null;
        if ($this->lookupField->values) {
            foreach ($this->lookupField->values as $key => $value) {
                $this->addField($key, ['type' => 'boolean', 'ui' => ['caption' => $value]]);
            }
        } elseif ($this->lookupField->enum) {
            foreach ($this->lookupField->enum as $enum) {
                $this->addField($enum, ['type' => 'boolean', 'ui' => ['caption' => $enum]]);
            }
        }
    }

    public function setConditionForModel($model)
    {
        $filter = $this->recallData();
        $values = [];
        foreach ($filter as $key => $isSet) {
            if ($isSet === true) {
                $values[] = $key;
            }
        }
        if (!empty($values)) {
            $model->addCondition($filter['name'], 'in', $values);
        }

        return $model;
    }
}
