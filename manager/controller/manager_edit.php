<?php

namespace manager\controller;

require_once dirname(__FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\Category;
use common\model\Common;
use info\model\Info;


$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ctg = new Category($db);
$info = new Info();
$common = new Common();

// テンプレート指定
$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR);
$twig = new \Twig_Environment($loader, [
    'cache' => Bootstrap::CACHE_DIR
]);


$info_id = $_POST['info_id'];
$info_user = $info->getInfoUserData($info_id);

$cateArr = $ctg->getCategories();
$tree = $ctg->buildTree($cateArr);

$context = $common->getContext();
$context['info_user'] = $info_user[0];
$context['tree'] = $tree;
$template = $twig->loadTemplate('manager/view/manager_edit.html.twig');
$template->display($context);