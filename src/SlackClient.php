<?php
namespace sky\slack;

use yii\httpclient\Client;
use yii\helpers\Json;
use Yii;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;

class SlackClient extends \yii\base\BaseObject
{
    public $clientClass = 'yii\httpclient\Client';
    public $clientOptions = [];
    public $defaultPayload = [];
    public $defaultChannel = 'general';
    public $testerChannel = 'tester';
    public $offline = false;
    
    private $_webhookUrls = [];
    private $_channel;
    
    protected $_client;
    
    public function init() {
        $this->setChannel($this->defaultChannel);
        return parent::init();
    }
    /**
     * get http client
     * 
     * @return Client
     */
    public function getClient()
    {
        if (!$this->_client) {
            $this->_client = Yii::createObject(array_merge(['class' => $this->clientClass], $this->clientOptions));
        }
        return $this->_client;
    }
    
    /**
     * set client webhook url
     * @param array $urls
     */
    public function setWebHookUrls(Array $urls)
    {
        $this->_webhookUrls = $urls;
    }
    
    /**
     * set channel key
     * 
     * @param string $name
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function setChannel($name)
    {
        if (!isset($this->_webhookUrls[$name])) {
            throw new \yii\base\InvalidConfigException($name . ' channel not exsist');
        }
        if (YII_ENV == 'dev') {
            $name = $this->testerChannel;
        }
        $this->_channel = $this->_webhookUrls[$name];
        return $this;
    }
    
    /**
     * Yii::$app->message->send([
     *  'text' => 'Yout Text Here'
     * ]);
     * 
     * @param array $payload
     * @return boolean
     */
    
    public function send($payload)
    {
        if ($this->offline) {
            return true;
        }
        $payload = [
            'payload' => Json::encode(array_merge($this->defaultPayload, $payload)),
        ];
        return $this->getClient()->post($this->_channel, $payload)->send();
    }
    
    /**
     * 
     * @param string $text
     * @param array $payload
     * @return boolean
     */
    public function sendText($text, $payload = [])
    {
        return $this->send(array_merge($payload, ['text' => $text]));
    }
    
    /**
     * helper mapping attribute label and value from model
     * 
     * @param BaseActiveRecord $model
     * @param array $attributes
     * @param boolean $short
     * @return array
     */
    public static function fieldsAttribute(BaseActiveRecord $model, $attributes = [], $short = true)
    {
        $attributes = $attributes ? : $model->attributes();
        $fields = [];
        foreach ($attributes as $attribute => $value) {
            $attribute = is_int($attribute) ? $value : $attribute;
            $fields[] = [
                'title' => $model->getAttributeLabel($attribute),
                'value' => ArrayHelper::getValue($model, $value),
                'short' => $short,
            ];
        }
        return $fields;
    }

}