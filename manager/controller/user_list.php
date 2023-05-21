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

// GETで現在のページ数を取得する（未入力の場合は1を挿入）
// if (isset($_GET['page'])) {
// 	$page = (int)$_GET['page'];
// } else {
// 	$page = 1;
// }

// // スタートのポジションを計算する
// if ($page > 1) {
// 	// 例：２ページ目の場合は、『(2 × 10) - 10 = 10』
// 	$start = ($page * 5) - 5;
// } else {
// 	$start = 0;
// }

// $dbh = mysqli_connect(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME);

// // userの総数を取得
// $query = "SELECT COUNT( * ) FROM user WHERE delete_flg = 0"; 
// $res = mysqli_query($dbh, $query);
// $data_tmp = [];
// while ($row = mysqli_fetch_assoc($res)) {
//     array_push($data_tmp, $row);
// }
// $data_tmp_n = (int)$data_tmp[0]["COUNT( * )"];
// $page_num = ceil($data_tmp_n/5);

// // userテーブルから5件のデータを取得する
// $query = "SELECT * FROM user WHERE delete_flg = 0 LIMIT {$start}, 5 "; 
// $res = mysqli_query($dbh, $query);
// $data = [];
// while ($row = mysqli_fetch_assoc($res)) {
//     array_push($data, $row);
// }

// mysqli_close($dbh);
$res = $common->getPagenationData('user', 'delete_flg = 0');

$data = $res['data'];
$page_num = $res['page_num'];
$page = $res['page'];

$context = $common->getContext();

$res = $manager->conditionalBranch();

$context['users'] = $data;
$context['page'] = $page;
$context['page_num'] = $page_num;

$template = $twig->loadTemplate('manager/view/user_list.html.twig');
$template->display( $context );