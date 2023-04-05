<?php

namespace user\controller;
require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;
use user\model\PDODatabase;
use user\model\Category;
use user\model\Login;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ctg = new Category($db);
$login = new Login;

session_start();

$cateArr = $ctg->getCategories();

$tree = $ctg->buildTree($cateArr);

//　管理者の場合は管理者トップ画面リンクを表示
$login->managerCheck();
$manager_message = $login->manager_message;

$message = "おかえりなさい " . $_SESSION['name'] ."さん";
$context['cateArr'] = $cateArr;
$context = [];

$context['tree'] = $tree;
$context['manager_message'] = $manager_message;
$context['message'] = $message;
$context['cateArr'] = $cateArr;
$template = $twig->loadTemplate('user/view/top.html.twig');
$template->display( $context );