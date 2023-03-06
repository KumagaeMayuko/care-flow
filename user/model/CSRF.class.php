<?php

namespace user\model;

class CSRF
{
    public $db = null;
    public $dbh = null;

    public function __construct()
    {
        session_start();
    }

    public function tokenCreate()
    {
        $csrf_token = mt_rand();
        $_SESSION['csrf_token'] = $csrf_token;
    }
    public function tokenCheck() 
    {
        if (!isset($_POST["token"]) && $_POST["token"] == $_SESSION['csrf_token']) {
            echo "不正なリクエストです";
        }
    }
}