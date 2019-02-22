<?php
namespace sky\slack;

use yii\helpers\ArrayHelper;

class StoreCollection extends \yii\base\BaseObject
{
    protected $_store = [];

    public function addValue($value, $key = null)
    {
        if ($key) {
            $this->_store[$key] = $value;
        } else {
            $this->_store[] = $value;
        }
    }
    
    public function getValue($key = null)
    {
        if ($key) {
            return ArrayHelper::getValue($this->_store, $key);
        } else {
            return $this->_store;
        }
    }
}