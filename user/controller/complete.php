<?php

namespace user\controller;

require_once dirname( __FILE__, 2 ) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );

$message = '登録が完了しました';
$url = 'login.php';
$url_message = 'ログイン画面へ';
$context = [];

$context['message'] = $message;
$context['url'] = $url;
$context['url_message'] = $url_message;
$template = $twig->loadTemplate('user/view/process_complete.html.twig');
$template->display( $context );
?>