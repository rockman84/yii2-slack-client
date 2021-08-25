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

    public function setText($text, $type = 'mrkdwn', $emoji = true)
    {
        $this->setParams('text', [
            'type' => 'plain_text',
            'text' => $text,
            'emoji' => $emoji,
        ]);
        return $this;
    }

    public function getParams()
    {
        $params = parent::getParams();
        $params['type'] = $this->type;
        return $params;
    }
}