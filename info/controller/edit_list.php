<?php

namespace info\controller;

require_once dirname(__FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\Category;
use info\model\Info;


$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ctg = new Category($db);
$info = new Info();

// テンプレート指定
$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig_Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);

session_start();
$user_id = $_SESSION['user_id'];
$user_info = $info->getUserInfoData($user_id);
var_dump($user_info);

$context = [];
$context['user_info'] = $user_info;
$template = $twig->loadTemplate('info/view/edit_list.html.twig');
$template->display($context);