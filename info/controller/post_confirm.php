<?php

namespace info\controller;

require_once dirname(__FILE__,3 ) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\Category;
use info\model\Info;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ctg = new Category($db);
$info = new Info();

//テンプレート指定
$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR) ;
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
]);

$context= [];

//モード判定（どの画面から来たかの判断）
//登録画面から来た場合
if (isset($_POST['confirm']) === true) {
    $mode = 'confirm';
}
//戻る場合
if (isset($_POST['back']) === true){
    $mode = 'back';
}
// 登録完了
if (isset($_POST['complete']) === true){
    $mode = 'complete';
}
//ボタンのモードよって処理をかえる
switch($mode){
    case 'confirm': //新規登録

        unset($_POST['confirm']);
        $dataArr = $_POST;
        //エラーメッセージの配列作成
        $errArr = $info->errorCheck($dataArr);
        $err_check = $info->getErrorFlg();

        if(isset($_FILES['image'])){
            $tmp_image = $_FILES['image'];
        } elseif ($_POST['image_name']) {
             $tmp_image = $_POST['image_name'];
        }

        if (isset($tmp_image) && $tmp_image['error'] === 0 && $tmp_image['size'] !== 0) {

            if (is_uploaded_file($tmp_image['tmp_name']) === true) {
                $image_info = getimagesize($tmp_image['tmp_name']);
                $image_mime = $image_info['mime'];
                $now = time();
                if ($tmp_image['size'] > 1048576) {
                    echo 'アップロードできる画像のサイズは、1MBまでです';
                } elseif (preg_match('/^image¥/jpeg$/', $image_mime) === 0) {
                    echo 'アップロードできる画像の形式は、JPEG形式だけです';
                } elseif (move_uploaded_file($tmp_image['tmp_name'], '../../images/tmp/upload_' . $now . '.jpeg') === true) {         
                   $image_name = "upload_" . $now . '.jpeg';
                   $context['image_name'] = $image_name;
                }
            }
        }

        // 新規で画像選択 → 確認画面からback → 画像選択なしで確認画面に遷移 の場合
        if(isset($dataArr['image_name']) && isset($_FILES['image']) && $_FILES['image']['size'] !== 0){
            $file_path = '../../images/tmp/' . $dataArr['image_name'];
            unset($dataArr['image_name']);
            unlink($file_path);
        }


        //エラーなければconfirm.tpl あるとregist.tpl
        $template = ($err_check === true)? 'info/view/post_confirm.html.twig':'info/view/post.html.twig';
        break;

    case 'back': //戻ってきた時
                // ポストされたデータを元に戻すので、$dataArrにいれる
        $dataArr = $_POST;
        unset($dataArr['back']) ;
        //エラーも定義しておかないと、Undefinedエラーがでる
        foreach($dataArr as $key => $value){
            $errArr[$key] = '';
        }

        $cateArr = $ctg->getCategories();
        $tree = $ctg->buildTree($cateArr);
        $context['tree'] = $tree;

        $template = 'info/view/post.html.twig';
    break;

    case 'complete': //登録完了
        $dataArr = $_POST;

        // ↓この情報はいらないので外しておく
        unset($dataArr['complete']);
        unset($dataArr['csrf_token']);
        $dataArr['user_id'] = '10'; 

        // tmpファイルからimagesファイルへ画像を移行
        $tmp_file_path = '../../images/tmp/' . $dataArr['image_name'];
        $images_file_path = '../../images/' . $dataArr['image_name'];
        copy( $tmp_file_path , $images_file_path);
        unlink($tmp_file_path);
        
        $dataArr['image'] = $dataArr['image_name'];
        unset($dataArr['image_name']);  
        $column = '';
        $insData = '';

        $cateArr = $ctg->getCategories();

        $cat_index = '';
        foreach ($cateArr as $key => $value){
            if ($value['ctg_name'] == $dataArr['ctg_name']) {
                $cat_index = $key;
            }
        }

        $ctg_id = $cateArr[$cat_index]['id'];

        unset($dataArr['ctg_name']); 
        if($dataArr['check_flg'] == 'on'){
            $dataArr['check_flg'] = true;
        } else {
           $dataArr['check_flg'] = false; 
        }
        $now = time();
        $dataArr['create_at'] = date("Y/m/d H:i:s", $now);
        $res = $db->insert('info', $dataArr);
       
        $template = 'info/view/post_success.html.twig'; 
    }

    $context['dataArr'] = $dataArr;
    $context['errArr'] = $errArr;
    $template = $twig->loadTemplate($template);
    $template->display($context);