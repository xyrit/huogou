<?php
/**
 * Created by PhpStorm.
 * User: jun
 * Date: 15/9/17
 * Time: 上午9:57
 */

return [
    'class' => 'yii\web\View',
    'defaultExtension' => 'html',
    'renderers' => [
        'html' => [
            'class' => 'yii\twig\ViewRenderer',
            'cachePath' => '@runtime/Twig/cache',
            // Array of twig options:
            'options' => [
                'auto_reload' => true,
                'debug'=>true,
            ],
            'lexerOptions' => [
                'tag_comment' => ['{*', '*}'],
                'tag_block' => ['{%', '%}'],
                'tag_variable' => ['{{', '}}']
            ],
            'globals' => ['html' => '\app\helpers\Html'],
            'uses' => ['yii\bootstrap','yii\grid'],
            'extensions'=>['Twig_Extension_Debug'],
        ],
    ],
];