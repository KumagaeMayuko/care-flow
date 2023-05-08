<?php

namespace manager\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\Category;
use common\model\Login;
use manager\model\manager;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ctg = new Category($db);
$login = new Login;
$manager = new manager;

$context = [];

$message = '作成完了しました';
$url = "top.php";
$url_message = 'トップページへ戻る';

$context['message'] = $message;
// $context['url'] = $url;
// $context['url_message'] = $url_message;

$template = $twig->loadTemplate('user/view/process_complete.html.twig');
$template->display( $context );