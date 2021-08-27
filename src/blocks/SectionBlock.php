<?php
namespace sky\slack\blocks;

use sky\slack\BaseBlock;
use sky\yii\helpers\ArrayHelper;

class SectionBlock extends BaseBlock
{
    public $type = 'section';
    /**
     * @var BaseBlock
     */
    public $accessory;

    public $_fields = [];

    public function addFields($text, $type = 'plain_text', $emoji = true)
    {
        $this->_fields[] = static::textObject($text, $type, $emoji);
        return $this;
    }

    public function getParams()
    {
        $params = parent::getParams();
        $this->accessory && $params['accessory'] = $this->accessory->getParams();

        return $this->_fields ? ArrayHelper::merge($params, ['fields' => $this->_fields]) : $params;
    }
}