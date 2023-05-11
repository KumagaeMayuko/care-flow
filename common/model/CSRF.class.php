<?php

namespace common\model;

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
        return $csrf_token;
    }
    public function tokenCheck() 
    {
        if (!empty($_POST["csrf_token"]) && $_POST["csrf_token"] != $_SESSION['csrf_token']) {
            echo "不正なリクエストです";
            return false;
        }
        if (!isset($_POST["csrf_token"])) {
            echo "不正なリクエストです";
            return false;
        }
        return true;
    }
}
?>