<?php
namespace sky\slack;

use Yii;
use sky\slack\SlackClient;
use yii\db\ActiveRecord;

/**
 * @property ActiveRecord $owner
 */
class SlackBehavior extends \yii\base\Behavior
{
    public $events = [];
    
    public $attributes = [];
    
    public $text = 'User ID: {user_id}, {event} {table_name} table';
    
    public function events() {
        if ($this->events !== false && !$this->events) {
            $this->events = [
                ActiveRecord::EVENT_AFTER_INSERT,
                ActiveRecord::EVENT_AFTER_UPDATE,
                ActiveRecord::EVENT_BEFORE_DELETE,
            ];
        }
        return array_fill_keys(
            $this->events,
            'sendSlack'
        );
    }
    
    public function sendSlack($event)
    {
        Yii::$app->message->send([
            'text' => Yii::t('app', $this->text, [
                'table_name' => $this->owner->tableName(),
                'event' => $event->name,
                'user_id' => Yii::$app->user->id,
            ]),
            'attachments' => [
                [
                    'fields' => $this->dataModel
                ]
            ],
        ]);
    }
    
    public function getDataModel()
    {
        if ($this->attributes === false) {
            return [];
        }
        if (!$this->attributes) {
            $this->attributes = array_keys($this->owner->getAttributes());
        }
        return SlackClient::fieldsAttribute($this->owner, $this->attributes);
    }
}

