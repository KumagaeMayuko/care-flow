<?php

namespace manager\controller;

require_once dirname( __FILE__,3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );

//セッションを使うことを宣言
session_start();

//ログインされていない場合は強制的にログインページにリダイレクト
if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit();
}

//セッション変数をクリア
$_SESSION = [];

//クッキーに登録されているセッションidの情報を削除
if (ini_get("session.use_cookies")) {
  setcookie(session_name(), '', time() - 42000, '/');
}

//セッションを破棄
session_destroy();

$message = "ログアウトしました";
$message = htmlspecialchars($message);

$context = [];
$context['message'] = $message;

$template = $twig->loadTemplate('manager/view/logout.html.twig');
$template->display($context);
?>