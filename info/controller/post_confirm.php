<?php

namespace info\controller;

require_once dirname(__FILE__,3 ) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\Category;
use common\model\Common;
use info\model\Info;
use common\model\CSRF;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ctg = new Category($db);
$info = new Info();
$common = new Common();

//テンプレート指定
$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR) ;
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
]);

$context = $common->getContext();

//モード判定（どの画面から来たかの判断）
//登録画面から来た場合
if (isset($_POST['confirm']) === true || isset($_POST['edit_confirm']) === true) {
    $mode = 'confirm';
}
//戻る場合
if (isset($_POST['back']) === true){
    $mode = 'back';
}
// 登録完了
if (isset($_POST['complete']) === true || isset($_POST['manager_edit_confirm']) === true){
    $mode = 'complete';
}
//ボタンのモードよって処理をかえる
switch($mode){
    case 'confirm': //新規登録
        unset($_POST['confirm']);
        $csrf = new CSRF();
        // csrf_tokenのチェック
        $res = $csrf->tokenCheck();
        // csrf_tokenのチェックの結果、falseだった場合
        if ($res == false) {
            header("Location:post.php");
        } 
        $dataArr = $_POST;
        //エラーメッセージの配列作成
        $errArr = $info->errorCheck($dataArr);
        $err_check = $info->getErrorFlg();

        // 戻らずに投稿（1回目）
        if(isset($_FILES['image'])){
            $tmp_image = $_FILES['image'];
        // １回戻ってからの投稿
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

        //　編集画面から来た場合
        if(isset($dataArr['edit_confirm'])){
           $template = 'info/view/edit_detail.html.twig'; 
        } else {
           $template = 'info/view/post.html.twig'; 
        }

        unset($dataArr['back']) ;
        //エラーも定義しておかないと、Undefinedエラーがでる
        foreach($dataArr as $key => $value){
            $errArr[$key] = '';
        }

        $cateArr = $ctg->getCategories();
        $tree = $ctg->buildTree($cateArr);

        $info_id = $dataArr['id'];
        $info_user = $info->getInfoUserData($info_id);

        $context['info_user'] = $info_user[0];
        $context['tree'] = $tree;

    break;

    case 'complete': //登録完了
        $dataArr = $_POST;
        // ↓この情報はいらないので外しておく
        unset($dataArr['complete']);
        unset($dataArr['csrf_token']); 

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
            $dataArr['check_flg'] = '1';
        } else {
           $dataArr['check_flg'] = '0'; 
        }
        $now = time();
        $dataArr['create_at'] = date("Y/m/d H:i:s", $now);

        //　アップデートする場合
        if(isset($_POST['edit_confirm'])){ 
            unset($dataArr['edit_confirm']);
            unset($dataArr['create_at']);
            $dataArr['update_at'] = date("Y/m/d H:i:s", $now);
            $insDataArr = [];
            $insDataArr = $dataArr; 
            unset($insDataArr['id']); 
            $res = $info->updateInfoData($dataArr['id'], $insDataArr);

            // info_categoryテーブルの既にあるレコードの削除
            $deleteRes = $info->deleteInfoCategoryData($dataArr['id']);

            // 新たにinfo_categoryテーブルのレコード作成
            $insdata = [
                'info_id' => $dataArr['id'],
                'ctg_id' => $ctg_id
            ];
            $info->infoCategoryInsert($insdata);
            $template = 'info/view/post_success.html.twig'; 
            // 管理者が確認し投稿する場合
        } else if(isset($_POST['manager_edit_confirm'])){
            unset($dataArr['manager_edit_confirm']);
            unset($dataArr['create_at']);
            $dataArr['update_at'] = date("Y/m/d H:i:s", $now);
            $insDataArr = [];
            $insDataArr = $dataArr; 
            $insDataArr['check_flg'] = '0';
            unset($insDataArr['id']); 
            $res = $info->updateInfoData($dataArr['id'], $insDataArr);

            // info_categoryテーブルの既にあるレコードの削除
            $deleteRes = $info->deleteInfoCategoryData($dataArr['id']);

            // 新たにinfo_categoryテーブルのレコード作成
            $insdata = [
                'info_id' => $dataArr['id'],
                'ctg_id' => $ctg_id
            ];
            $info->infoCategoryInsert($insdata); 

            $template = 'manager/view/manager_post_success.html.twig';
        } else {
            $dataArr['user_id'] = $_SESSION['user_id'];
            $res = $db->insert('info', $dataArr); 

            $infoUserRes = $info->getInfoUserData($dataArr['id']);

            //　info_categoryテーブル作成
            $info_id = $db->dbh->lastInsertId();
            $insdata = [
                'info_id' => $info_id,
                'ctg_id' => $ctg_id
            ];
            $info->infoCategoryInsert($insdata);
            $template = 'info/view/post_success.html.twig'; 
        }

    }

    $context['dataArr'] = $dataArr;
    $context['errArr'] = $errArr;
    $template = $twig->loadTemplate($template);
    $template->display($context);