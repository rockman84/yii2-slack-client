<?php
namespace sky\slack;

use sky\yii\helpers\ArrayHelper;

/**
 * @property-read array $text
 * @property-write string $string
 */
class BaseBlock extends ParamBuilder
{
    public $id;

    public $visible = true;

    public $type = 'divider';

    public $parent;

    protected $_params = [];

    public function init()
    {
        if (!$this->id) {
            $this->id = rand(1, 9999);
        }
        parent::init();
    }

    /**
     * set Text
     * @param $text
     * @param string $type
     * @param null $emoji
     * @return $this
     */
    public function setText($text, $type = 'plain_text', $emoji = null)
    {
        $this->setParams('text', static::textObject($text, $type, $emoji));
        return $this;
    }

    /**
     * get Text
     * @return array
     * @throws \Exception
     */
    public function getText()
    {
        return ArrayHelper::getValue($this->_params, 'text');
    }

    /**
     * Text Object Build
     * @param $text
     * @param string $type
     * @param null $emoji
     * @return array
     */
    protected static function textObject($text, $type = 'plain_text', $emoji = null)
    {
        $textobj =  [
            'text' => $text,
            'type' => $type,
        ];
        $emoji && $textobj['emoji'] = $emoji;
        return $textobj;
    }

    public function getParams()
    {
        $params = parent::getParams();
        $params['type'] = $this->type;
        return $params;
    }
}