<?php

namespace user\controller;

require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;
use user\model\Login;
use user\model\CSRF;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );


$csrf = new CSRF();
$csrf->tokenCreate();
$csrf->tokenCheck();
session_destroy();
$csrf_token = $_SESSION['csrf_token'];

$login = new Login();
$login->checkSession();
$message = $login->message;

$context = [];
$context['message'] = $message;
$context['csrf_token'] = $csrf_token;

$template = $twig->loadTemplate( 'user/view/login.html.twig' );
$template->display($context);
?>