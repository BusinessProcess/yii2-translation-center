### Installing via Composer

The recommended way to install Guzzle is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of Guzzle:

```bash
composer require kialex/yii2-translation-center dev-master
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

Client configuration ( if you use advanced template )
```
/app/common/config/main.php
...
'container' => [
        'singletons' => [
            'Kialex\TranslateCenter\Client' => [
                [],
                [
                    [
                        'login' => 'admin.center@center.ru',
                        'password' => 'qwerty',
                        'projectUuid' => 'xxxxxx-fxxx-4xx2-8c0a-35axxxxxx8'
                    ],
                ]
            ]
        ]
    ],
...
```

How to use:
```php
$translateCenter = \Yii::createObject(\Kialex\TranslateCenter\Client::class);
$translateCenter->createResource([
    [
        'key' => 'someKey',
        'value' => 'someValue',
        'tags' => ['someGroup1']
    ],
    [
        'key' => 'someKey1',
        'value' => 'someValue1',
        'tags' => ['someGroup1']
    ]
], 'ru');
// Fetch 1st page resources (default 300 sources) with langs `ru`, `en` and `someGroup1` tag
$translateCenter->fetch([
    'langs' => 'ru,en',
    'tags' => 'someGroup1'
], 1);
```

You can mapping console controller to pushing/creating resources ( if you use advanced template )
```
/app/console/config/main.php
...
'controllerMap' => [
        ...
        'translate-center' => [
            'class' => 'Kialex\TranslateCenter\Console\Controllers\TranslationCenterController',
            'staticSources' => ['group1', 'group2', 'group3'],
            'dynamicSources' => ['group4', 'group5', 'group6']
          ],
    ],
...
```
Attention! This controller uses json storage to keep you transaltion, you should also configure i18n components:
```php
...
'components' => [
        ...
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource', // default Yii transaltion
                    'fileMap' => [
                        'app'       => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ],
                '*' => [
                    'class' => '\yii\i18n\JsonMessageSource', // Your tranlation fron Translate Center
                    'basePath' => '@common/messages'
                    // If you change this path, you shoud change it in// 
                    // `\Kialex\TranslateCenter\Storage\JsonFileStorage` as well
                    // You may do it via singletons or defenations in config
                ],
            ],
        ]
...
```

Fetching static resources to default Yii 2 translations:
```
# ./yii translation-center/pull-static-sources
...
// Now you can use:
\Yii::t('someGroup1', 'someKey');
...
// You also fetching dynamic resources, but not recommended for this version!
# ./yii translation-center/pull-dynamic-sources
```