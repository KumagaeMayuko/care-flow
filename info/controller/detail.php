<?php

namespace info\controller;

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

$context = $common->getContext();

$info_id = (isset($_GET['info_id']) === true && preg_match('/^[0-9]+$/', $_GET['info_id']) === 1) ? $_GET['info_id'] : '';

$user_id = $_SESSION['user_id'];

// ユーザーが詳細画面へ遷移した際にread_statusテーブルを作成し、既読したことにする
// （read_statusテーブルにデータがなければ、作成）
$res = $info->createReadStatusDataNotExists($info_id, $user_id);

$info_detail = $info->getInfoUserData($info_id);

$context['info_detail'] = $info_detail;
$template = $twig->loadTemplate('info/view/detail.html.twig');
$template->display($context);
