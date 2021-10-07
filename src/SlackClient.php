<?php
namespace sky\slack;

use sky\slack\blocks\SectionBlock;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\helpers\Json;
use Yii;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\queue\Queue;

/**
 * @property array $webhookUrls
 * @property string $channel
 */
class SlackClient extends \yii\base\BaseObject
{
    public $clientClass = 'yii\httpclient\Client';
    
    public $clientOptions = [];

    /**
     * @deprecated 7 oct 2021
     * @see SlackClient::$channel
     * @var string
     */
    public $defaultChannel = 'general';

    /**
     * @deprecated 27 aug 2021
     * @see SlackClient::$debugChannel
     * @var string
     */
    public $testerChannel = 'tester';

    /**
     * debug channel name, if set all send in debug channel
     * @var string|null
     */
    public $debugChannel = 'debug';

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
     * @deprecated 25 aug 2021
     * @var array
     */
    public $defaultPayload = [];

    /**
     * queue component name
     * @var string
     */
    public $queue = 'queue';

    /**
     * list of channels available
     * @var array
     */
    private $_webhookUrls = [];

    /**
     * channel name
     * @var string
     */
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
            $this->_client = Yii::createObject(array_merge([
                'class' => $this->clientClass,
                'transport' => 'yii\httpclient\CurlTransport'
            ], $this->clientOptions));
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
            throw new \yii\base\InvalidConfigException($name . ' channel not exist');
        }
        $this->_channel = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->_channel;
    }

    /**
     * Yii::$app->message->send([
     *  'text' => 'Yout Text Here'
     * ]);
     *
     * @param array|string $payload
     * @return \yii\httpclient\Response|boolean
     * @throws \yii\httpclient\Exception
     * @throws \Exception
     */
    
    public function send($payload)
    {
        if (!$this->enable) {
            return true;
        }
        if (is_string($payload)) {
            return $this->sendText($payload);
        }
        if ($this->debugChannel) {
            $builder = new SectionBlock(['text' => "*** This sent on debug mode ***"]);
            $builder->addField('Target Channel', $this->_channel)
                ->addField('App Name', Yii::$app->name);
            $payload['blocks'][] = $builder->getParams();
        }
        $payload = [
            'payload' => Json::encode($payload),
        ];
        $url = $this->getWebhookUrl($this->debugChannel ? : $this->_channel);
        if (!$url) {
            throw new InvalidConfigException("Channel {$this->_channel} not found");
        }
        return $this->getClient()
            ->post($url, $payload)
            ->send();
    }

    /**
     * @param string $text
     * @param array $payload
     * @return boolean
     * @throws \yii\httpclient\Exception
     */
    public function sendText($text, $payload = [])
    {
        $builder = $this->createBuilder()->setText($text)->addTextSectionBlock($text);
        return $this->send(array_merge($builder->getParams(), $payload));
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

    /**
     * create slack builder
     * @param array $params
     * @return SlackBuilder
     */
    public function createBuilder($params = [])
    {
        $params['client'] = $this;
        return new SlackBuilder($params);
    }

    /**
     * send Builder
     * @param SlackBuilder $builder
     * @return bool
     */
    public function sendBuilder(SlackBuilder $builder)
    {
        return $builder->send($this);
    }

    /**
     * get Url Webhook
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    protected function getWebhookUrl($name)
    {
        return ArrayHelper::getValue($this->_webhookUrls, $name, false);
    }

}
