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
...php
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
        'tags' => ['category1', 'category2']
    ],
    [
        'key' => 'someKey1',
        'value' => 'someValue1',
        'tags' => ['category3', 'category4']
    ]
], 'ru');
// Fetch 1st page resources (default 300 sources) with langs `ru`, `en` and `someGroup1` tag
$translateCenter->fetch([
    'langs' => 'ru,en',
    'tags' => 'someGroup1'
], 1);
```

You can mapping console controller to pulling translations ( if you use advanced template )
```
/app/console/config/main.php
...
'controllerMap' => [
        ...
        'translate-center' => [
            'class' => '\Kialex\TranslateCenter\Console\Controllers\TranslationCenterController',
            'staticGroups' => ['app', 'app_js', 'app_email'],
            'dynamicGroups' => ['db_product', 'db_product_category'],
            'staticStorageClass' => \Kialex\TranslateCenter\Storage\JsonFileStorage,
            'dynamicStorageClass' => \Translate\StorageManager\Storage\ElasticStorage,
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
]
...
```

Using Redis as Message Source
- You need define redis component in your common app config
```
/app/common/config/main.php
'components' => [
    ...
    'redis' => [
        'class' => \yii\redis\Connection::class,
        'hostname' => 'localhost',
        'port' => 6359,
        'database' => 10,
    ],
    ...
]
```
If your redis component ID has another name, you should define it:
```
/app/common/config/main.php
'container' => [
    'singletons' => [
    ...
    \Kialex\TranslateCenter\Storage\RedisStorage::class => [
        'class' => \Kialex\TranslateCenter\Storage\RedisStorage::class,
        'redis' => 'other_redis_compoennt_id'
    ],
    ...
    ]
]
```
- In the controlle map change storage class to Redis Storage:
```
/app/console/config/main.php
...
'controllerMap' => [
        ...
        'translate-center' => [
            ...
            'staticStorageClass' => \Kialex\TranslateCenter\Storage\RedisStorage,
            ...
          ],
    ],
...
```
- Change message source to Redis
```
/app/console/config/main.php
'components' => [
    ...
    'i18n' => [
        'translations' => [
            ...
            '*' => ['class' => '\Kialex\TranslateCenter\Source\RedisSource'],
            ...
        ],
    ]
    ...
]
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