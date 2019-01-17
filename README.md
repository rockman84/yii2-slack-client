# yii2-slack-client

## Set Client Configuration
```
'message' => [
    'class' => 'sky\slack\SlackClient',
    'defaultChannel' => 'general',
    'offline' => false,
    'webhookUrls' => [
        // channels web hook
        'general' => 'https://hooks.slack.com/services/[key]',
        'error' => 'https://hooks.slack.com/services/[key]',
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
            'channel' => YII_DEBUG ? 'tester' : 'error',
        ],
    ],
],
```