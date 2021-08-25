<?php
namespace sky\slack;

use yii\helpers\ArrayHelper;

/**
 * https://api.slack.com/reference/surfaces/formatting
 */
class SlackBuilder extends ParamBuilder
{
    public $channelName;

    public $client;

    protected $_params = [
        'text' => null,
        'mrkdwn' => true,
    ];

    /**
     * @var BaseBlock[]
     * @retrun $this
     */
    protected $_blocks = [];

    public function setText($text)
    {
        ArrayHelper::setValue($this->_params, 'text', $text);
        return $this;
    }
    /**
     * @see https://api.slack.com/reference/block-kit/blocks
     * @param $id
     * @param BaseBlock $block
     * @retrun $this
     */
    public function addBlock(BaseBlock $block)
    {
        $block->parent = $this;
        $this->_blocks[$block->id] = $block;
        return $this;
    }

    public function addDividerBlock()
    {
        $this->addBlock(new BaseBlock(['parent' => $this]));
    }

    public function getParams()
    {
        $blocks = [];
        foreach ($this->_blocks as $block)
        {
            $params = $block->visible ? $block->getParams() : [];
            if ($params) {
                $params['block_id'] = 'block-' . $block->id;
                $blocks[] = $params;
            }
        }

        return ArrayHelper::merge($this->_params, ['blocks' => $blocks]);
    }

    public function send(SlackClient $slackClient = null)
    {
        $slackClient = $slackClient ? : $this->client;
        return $slackClient->send($this->getParams());
    }
}