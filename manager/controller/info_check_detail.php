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

$check_flg = '1';
$checkPosts = $manager->getInfosDataByCheckFlg($check_flg);
foreach($checkPosts as $v){
    if($_GET['info_id'] == $v['id']){
        $post = $v;
    }
}
$context = [];
$context['post'] = $post;

$template = $twig->loadTemplate('manager/view/info_check_detail.html.twig');
$template->display( $context );