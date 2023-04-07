<?php

namespace manager\controller;
require_once dirname( __FILE__, 3) . '/common/model/Bootstrap.class.php';

use common\model\Bootstrap;
use common\model\PDODatabase;
use manager\model\manager;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$manager = new manager;

session_start();

$userData = $manager->getUserData($_SESSION['user_id']);

// 名前とメアドの変更
if(isset($_POST['name'])){
    $dataArr =[];
    $dataArr = $_POST;
    $userNameRes = $manager->updateUserName($userData[0]['id'], $dataArr['name']);
    $userEmailRes = $manager->updateUserEmail($userData[0]['id'], $dataArr['email']);
} 

$context = [];

$context['userData'] = $userData[0];

$template = $twig->loadTemplate('manager/view/account.html.twig');
$template->display( $context );