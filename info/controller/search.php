<?php

namespace info\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use info\model\Info;
use user\model\Category;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$info = new Info;
$ctg = new Category($db);

$search = [];
$search = $_GET['search'];
unset($_GET['send']);
$res = $info->searchInfoData($search);

$context = [];

$data = [];
$data = $res;
$count = count($data);

        if ($count === 0){
            echo "検索結果はありませんでした";

            $cateArr = $ctg->getCategories();

            $tree = $ctg->buildTree($cateArr);

            $context['cateArr'] = $cateArr;

            $context['tree'] = $tree;

            $template = 'user/view/staff_top.html.twig';

        } else if ($count === 1){
            header('Location:' . Bootstrap::ENTRY_URL .  'info/controller/detail.php?info_id=' . $data[0]['id']  );
        } else if ($count >= 2){    
            $context['infos'] = $data;
            $template = 'info/view/list.html.twig';

        }else if($res === false){
            echo  'エラーが出ています'; 

            $cateArr = $ctg->getCategories();
            $tree = $ctg->buildTree($cateArr);

            $context['cateArr'] = $cateArr;

            $context['tree'] = $tree;
            $template = 'user/view/staff_top.html.twig';
        }

$template = $twig->loadTemplate($template);
$template->display( $context );
