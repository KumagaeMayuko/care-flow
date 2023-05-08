<?php

namespace common\model;

use user\model\PDODatabase;
use user\model\Bootstrap;


class Common
{
    private $dataArr = [];

    private $errArr = [];

    public $db = null;
    public $dbh = null;

    public function __construct()
    {
        $db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
        $this->db = $db;
        $this->dbh = $db->dbh;
    }
    public function errorCheck($dataArr)
    {
        $this->dataArr = $dataArr;
        //クラス内のメソッドを読み込む
        $this->createErrorMessage();

        $this->titleCheck();
        $this->ctg_nameCheck();
        $this->contentCheck();

        return $this->errArr;
    }

    private function createErrorMessage()
    {
        foreach($this->dataArr as $key => $val){
            $this->errArr[ $key ] = '';
        }
    }

    private function titleCheck()
    {
        if ($this->dataArr['title'] === ''){
            $this->errArr[ 'title' ] = 'タイトルを入力してください';
        }
    }

    public function ctg_nameCheck()
    {
        if ($this->dataArr['ctg_name'] === "") {
            $this->errArr['ctg_name'] = "カテゴリーを選択してください";
        } 
    }

    public function contentCheck()
    {
        if ($_POST['content'] === "") {
            $this->errArr['content'] = "投稿内容を入力してください";
        }
    }

    public function getErrorFlg()
    {
        $err_check = true;
        foreach ($this->errArr as $key => $value) {
            if ($value !== '') {
                $err_check = false;
            }
        }
        return $err_check;
    }

    // contextに渡す共通の変数を返す
    public function getContext()
    {
        session_start();
        $context = [];
        $context['manager_flg'] = $_SESSION['manager_flg'];
        $context['username'] = $_SESSION['name'];
        return $context;
    }
}
?>