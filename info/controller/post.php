<?php
namespace info\controller;

require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\CSRF;
use common\model\Category;

// テンプレート指定
$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig   = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$csrf = new CSRF();
$ctg = new Category($db);

// 初期データを設定
$dataArr = [ 
    'title'      => '',
    'ctg_name'       => '',
    'content'       => '',
    'image' => '',
    'check_flg' => '',
];

// エラーメッセージの定義、初期
$errArr  = [];
foreach ($dataArr as $key => $value){
    $errArr[ $key ] = '';
}

$cateArr = $ctg->getCategories();
$tree = $ctg->buildTree($cateArr);

$csrf->tokenCreate();

// session_start();
$csrf_token = $_SESSION['csrf_token'];
$context = [];

$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;
$context['tree'] = $tree;
$context['csrf_token'] = $csrf_token;
$template = $twig->loadTemplate('info/view/post.html.twig');
$template->display( $context );
