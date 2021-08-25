<?php
namespace sky\slack\blocks\elements;

use sky\slack\BaseBlock;

class ImageElement extends BaseBlock
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