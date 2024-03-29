<?php
namespace user\controller;

require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;
use common\model\CSRF;

// テンプレート指定
$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig   = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );

$csrf = new CSRF();

//初期データを設定
$dataArr = [ 
    'name'      => '',
    'pass'       => '',
    'pass_confirm'       => '',
    'email' => '',
];

//エラーメッセージの定義、初期
$errArr  = [];
foreach ($dataArr as $key => $value){
    $errArr[ $key ] = '';
}

$csrf_token = $csrf->tokenCreate();
$context = [];

$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;
$context['csrf_token'] = $csrf_token;
$template = $twig->loadTemplate('user/view/regist.html.twig');
$template->display( $context );
?>

