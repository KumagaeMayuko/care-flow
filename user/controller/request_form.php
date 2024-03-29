<?php

namespace user\controller;

require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;
use common\model\CSRF;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );

session_start();
$context = [];

$csrf = new CSRF();
$csrf_token = $csrf->tokenCreate();

$context['csrf_token'] = $csrf_token;

$template = $twig->loadTemplate('user/view/request_form.html.twig');
$template->display($context);
?>