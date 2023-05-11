<?php

namespace user\controller;

require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;
use user\model\Login;
use common\model\CSRF;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );

session_start();

$csrf = new CSRF();
$login = new Login();

$context = [];

if (isset($_POST['submit'])) {
    unset($_POST['submit']);
    $res = $csrf->tokenCheck();
    $login->loginByCsrfToken($res);
    $context['message'] = $login->message;
}

$csrf_token = $csrf->tokenCreate();
$context['csrf_token'] = $csrf_token;

$template = $twig->loadTemplate( 'user/view/login.html.twig');
$template->display($context);

?>