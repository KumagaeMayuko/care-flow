<?php

namespace manager\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use user\model\User;
use common\model\Common;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User;
$common = new Common;

$test_id = (isset($_GET['id']) === true && preg_match('/^[0-9]+$/', $_GET['id']) === 1) ? $_GET['id'] : '';

$context = $common->getContext();
// 指定したidのtestテーブル情報を取得
$test = $user->getTestDataById($test_id);

// 指定したidのquestionデータの取得
$question = $user->getQuestionDataById($test_id);

$context['test'] = $test[0];
$context['questions'] = $question;

$template = $twig->loadTemplate('manager/view/test_detail.html.twig');
$template->display( $context );