<?php

namespace user\controller;
ini_set('display_errors', "On");
require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\PDODatabase;
use user\model\Common;
use user\model\Bootstrap;
use user\model\CSRF;

//テンプレート指定
$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR) ;
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
]);

// $db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS,
// Bootstrap::DB_NAME);
$common = new Common();
$csrf = new CSRF();

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
                    //データを受け継ぐ
                    //↓この情報は入力には必要ない
        unset($_POST['confirm']);
        $dataArr = $_POST;

        //エラーメッセージの配列作成
        $errArr = $common->errorCheck($dataArr);
        $err_check = $common->getErrorFlg();
        $csrf->tokenCheck();
        session_destroy();
        // err_check = false→エラーがありますよ！
        // err_check = true →エラーがないですよ！

        //エラーなければconfirm.tpl あるとregist.tpl
        $template = ($err_check === true)? 'user/view/confirm.html.twig':'user/view/regist.html.twig';
        break;

    case 'back': //戻ってきた時
                // ポストされたデータを元に戻すので、$dataArrにいれる
        $dataArr = $_POST;
        unset($dataArr['back']) ;
        //エラーも定義しておかないと、Undefinedエラーがでる
        foreach($dataArr as $key => $value) {
            $errArr[$key] = '';
        }

        $template = 'user/view/regist.html.twig';
    break;

    case 'complete': //登録完了
        $dataArr = $_POST;
        $hash = password_hash($dataArr['pass'], PASSWORD_BCRYPT);
        $pass_verify = password_verify($dataArr['pass'], $hash);
        if (password_verify($dataArr['pass'], $hash)){
            echo'';
        } else {
            echo 'パスワードの暗号化に失敗しました';
        }
        $dataArr['pass'] = $hash;
        $dataArr['regist_at'] = date('Y-m-d H:i:s', time());
        // ↓この情報はいらないので外しておく
        unset($dataArr['complete']);
        unset($dataArr['pass_confirm']);
        unset($dataArr['csrf_token']);
        $res = $common->db->insert("user", $dataArr);

        if ($res === true){
            //登録成功時は完成ページへ
            header('Location:' . Bootstrap::ENTRY_URL .  'controller/complete.php');
            exit ();
        } else {
            //登録失敗時は登録画面に戻る
            $template = 'user/view/regist.html.twig';

            foreach ($dataArr as $key => $value) {
                $errArr[$key] = '';
            }
        }
        break;
    }

    $context['dataArr'] = $dataArr;
    $context['errArr'] = $errArr;
    $template = $twig->loadTemplate($template);
    $template->display($context);
?>

