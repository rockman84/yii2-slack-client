<?php

namespace sky\slack;

use Yii;
use yii\log\Target;

class SlackTarget extends Target
{   
    /**
     * set channel key
     * 
     * @var string
     */
    public $channel = 'general';
    
    /**
     * set name component application
     * 
     * @var string
     */
    public $componentName = 'message';
    
    public function init() {
        if (!Yii::$app->{$this->componentName} instanceof \sky\slack\SlackClient) {
            throw new \yii\base\InvalidConfigException("component {$this->componentName} not set");
        }
        return parent::init();
    }

    public function export()
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";
        $fields = [];
        if (Yii::$app instanceof \yii\web\Application) {
            $fields = [
                [
                    'title' => 'User',
                    'value' => !Yii::$app->user->isGuest ? Yii::$app->user->identity->email : '-',
                    'short' => false,
                ],
                [
                    'title' => 'Url',
                    'value' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '-',
                    'short' => true,
                ],
            ];
        } elseif (Yii::$app instanceof \yii\web\Application) {
            $fields = [
                [
                    'title' => 'Type',
                    'value' => 'console',
                    'short' => true,
                ],
            ];
        }
        $fields[] = [
            'title' => 'Controller Action',
            'value' => Yii::$app->controller ? Yii::$app->controller->id . '/' . Yii::$app->controller->action->id : null,
            'short' => true
        ];
        $fields[] = [
            'title' => 'Level',
            'value' => $this->levels,
            'short' => true
        ];
        $fields[] = [
            'title' => 'Debug Model',
            'value' => YII_DEBUG,
            'short' => true
        ];
        Yii::$app->{$this->componentName}->setChannel($this->channel)->send([
            'text' => "ERROR LEVEL {$this->levels} - " . Yii::$app->name,
            'attachments' => [
                [
                    'color' => "#ff0000",
                    'text' => $text,
                    'fields' => $fields,
                ]
            ]
        ]);
    }
}
