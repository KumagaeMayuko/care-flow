<?php

namespace user\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use user\model\User;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User;

session_start();
$user_id = $_SESSION['user_id'];

$test_id = (isset($_GET['id']) === true && preg_match('/^[0-9]+$/', $_GET['id']) === 1) ? $_GET['id'] : '';

// 指定したidのtestテーブル情報を取得
$test = $user->getTestDataById($test_id);

// 指定したidのquestionデータの取得
$question = $user->getQuestionDataById($test_id);

$context = [];

// テスト受講した場合（post送信された場合）
if(!empty($_POST)){
    $now = time();
    $created_at = date("Y/m/d H:i:s", $now);
    unset($_POST['submit']);
    foreach($_POST as $key => $value){
        $question_id = $key;
        $choice_no = $value;
        // user_answerテーブルにuserの回答をinsert
        // $res = $user->insertUserAnswer($user_id, $question_id, $choice_no, $created_at);
    }
    $user_answer =  array_values($_POST);
    $question_ids =  array_keys($_POST);
    $questions = $user->getUQuestionDataByQuestionId($question_ids);
    $questions = array_map(function($a, $b) {
        $a['user_answer'] = $b;
        return $a;
    }, $questions, $user_answer);

    $context['questions'] = $questions;
    $template = 'user/view/test_answer.html.twig';
    unset($_POST);
} else {
    $context['test'] = $test[0];
    $context['questions'] = $question;
    $template = 'user/view/test_detail.html.twig';
}

$template = $twig->loadTemplate($template);
$template->display( $context );