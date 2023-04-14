<?php

namespace manager\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use manager\model\manager;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$manager = new manager;

$check_flg = '1';
$checkPosts = $manager->getInfosDataByCheckFlg($check_flg);

$context = [];
$context['checkPosts'] = $checkPosts;

$template = $twig->loadTemplate('manager/view/info_check_list.html.twig');
$template->display( $context );