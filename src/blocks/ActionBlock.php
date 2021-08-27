<?php
namespace sky\slack\blocks;

use sky\slack\BaseBlock;
use sky\yii\helpers\ArrayHelper;

class ActionBlock extends BaseBlock
{
    public $type = 'actions';

    /**
     * @var BaseBlock[]
     */
    protected $_elements = [];

    public function addElement(BaseBlock $element)
    {
        $this->_elements[$element->id] = $element;
    }

    public function getElements($id = null)
    {
        if ($id === null) {
            return $this->_elements;
        }
        return ArrayHelper::getValue($this->_elements, $id);
    }

    public function getParams()
    {
        $params = parent::getParams();
        foreach ($this->_elements as $element) {
            $params['elements'][] = $this->_elements->getParams();
        }
        return $params;
    }
}