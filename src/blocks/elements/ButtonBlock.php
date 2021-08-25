<?php
namespace sky\slack\blocks\elements;

use sky\slack\BaseBlock;

class ButtonBlock extends BaseBlock
{
    const STYLE_DEFAULT = 'default';
    const STYLE_PRIMARY = 'primary';
    const STYLE_DANGER = 'danger';

    public $type = 'button';

    public $actionId = 'button';

    public $url;

    public $style;

    public $confirm;

    public $value;

    public function getParams()
    {
        $params = parent::getParams();
        $params['url'] = $this->url;

        $params['action_id'] = $this->actionId;
        $this->confirm && $params['confirm'] = $this->confirm;
        $this->value && $params['confirm'] = $this->value;
        $this->style && $params['style'] = $this->style;
        unset($params['block_id']);
        return $params;
    }

    public function setText($text, $type = 'plain_text', $emoji = true)
    {
        return parent::setText($text, $type, $emoji);
    }
}