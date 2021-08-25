<?php
namespace sky\slack;

use Yii;
use yii\queue\RetryableJobInterface;

class SlackWorkers extends \yii\base\BaseObject implements RetryableJobInterface
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

    public function getTtr()
    {
        return 60 * 5;
    }

    public function canRetry($attempt, $error)
    {
        return $attempt <= 5;
    }
}