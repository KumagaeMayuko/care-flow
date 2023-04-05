<?php

namespace manager\controller;

require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\Login;
use common\model\CSRF;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );

$login = new Login;
$login->checkSession();

$csrf = new CSRF();
$csrf->tokenCreate();
$csrf->tokenCheck();
session_destroy();
$csrf_token = $_SESSION['csrf_token'];
$message = $login->message;

$context = [];
$context['message'] = $message;
$context['csrf_token'] = $csrf_token;

$template = $twig->loadTemplate( 'manager/view/login.html.twig' );
$template->display($context);
