<?php
namespace sky\slack;

use sky\slack\BaseBlock;
use sky\slack\blocks\SectionBlock;
use yii\base\InvalidCallException;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Builder Block Schema
 * @see https://api.slack.com/reference/block-kit
 */
class SlackBuilder extends ParamBuilder
{
    public $channel;

    public $client;
    
    protected $_params = [];

    /**
     * @var BaseBlock[]
     * @retrun $this
     */
    protected $_blocks = [];

    /**
     * set Text
     * @param $text
     * @param string $type
     * @param null $emoji
     * @return $this
     */
    public function setText($text, $type = 'mrkdwn', $emoji = null)
    {
        $this->_params = ArrayHelper::merge($this->_params, BaseBlock::textObject($text, $type, $emoji));
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

    /**
     * add Divider
     * @return $this
     */
    public function addDividerBlock()
    {
        $this->addBlock(new BaseBlock());
        return $this;
    }

    /**
     * add Text Section
     * @param $text
     */
    public function addTextSectionBlock($text)
    {
        $this->createBlock()
            ->setText($text);
        return $this;
    }

    /**
     * add Header
     * @param $text
     * @return $this
     */
    public function addHeaderBlock($text)
    {
        $block = $this->createBlock(BaseBlock::class, ['type' => 'header']);
        $block->setText($text, 'plain_text');
        return $this;
    }

    /**
     * @param string $class
     * @param array $params
     * @return SectionBlock
     */
    public function createBlock($class = SectionBlock::class, $params = [])
    {
        $block = new $class($params);
        $this->addBlock($block);
        return $block;
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
        if (!$slackClient instanceof SlackClient) {
            throw new InvalidCallException('client must be SlackClient Instance');
        }

        if ($this->channel) {
            $slackClient->setChannel($this->channel);
        }

        return $slackClient->send($this->getParams());
    }
}