<?php
namespace sky\slack;

use sky\yii\helpers\ArrayHelper;

/**
 * Base Block
 *
 * @property-read array $text
 */
class BaseBlock extends ParamBuilder
{
    /**
     * unique id
     * @var string
     */
    public $id;

    /**
     * is include to params
     * @var bool
     */
    public $visible = true;

    /**
     * type
     * @var string
     */
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
     *
     * @see https://api.slack.com/reference/surfaces/formatting
     * @param $text
     * @param string $type
     * @param null $emoji
     * @return array
     */
    public static function textObject($text, $type = 'plain_text', $emoji = null)
    {
        $textobj =  [
            'text' => $text,
            'type' => $type,
        ];
        $emoji && $textobj['emoji'] = $emoji;
        return $textobj;
    }

    /**
     * get all params
     * @return array
     */
    public function getParams()
    {
        $params = parent::getParams();
        $params['type'] = $this->type;
        return $params;
    }
}