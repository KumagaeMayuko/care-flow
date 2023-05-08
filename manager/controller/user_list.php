<?php

namespace manager\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\Common;
use manager\model\Manager;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$manager = new Manager;
$common = new Common;

$users = $manager->getUsersData();

$context = $common->getContext();

$res = $manager->conditionalBranch();

$context['users'] = $users;

$template = $twig->loadTemplate('manager/view/user_list.html.twig');
$template->display( $context );