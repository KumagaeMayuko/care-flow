<?php

namespace info\controller;

ini_set('display_errors', 'On');

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

$info_id = (isset($_GET['info_id']) === true && preg_match('/^[0-9]+$/', $_GET['info_id']) === 1) ? $_GET['info_id'] : '';

$info_detail = $info->getInfoUserData($info_id);

$context = [];
$context['info_detail'] = $info_detail;
$template = $twig->loadTemplate('info/view/detail.html.twig');
$template->display($context);
