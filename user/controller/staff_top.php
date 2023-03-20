<?php

namespace user\controller;
require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;
use user\model\PDODatabase;
use user\model\Category;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ctg = new Category($db);

session_start();

$cateArr = $ctg->getCategories();

$tree = $ctg->buildTree($cateArr);

$message = "おかえりなさい " . $_SESSION['name'] ."さん";
$context['cateArr'] = $cateArr;
$context = [];

$context['tree'] = $tree;
$context['message'] = $message;
$context['cateArr'] = $cateArr;
$template = $twig->loadTemplate('user/view/staff_top.html.twig');
$template->display( $context );