<?php

namespace user\controller;
require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;
use user\model\PDODatabase;
use user\model\Category;
use user\model\Login;
use common\model\Common;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ctg = new Category($db);
$login = new Login;
$common = new Common;

$cateArr = $ctg->getCategories();
$tree = $ctg->buildTree($cateArr);

$context = $common->getContext();

$context['cateArr'] = $cateArr;
$context['tree'] = $tree;

$template = $twig->loadTemplate('user/view/top.html.twig');

$template->display( $context );