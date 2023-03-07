<?php

namespace user\controller;

require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;
use user\model\Database;
use user\model\Login;


// $db = new Database(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME);

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );

$login = new Login();
$login->checkSession();

$message = $login->message;

$context = [];
$context['message'] = $message;

$template = $twig->loadTemplate( 'user/view/login.html.twig' );
$template->display($context);
?>