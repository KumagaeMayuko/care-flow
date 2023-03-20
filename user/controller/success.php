<?php

namespace user\controller;

require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;

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

//ログインされている場合は表示用メッセージを編集
$message = "こんにちは" . $_SESSION['name']."さん";
$message = htmlspecialchars($message);

$context = [];
$context['message'] = $message;

$template = $twig->loadTemplate('user/view/success.html.twig');
$template->display($context);
?>