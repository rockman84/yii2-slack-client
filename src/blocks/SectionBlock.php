<?php
namespace sky\slack\blocks;

use sky\slack\BaseBlock;

class SectionBlock extends BaseBlock
{
    public $type = 'section';
    /**
     * @var BaseBlock
     */
    public $accessory;

    public function getParams()
    {
        $params = parent::getParams();
        $this->accessory && $params['accessory'] = $this->accessory->getParams();
        return $params;
    }
}