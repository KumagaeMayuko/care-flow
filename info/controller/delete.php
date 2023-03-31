<?php

namespace info\controller;

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

$context = [];

if (!isset($_POST['delete_complete']) === true) {
    $mode = 'confirm';
} else {
    $mode = 'complete';
}

switch($mode){
    case 'confirm':
        $info_id = (isset($_GET['id']) === true && preg_match('/^[0-9]+$/', $_GET['id']) === 1) ? $_GET['id'] : '';
        $info_detail = $info->getInfoUserData($info_id);
        $context['info_detail'] = $info_detail[0];
        $template = 'info/view/delete.html.twig';
        break;
    case 'complete':
        $dataArr = $_POST;

        // 画像の削除
        // $file_path = (Bootstrap::IMAGE_URL) . $dataArr['image'];
        if(isset($dataArr['image'])){
            $file_path = '../../images/' . $dataArr['image'];
            $result = unlink($file_path);
        }
        $res = $info->deleteInfoData($dataArr['id']);
        $infoCtgRes = $info->deleteInfoCategoryData($dataArr['id']);
        if($res === true && $infoCtgRes === true){
            $template = 'info/view/delete_complete.html.twig';
        } 
        break;
}

$template = $twig->loadTemplate($template);
$template->display($context);