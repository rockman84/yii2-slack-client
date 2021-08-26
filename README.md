# yii2-slack-client

## Install Package
```
php composer.phar require sky/yii2-slack-client "*"
```
or add in composer.json
```
"sky/yii2-slack-client" : "@dev"
```

## Set Client Configuration
```
'message' => [
    'class' => 'sky\slack\SlackClient',
    'defaultChannel' => 'general',
    'webhookUrls' => [
        // channels web hook
        'general' => 'https://hooks.slack.com/services/[key]',
        'error' => 'https://hooks.slack.com/services/[key]',
        'tester' => 'https://hooks.slack.com/services/[key]',
    ],
],
```

## Set Up Error Target Configuration (Optional)
```
'log' => [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'sky\slack\SlackTarget',
            'levels' => ['error', 'warning'],
            'channel' => 'error',
        ],
    ],
],
```

## Basic how to use
```
use sky\slack\SlackClient;
use Yii;

Yii::$app->message->sendText('Hello World');

Yii::$app->message->setChannel('tester')->sendText('Hello World');

// use attachment
Yii::$app->message->send([
    'text' => 'Hello World',
    'attachments' => [
        [
            'text' => 'Attachment 1',
            'fields' => SlackClient::fieldsAttribute($model, [
                'id',
                'name',
                'country' => 'country.name',
            ])
        ]
    ],
]);
```
## Slack Builder
```phpt
$builder = Yii::$app->message->createBuilder();
$builder->setText('Hello World');

// add Blocks
$builder->addHeaderBlock(['text' => 'Helllo']);
$slack->addDividerBlock();

$sectionBlock = new SectionBlock(['text' => 'this is object block']);
$builder->addBlock($section);

// send builder
$builder->send();

// or
$builder->send(Yii::$app->message);

// or 
Yii::$app->message->sendBuilder($builder);
```

If this library is useful for you, say thanks [buying me a beer :beer:](https://www.paypal.me/huanghanzen)!