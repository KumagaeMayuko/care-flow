<?php

namespace manager\controller;

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
$cateArr = $ctg->getCategories();
$tree = $ctg->buildTree($cateArr);

$info_id = $_POST['id'];
$dataArr = $_POST;
unset($dataArr['submit']);
unset($dataArr['csrf_token']);
$updateInfoData = $info->updateInfoData($info_id, $dataArr);


// $info_id = $_POST['info_id'];
// $info_user = $info->getInfoUserData($info_id);


// $context = [];
// $context['info_user'] = $info_user[0];
// $context['tree'] = $tree;
// $template = $twig->loadTemplate('manager/view/manager_edit.html.twig');
// $template->display($context);