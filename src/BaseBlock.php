<?php
namespace sky\slack;

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

    public function setText($text, $type = 'plain_text', $emoji = null)
    {
        $this->setParams('text', static::textObject($text, $type, $emoji));
        return $this;
    }

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