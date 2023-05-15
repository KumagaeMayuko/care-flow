<?php

namespace manager\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\Common;
use manager\model\manager;
use info\model\info;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$manager = new manager;
$common = new Common;
$info = new info;

$user_id = $_GET['user_id'];

// 未読のタイトルを全て取得
$unreadInfos = $manager->getUnReadInfos($user_id);

// nameを取得
$readStatusByUserId = $manager->getUserReadStatusDataByUserId($user_id);

$context = $common->getContext();
$context['unreadInfos'] = $unreadInfos;

$name = $readStatusByUserId[0]['name'];
$context['name'] = $name;

$template = $twig->loadTemplate('manager/view/read_check_detail.html.twig');
$template->display( $context );