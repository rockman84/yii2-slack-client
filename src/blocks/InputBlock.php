<?php
namespace sky\slack\blocks;

use sky\slack\BaseBlock;
use sky\yii\helpers\ArrayHelper;

class InputBlock extends ActionBlock
{
    public $type = 'input';

    public $dispatchAction;

    /**
     * @var boolean
     */
    public $optional;

    public function setLabel($text)
    {
        $this->setParams('label', static::textObject($text, 'plain_text'));
    }

    public function setHint($text)
    {
        $this->setParams('hint', static::textObject($text, 'plain_text'));
    }

    public function getParams()
    {
        $params = parent::getParams();
        foreach ($this->_elements as $element) {
            $params['elements'][] = $this->_elements->getParams();
        }
        $this->dispatchAction && $params['dispatch_action'] = $this->dispatchAction;
        $this->hint && $params['hint'] = $this->hint;
        $this->optional && $params['optional'] = $this->optional;
        return $params;
    }
}