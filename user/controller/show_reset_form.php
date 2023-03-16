<?php
namespace user\controller;
ini_set('display_errors', 1);

require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Common;
use user\model\Bootstrap;
use user\model\CSRF;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$common = new Common();
$csrf = new CSRF();

// クエリからtokenを取得
$passwordResetToken = $_GET['token'];

// tokenに合致するユーザーを取得
$table = 'password_resets';
$col = '';
$where = 'token = ?'; 
$arrVal = [$passwordResetToken];

$passwordResetUser = $common->db->select($table, $col, $where, $arrVal);
// 合致するユーザーがいなければ無効なトークンなので、処理を中断
if (!$passwordResetUser) {
    exit('無効なURLです');
}

// 今回はtokenの有効期間を24時間とする
$tokenValidPeriod = (new \DateTime())->modify("24 hour")->format('Y-m-d H:i:s');
// パスワードの変更リクエストが24時間以上前の場合、有効期限切れとする
var_dump($passwordResetUser[0]['token_sent_at']);
if ($passwordResetUser[0]['token_sent_at'] > $tokenValidPeriod) {
    exit('有効期限切れです');
}

// // formに埋め込むcsrf tokenの生成
// if (empty($_SESSION['csrf_token'])) {
//     $csrf->tokenCreate();
//     $conf_token = $_SESSION['csrf_token'];
// }

// $csrf_token = $_SESSION['csrf_token'];

$context = [];
$context['passwordResetToken'] = $passwordResetToken;
// $context['csrf_token'] = $conf_token;

$template = $twig->loadTemplate('user/view/reset_form.html.twig');
$template->display($context);
?>