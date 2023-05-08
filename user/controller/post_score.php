<?php

namespace user\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use common\model\Common;
use user\model\User;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User;
$common = new Common;

$context = $common->getContext();

$user_id = $_SESSION['user_id'];

// testのタイトルを一覧画面で表示するためにデータを取得
$tests = $user->getTestData();

// user_answerテーブルからデータを取得
$userAnswers = $user->getUserAnswerDataByUserId($user_id);

// created_atで配列をソート
usort($userAnswers, function($a, $b) {
    return $a['created_at'] <=> $b['created_at'];
});

// 新しい配列を作成（created_atが同じものを配列に格納）
$userAnswersByCreatedAt = [];
foreach ($userAnswers as $key => $value) {
    if ($key == 0 || $value['created_at'] != $userAnswers[$key-1]['created_at']) {
        // 新しいcreated_atの値を持つ要素を追加
        array_push($userAnswersByCreatedAt, array($value));
    } else {
        // 同じcreated_atの値を持つ要素を既存の配列に追加
        array_push($userAnswersByCreatedAt[count($userAnswersByCreatedAt)-1], $value);
    }
}

$userAnswersByCreatedAt = [];
foreach ($userAnswers as $key => $value) {
    $userAnswersByCreatedAt[$value['created_at']] = $value;
}
$userAnswersByCreatedAt = array_values($userAnswersByCreatedAt);

$questions = $user->getUQuestionData();

$allData = $user->getTestScoreByUserId($user_id);
$test = $user->getUncompletedTestData($user_id);

// $uniqueBytitle = array_unique($allData, );

$uniqueBytitle = [];
foreach ($allData as $data) {
    $title = $data['title'];
    if (!array_key_exists($title, $uniqueBytitle)) { // keyが存在しなかった場合
        $uniqueBytitle[$title] = [];
    }
    $uniqueBytitle[$title][] = $data;
}

$context['uniqueBytitle'] = $uniqueBytitle;
$context['test'] = $test;
$template = $twig->loadTemplate('user/view/post_score.html.twig');
$template->display( $context );