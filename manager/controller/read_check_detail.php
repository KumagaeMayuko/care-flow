<?php

namespace manager\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use manager\model\manager;
use info\model\info;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$manager = new manager;
$info = new info;

$user_id = $_GET['user_id'];

$infos = $manager->getInfosData();
$readStatusByUserId = $manager->getUserReadStatusDataByUserId($user_id);

$context = [];

// 全てのinfo_idから既読したinfo_id,titleを取得
$alreadyReadInfos = array_column($readStatusByUserId, 'title', 'info_id'); // 配列から'id''title'キーの値を取り出して新しい配列を作成
$infoAll = array_column($infos, 'title', 'id');
$unreadInfos = array_diff($infoAll, $alreadyReadInfos); // $infoAllから$alreadyReadInfoIdを取り除いた配列を作る
$context['unreadInfos'] = $unreadInfos;


$name = $readStatusByUserId[0]['name'];
$context['name'] = $name;

$template = $twig->loadTemplate('manager/view/read_check_detail.html.twig');
$template->display( $context );