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
$url = 'staff_top.php';
$url_message = 'トップページへ';
$context = [];

$context['message'] = $message;
$context['url'] = $url;
$context['url_message'] = $url_message;
$template = $twig->loadTemplate('user/view/process_complete.html.twig');
$template->display( $context );
?>