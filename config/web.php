<?php

$params = require(__DIR__ . '/params.php');
use \yii\web\Request;


//$baseUrl = str_replace('', '', (new Request)->getBaseUrl());
$baseUrl = str_replace('/web', '', (new Request)->getBaseUrl());

$vendorDir = dirname(__DIR__) . '/vendor';

$config = [
    'layout'=>'main',
    'name'=>'Staks Personal Metal Manager',
    'vendorPath' => $vendorDir,
    'extensions' => array_merge(
        require($vendorDir . '/yiisoft/extensions.php'),
        [
            'Imagine' =>
                array (
                    'name' => 'Imagine',
                    'version' => '1',
                    'alias' =>
                        array (
                            '@Imagine' => $vendorDir . '/imagine/lib/Imagine',
                        ),
                ),
        ]
    ),
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    //'language' => 'ru',
    //'language' => 'ru-RU',
    //'sourceLanguage' => 'en-US',
    'components' => [
        'formatter' => array(
            'class' => 'app\components\Formatter',
        ),
        'assetManager' => [
            'bundles' => [
                'yii\jui\JuiAsset' => [
                    'css' => [
                        'jquery-ui.css',
                    ],
                    'js' => []
                ],
                'yii\bootstrap\BootstrapPluginAsset' => [
                    'js'=>[]
                ],
                'yii\bootstrap\BootstrapAsset' => [
                    'css' => [],
                ],
            ],
        ],
        'request' => [
            'baseUrl' => $baseUrl,
            'cookieValidationKey' => 'Nurbek',
        ],
        'urlManager'=>[
            'enablePrettyUrl'=>true,
            'showScriptName'=>false,
            //'showScriptName'=>false,
            'baseUrl'=>$baseUrl,
           /* 'rules'=>array(
                'sitemap.xml'=>'site/xml',
                'forum/<urlTitle>-<id:\d+>'=>'forum/view',
                'forum/<action:(complete|create|view|update|delete|admin|deleteFile|form)>/*'=>'forum/<action>',
                'forum/<order:popular>'=>'forum/index',
                'forum/<cat>/search/<search>'=>'forum/index',
                'forum/<cat>'=>'forum/index',
                'forum/*'=>'forum/index',
                'forum/<action:(index)>'=>'forum/<action>',
            ),*/
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            //'defaultRoles' => ['1','2',], //здесь прописываем роли
            //зададим куда будут сохраняться наши файлы конфигураций RBAC
            'itemFile' => '@app/components/rbac/items.php',
            'assignmentFile' => '@app/components/rbac/assignments.php',
            'ruleFile' => '@app/components/rbac/rules.php'
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'UM' => array(
            'class' => 'app\components\UM',
        ),
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'view' => [
            'theme' => [
                'pathMap' => ['@app/views' => '@app/themes/porto'],
                'baseUrl' => '@web/themes/porto',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV)
{
    $config['components']['db'] = require(__DIR__ . '/db.php');
    // configuration adjustments for 'dev' environment
/*    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';*/

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}
else
{
    $config['components']['db'] = require(__DIR__ . '/db_prod.php');
}


/*
    $config['components']['log']['targets'][] = [
        'class' => 'yii\log\FileTarget',
        'levels' => ['info'],
        'categories' => [ 'yii\db*', ],
        'logFile' => '@app/runtime/logs/API/requests.log',
        'maxFileSize' => 1024 * 2,
        'maxLogFiles' => 20,
    ];*/

return $config;
