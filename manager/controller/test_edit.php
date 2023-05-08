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

if(!empty($_POST['test_edit'])){  //編集画面
    unset($_POST['test_edit']);
    $test_id = $_POST['test_id'];
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

} elseif (!empty($_POST['test_delete'])){ //detail画面から削除ボタンが押された時
    unset($_POST['test_delete']);
    $test_id = $_POST['test_id'];
    // questionの問題全体（test_id）delete_flgを１へ変更
    $delete_flg = '1';
    $questionRes = $manager->updateQuestionDataByTestId($test_id, $delete_flg); 
    // testのdelete_flgを１へ変更
    $testRes = $manager->updateTestDeleteFlg($test_id, $delete_flg); 
    // testのタイトルを一覧画面で表示するためにデータを取得
    $tests = $manager->getTestData();
    $context = $common->getContext();
    $context['tests'] = $tests;
    $template = 'manager/view/test_list.html.twig';

    $template = $twig->loadTemplate($template);
    $template->display( $context );

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
        $question_id = $key;
        $question = $_POST['question_id_'. $key];
        $answer_no = $_POST['answer_no_id_'. $key];
        $newQuestion = $_POST['question_'. $key];
        $newAnswer_no = $_POST['answer_no_'. $key];
        // 編集で問題文を削除した場合にdelete_flgを１に変更
        if(isset($_POST['remove_flg_question_id_'. $key]) && $_POST['remove_flg_question_id_'. $key] == '1'){
            $delete_flg = '1';
            $res = $manager->updateQuestionDataByDeleteFlg($question_id, $delete_flg);
        }
        // 既存の問題文を更新
        if(isset($question) && isset($answer_no)){
            $quesitonUpdataRes = $manager->updateQuestionDataByQuestionId($question_id, $question, $answer_no);
        }   
        // 問題文を登録
        if(isset($newQuestion) && isset($newAnswer_no)){
            $insQuestionData = $manager->insertQuestionData($test_id, $newQuestion, $newAnswer_no);
        }
    }
    $context = $common->getContext();
    $context['message'] = 'テスト問題の編集が完了しました';
    $context['url_message'] = '管理者トップページへ戻る';

    $template = 'user/view/process_complete.html.twig';

    $template = $twig->loadTemplate($template);
    $template->display( $context );
}
