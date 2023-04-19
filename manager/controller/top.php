<?php

namespace manager\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\Category;
use common\model\Login;
use manager\model\manager;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ctg = new Category($db);
$login = new Login;
$manager = new manager;

if(!empty($_POST['ctg_name'])){
    $ctg_name = $_POST['ctg_name'];
    $parent_id = $_POST['parent_id'];
    $res = $manager->insertCategoryData($ctg_name, $parent_id);
    $_POST = []; 
    header('Location:top.php');
    exit;
} elseif(!empty($_POST['id'])){
    $id = $_POST['id'];
    $res = $manager->getCategoryByParentId($id);
    if(!empty($res)){ // 削除するカテゴリーがtopの親だった場合
        $parent_res = $manager->updateCategoryDataByDeleteFlg($id);
        $children_res = $manager->updateCategoryDataByParentId($id);
        $_POST = [];
        header('Location:top.php');
        exit;
    } else { // 子カテゴリーが存在する場合
        $children_res = $manager->updateCategoryDataByParentId($id);
        $_POST = []; 
        header('Location:top.php');
        exit; 
    }
}

session_start();

$cateArr = $ctg->getCategories();

$tree = $ctg->buildTree($cateArr);

//　管理者の場合は管理者トップ画面リンクを表示
$login->managerCheck();
$manager_message = $login->manager_message;

$message = "おかえりなさい " . $_SESSION['name'] ."さん";
$context['cateArr'] = $cateArr;
$context = [];

$context['tree'] = $tree;
$context['manager_message'] = $manager_message;
$context['message'] = $message;
$context['cateArr'] = $cateArr;
$template = $twig->loadTemplate('manager/view/top.html.twig');
$template->display( $context );