<?php

namespace manager\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use user\model\User;
use common\model\Common;
use manager\model\Manager;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User;
$common = new Common;
$manager = new Manager;

if(!empty($_POST['test_edit'])){
    unset($_POST['test_edit']);
    $test_id = $_POST['test_id'];
    $context = $common->getContext();
    // 指定したidのtestテーブル情報を取得
    $test = $user->getTestDataById($test_id);

    // 指定したidのquestionデータの取得
    $questions = $user->getQuestionDataById($test_id);

    $context = $common->getContext();
    $context['test'] = $test[0];
    $context['questions'] = $questions;
    $template = 'manager/view/test_edit.html.twig';

    $template = $twig->loadTemplate($template);
    $template->display( $context );

} elseif (!empty($_POST['test_delete'])){
    unset($_POST['test_delete']);
    $test_id = $_POST['test_id'];
    $template = 'user/view/process_complete.html.twig';
} elseif (!empty($_POST['test_edit_complete'])){  // 編集画面からPOSTされた時
    unset($_POST['test_edit_complete']);
    $test_id = $_POST['test_id'];
    $title = $_POST['title'];  
    $res = $manager->updateTestTitle($test_id, $title);  // testテーブルにタイトルを追加
    unset($_POST['title']);
    unset($_POST['test_id']);
    $keys_post[] = array_keys($_POST);
    $keys_result = array();
    foreach ($keys_post[0] as $key) {
        $key = preg_replace('/\D/', '', $key); // 数字以外の文字列を削除
        $keys_result[] = $key;
    }
    $keys_unique = array_unique($keys_result);
    foreach($keys_unique as $key) {
        $_POST['remove_flg_question_id_'. $key] == 'false';
        $question_id = $key;
        $question = $_POST['question_id_'. $key];
        $answer_no = $_POST['answer_no_id_'. $key];
        $delete_flg = $_POST['remove_flg_question_id_'. $key];
        $res = $manager->updateQuestionDataByQuestionId($question_id, $question, $answer_no, $delete_flg);

    }
    $context = $common->getContext();
    $context['message'] = 'テスト問題の編集が完了しました';
    $context['url_message'] = '管理者トップページへ戻る';

    $template = 'user/view/process_complete.html.twig';

    $template = $twig->loadTemplate($template);
    $template->display( $context );
}
