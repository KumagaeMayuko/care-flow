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

$userData = $manager->getUsersData();
$infos = $manager->getInfosData();
$context = $common->getContext();

// var_dump($userData);
$readStatusUsers = $manager->getUserReadStatusData();
$alreadyReadInfos = array_column($readStatusUsers, 'read_status_id', 'user_id');
// $userData['read_status_id'] = $alreadyReadInfos;
// var_dump($readStatusUsers);
// var_dump($readStatusUsers);

function groupBy($array, $key1, $key2) {
    // var_dump($array[0][$key2]);
    $result = [];
    foreach($array as $val) {
        if(!isset($result[$val[$key1]])) {
            $result[$val[$key1]] = [];
            $result[$val[$key1]][$key2] = $val[$key2];
        }
        $result[$val[$key1]]['readed'][] = $val['info_id'];
    }
    return $result;
}

$infoAll = array_column($infos,'id');
$readedByUser = groupBy($readStatusUsers, 'name', 'user_id');
foreach($readedByUser as &$value){
    $readed = $value['readed'];
    $unreadInfos = array_diff($infoAll, $readed);
    $value['unreaded'] = $unreadInfos;
}

$context['readedByUser'] = $readedByUser;

$template = $twig->loadTemplate('manager/view/read_check_list.html.twig');
$template->display( $context );