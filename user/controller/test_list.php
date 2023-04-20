<?php

namespace user\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use user\model\User;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User;

// testのタイトルを一覧画面で表示するためにデータを取得
$test = $user->getTestData();

$context = [];

$context['tests'] = $test;

$template = $twig->loadTemplate('user/view/test_list.html.twig');
$template->display( $context );