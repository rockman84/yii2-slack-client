<?php
namespace sky\slack;

use Yii;

class SlackWorkers extends \yii\base\BaseObject implements \yii\queue\JobInterface
{
    public $text;
    
    public $payload = [];
    
    public $channel = null;
    
    public function execute($queue) {
        $slack = Yii::$app->message;
        if ($this->channel != null) {
            $slack->setChannel($this->channel);
        }
        $slack->sendText($text, $this->payload);
        
    }
}