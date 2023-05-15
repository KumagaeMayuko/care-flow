<?php

namespace manager\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\Category;
use common\model\Login;
use common\model\Common;
use manager\model\manager;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$ctg = new Category($db);
$login = new Login;
$common = new Common;
$manager = new manager;

// 問題文と答えがpostされた時
if(!empty($_POST) && isset($_POST['title'])){
    $title = $_POST['title'];  // titleのみ配列から削除
    $lastInsTestId = $manager->insertTestData($title);  // testテーブルにタイトルを追加しレコード作成し、IDを取得
    unset($_POST['title']); // titleのみ配列から削除
    $count = 1;
    while (isset($_POST['question_' . $count])) {  // 問題がある分だけ問題と答えを組み合わせたものを配列に格納
        $test = [];
        $test["question_".$count] = $_POST["question_".$count];
        $test["answer_no_".$count] = $_POST["answer_no_".$count];
        $insQuestionData = $manager->insertQuestionData($lastInsTestId, $test["question_".$count], $test["answer_no_".$count]);
        $count ++;
    }
    unset($_POST);
    header("Location: test_success.php");

    exit();
} elseif (!empty($_POST)){
    $message = 'タイトルを入力してください';
}

$context = $common->getContext();

$context['message'] = $message;

$template = $twig->loadTemplate('manager/view/test_create.html.twig');
$template->display( $context );