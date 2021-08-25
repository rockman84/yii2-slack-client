<?php
namespace sky\slack\blocks;

use sky\slack\BaseBlock;
use yii\base\NotSupportedException;

/**
 * @see https://api.slack.com/reference/block-kit/blocks#image
 */
class ImageBlock extends BaseBlock
{
    public $type = 'image';

    public $imageUrl;

    public $altText;

    public $title;

    public function getParams()
    {
        $params = parent::getParams();
        $params['image_url'] = $this->imageUrl;
        $params['alt_text'] = $this->altText;
        $this->title && $params['title'] = $this->title;
        return $params;
    }

    public function setText($text, $type = 'mrkdwn', $emoji = true)
    {
        throw new NotSupportedException('Image Block Not Supported Text');
    }
}