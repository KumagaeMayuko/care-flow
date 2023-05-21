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

$ctg_id = (isset($_GET['id']) === true && preg_match('/^[0-9]+$/', $_GET['id']) === 1) ? $_GET['id'] : '';
// $infos = $info->getInfoCategoryDataByCheckFlg($ctg_id, '0');

// $res = $common->getPagenationData('info', 'delete_flg = 0', $per_page = 5);

// $ctgChildren = $ctg->recursiveGetChildCategories($ctg_id);
// $ctgParent = $ctg->getCategorieById($ctg_id);
// $ctgAll = array_merge($ctgChildren, $ctgParent);
// $infoAll = [];
// foreach($ctgAll as $ctg ) {
//     $ctg_id = $ctg['id'];
    $infoAll = $info->getInfoCategoryData($ctg_id);
//     $infoAll = array_merge($infoAll, $res);
// }

// カテゴリー毎に配列を作成
// $infoAllByCategoryGroup = [];
// foreach ($infoAll as $item) {
//     $ctgName = $item['ctg_name'];
//     if (!array_key_exists($ctgName, $infoAllByCategoryGroup)) {
//         $newArray[$ctgName] = [];
//     }
//     $infoAllByCategoryGroup[$ctgName][] = $item;
// }

$per_page = 5;
$page_num = ceil(count($infoAll)/$per_page);

// GETで現在のページ数を取得する（未入力の場合は1を挿入）
if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
} else {
    $page = 1;
}

// スタートのポジションを計算する
if ($page > 1) {
    // 例：２ページ目の場合は、『(2 × 10) - 10 = 10』
    $start = ($page * $per_page) - $per_page;
} else {
    $start = 0;
}


// infoテーブルから5件のみ取得したい場合
$data = $common->getDataByLimit($ctg_id, $start, $per_page);

if(empty($data[0])){
    $message = '投稿はありません。';
}

// 配列の2番目から5番目の要素を取得
$context = $common->getContext();
$context['page'] = $page;
$context['page_num'] = $page_num;
// $infos = $infoAll[$start:];
$context['infoAllByCategoryGroup'] = $infoAllByCategoryGroup;
$context['infoAll'] = $infoAll;
$context['data'] = $data;
// $context['All'] = $All;
$context['infos'] = array_slice($infoAll, $start, $start+$per_page);
$context['ctg_id'] = $ctg_id;
$context['ctg_id'] = $ctg_id;
$context['message'] = $message;
// $context['infos'] = $infos;

$template = $twig->loadTemplate('info/view/list.html.twig');
$template->display($context);
