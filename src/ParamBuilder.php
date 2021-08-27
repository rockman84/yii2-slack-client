<?php
namespace sky\slack;

use sky\yii\helpers\ArrayHelper;
use yii\base\BaseObject;

/**
 * @property array $params
 */
class ParamBuilder extends BaseObject
{
    protected $_params = [];

    public function setParams($key, $value)
    {
        ArrayHelper::setValue($this->_params, $key, $value);
    }

    public function getParams()
    {
        return $this->_params;
    }
}