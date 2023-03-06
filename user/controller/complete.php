<?php

namespace user\controller;

require_once dirname( __FILE__, 2 ) . '/model/Bootstrap.class.php';

use user\model\Bootstrap;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );

$template = $twig->loadTemplate( 'user/view/complete.html.twig' );
$template->display( [] );
