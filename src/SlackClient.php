<?php
namespace sky\slack;

use yii\httpclient\Client;
use yii\helpers\Json;
use Yii;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\queue\Queue;

/**
 * @property array $webhookUrls
 */
class SlackClient extends \yii\base\BaseObject
{
    public $clientClass = 'yii\httpclient\Client';
    
    public $clientOptions = [];

    /**
     * @deprecated 25 aug 2021
     * @var array
     */
    public $defaultPayload = [];
    
    public $defaultChannel = 'general';
    
    public $testerChannel = 'tester';

    public $debugChannel = false;

    /**
     * enable or disable
     * @var bool
     */
    public $enable = true;

    /**
     * @deprecated april 2021
     * @see $enable
     * @var bool
     */
    public $offline = false;

    /**
     * queue component name
     * @var string
     */
    public $queue = 'queue';
    
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
     * get all webhooks url
     * @return array
     */
    public function getWebHookUrls()
    {
        return $this->_webhookUrls;        
    }
    
    /**
     * add new webhook
     * @param type $name
     * @param type $url
     * @return $this
     */
    public function addWebHook($name, $url)
    {
        $this->_webhookUrls[$name] = $url;
        return $this;
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
        $url = $this->getWebhookUrl($this->debugChannel ? : $name);

        $this->_channel = $url;
        return $this;
    }
    
    /**
     * Yii::$app->message->send([
     *  'text' => 'Yout Text Here'
     * ]);
     * 
     * @param array|string $payload
     * @return boolean
     */
    
    public function send($payload)
    {
        if (!$this->enable) {
            return true;
        }
        if (is_string($payload)) {
            return $this->send(array_merge($payload, ['text' => $payload]));
        }
        $payload = [
            'payload' => Json::encode($payload),
        ];
        return $this->getClient()->post($this->_channel, $payload)->send();
    }
    
    /**
     * @deprecated april 2021
     * @param string $text
     * @param array $payload
     * @return boolean
     */
    public function sendText($text, $payload = [])
    {
        return $this->send(array_merge($payload, ['text' => $text]));
    }
    
    public function pushQueue($options, $delay = null, $priority = null,  $ttr = null)
    {
        /* @var $queue \yii\queue\db\Queue */
        $queue = Yii::$app->get($this->queue);
        if (!$queue instanceof Queue) {
            throw new \yii\base\NotSupportedException("Queue system not avaliable");
        }
        return $queue->delay($delay)
                ->priority($priority)
                ->ttr($ttr)
                ->push(new SlackWorkers(array_merge(['channel' => $this->_channel], $options)));
    }
    
    /**
     * helper mapping attribute label and value from model
     * @deprecated 24 aug 2021
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

    public function createBuilder()
    {
        return new SlackBuilder(['client' => $this]);
    }

    protected function getWebhookUrl($name)
    {
        return ArrayHelper::getValue($this->_webhookUrls, $name, false);
    }

}
