<?php

require __DIR__ . '/enviroments.php';
require __DIR__ . '/urls_custom.php';
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$redis = require __DIR__ . '/redis.php';

$config = [
    'id' => 'app-basic',
    'name' => 'Basic Application',
    'language'=>'en-US',
    'sourceLanguage' => 'en-US',
    'basePath' => dirname(__DIR__),
    'layoutPath' => '@vendor/weebz/yii2-basics/src/themes/adminlte3/views/layouts',
    'controllerNamespace' => 'app\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset'
    ],
    'modules' => [
        'common' => [ 
            'class' => '\weebz\yii2basics\Module',
            'layoutPath' => '@vendor/weebz/yii2-basics/src/themes/adminlte3/views/layouts', 
        ],
        'gridview' =>  [
             'class' => '\kartik\grid\Module',
             // enter optional module parameters below - only if you need to  
             // use your own export download action or custom translation 
             // message source
             'downloadAction' => 'gridview/export/download',
             // 'i18n' => []
         ]
    ],
    'components' => [
        'httpclient' => [
            'class' => 'yii\httpclient\Client',
            'baseUrl' => '/rest', // ajuste conforme necessário
        ],
        'session' => [
            'timeout' => 2628000, //1 month in seconds
            'class' => 'yii\web\DbSession',
            'sessionTable' => 'yii_session',
        ],
        'view' => [
            'theme' => [
                'basePath' => '@vendor/weebz/yii2-basics/src/themes/adminlte3',
                'baseUrl' => '@vendor/weebz/yii2-basics/src/themes/adminlte3/web',
                'pathMap' => [
                    '@app/views' => '@vendor/weebz/yii2-basics/src/themes/adminlte3/views',
                ],  
            ],
        ],

        'formatter' => [
            'locale'=> 'pt_BR',
            'defaultTimeZone'	=> 'America/Fortaleza',
            'dateFormat' => 'dd/MM/yyyy',
            'datetimeFormat' => 'php:d/m/y H:i',
            'timeFormat' => 'php:H:i',
            'decimalSeparator' => '.',
            'thousandSeparator' => '',
            'currencyCode' => 'BRL',
            'class' => '\weebz\yii2basics\formatters\Custom',
        ],
        
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\DbMessageSource',
                    'db' => 'db',
                    'sourceLanguage' => 'en-US',
                    'sourceMessageTable' => '{{%source_message}}',
                    'messageTable' => '{{%message}}',
                ],
                'app' => [
                    'class' => 'yii\i18n\DbMessageSource',
                    'db' => 'db',
                    'sourceLanguage' => 'en-US',
                    'sourceMessageTable' => '{{%source_message}}',
                    'messageTable' => '{{%message}}',
                ],
            ],
        ],
        
        'user' => [
            'identityClass' => '\weebz\yii2basics\models\User',
            'enableAutoLogin' => false,
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => array_merge(
                customControllersUrl(['site-section','util','site','group','user','rule','language','source-message','message',
                'menu','params','configuration','meta-tag','email-service','license-type','license','log','folder','file','role',
                'section','page','notification','notification-message','rest/mail','rest/storage','rest/auth','rest/address','rest/instagram','rest/youtube','rest/cron'],'common'),
            [
                "site/clear-cache/<key:\w+>" => "site/clear-cache",
                "page/show/<id:\w+>" => "page/show",
                '<controller:\w+>/<id:\d+>' => '<controller>/view',			
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',			
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                ['class' => 'yii\rest\UrlRule', 'controller' => 'tools'],
            ]),
        ],

        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
            //'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            // 'csrfParam' => '_csrf-backend',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

        'errorHandler' => [
            'errorAction' => 'common/site/error',
        ],

        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'redis' => $redis,
       
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
        'generators' => [ // here
            'crud' => [ // generator name
                'class' => 'yii\gii\generators\crud\Generator', // generator class
                'templates' => [ // setting for our templates
                    'yii2-basics' => '@vendor/weebz/yii2-basics/src/gii/generators/crud/default' // template name => path to template
                ]
            ],
            'model' => [
                'class' => 'weebz\yii2basics\gii\generators\model\CustomGenerator',
                'templates' => [
                    'custom' => '@vendor/weebz/yii2-basics/src/gii/generators/model/custom',
                ],
            ],
        ]
    ];
}

return $config;