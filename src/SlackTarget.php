<?php

namespace sky\slack;

use sky\slack\blocks\SectionBlock;
use Yii;
use yii\base\InvalidCallException;
use yii\console\Application;
use yii\log\Target;
use yii\web\User;

/**
 * @property array $fields
 * @property SlackClient $slack
 */
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

    /**
     * @var callable
     */
    public $filterTarget;

    public $logVars = [
        '_POST',
        '_GET',
        '_FILES',
    ];

    /**
     * enable send via queue
     * @deprecated 27 aug 2021
     * @var boolean
     */
    public $enableQueue = false;
    
    public function init() {
        if (!Yii::$app->{$this->componentName} instanceof \sky\slack\SlackClient) {
            throw new \yii\base\InvalidConfigException("component {$this->componentName} not set");
        }
        return parent::init();
    }

    public function export()
    {
        if (is_callable($this->filterTarget)) {
            $filter = call_user_func_array($this->filterLog, [Yii::$app, $this]);
            if (!$filter) {
                return;
            }
        }
        $title = Yii::$app->name . " Alert!";
        $builder = $this->slack->createBuilder(['channel' => $this->channel]);
        $builder->setText($title)
            ->addHeaderBlock(Yii::$app->name . " Alert!")
            ->addDividerBlock();

        foreach ($this->messages as $message) {
            $builder->createBlock()
                ->setText($this->formatMessage($message));
            $builder->addDividerBlock();
        }

        $baseData = [
            'Controller Action' => Yii::$app->controller ? Yii::$app->controller->id . '/' . Yii::$app->controller->action->id : null,
            'Application' => Yii::$app instanceof Application ? 'console' : 'web',
            'App ID' => Yii::$app->id,
            'Module' => Yii::$app->module ? Yii::$app->module->id : null,
        ];
        static::addFields($builder->createBlock(), $baseData);

        if (Yii::$app instanceof \yii\web\Application) {
            $request = Yii::$app->request;
            $webData = [
                'URL' => $_SERVER['REQUEST_URI'],
                'Referrer' => $request->referrer,
                'Remote IP' => $request->remoteIP,
                'User Agent' => $request->userAgent,
                'User Host' => $request->userHost,
                'Auth User' => $request->authUser,
                'Server Name' => $request->serverName,
                'Server Port' => $request->port,
                'Method / Is Ajax' => $request->method . '/' . Yii::$app->formatter->asBoolean(Yii::$app->request->isAjax),
            ];
            static::addFields($builder->createBlock(), $webData);
        }

        if (Yii::$app->get('user') instanceof User) {
            $user = Yii::$app->user;
            $userBlock = $builder->createBlock()->addField('User ID', $user->id ? : 'Guest');
            if ($user->identity && isset($user->identity->email)) {
                $userBlock->addField('Email', $user->identity->email);
            }
        }

        $builder->addDividerBlock()->send();
    }

    public function getSlack()
    {
        $slack = Yii::$app->get($this->componentName);
        if ($slack instanceof SlackClient) {
            return $slack;
        }
        throw new InvalidCallException('Slack Component must instance Slack Client');
    }

    protected static function addFields(SectionBlock $block, $params = [])
    {
        foreach ($params as $label => $value) {
            $block->addField($label, "`" . ($value ? : "N/A") . "`");
        }
    }

    /**
     * @deprecated 7 oct 2021
     * @return array|array[]
     */
    public function getFields()
    {
        $fields = [];
        if (Yii::$app instanceof \yii\web\Application) {
            $fields = [
                [
                    'title' => 'User Agent',
                    'value' => Yii::$app->request->userAgent,
                    'short' => false,
                ],
                [
                    'title' => 'User Email',
                    'value' => !Yii::$app->user->isGuest ? Yii::$app->user->identity->email : 'Guest User',
                    'short' => true,
                ],
                [
                    'title' => 'Remote IP',
                    'value' => Yii::$app->request->remoteIP,
                    'short' => true,
                ],
                [
                    'title' => 'Server Name',
                    'value' => Yii::$app->request->serverName,
                    'short' => true,
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
        $fields[] = [
            'title' => 'App ID',
            'value' => Yii::$app->id,
            'short' => true
        ];
        return $fields;
    }
}
