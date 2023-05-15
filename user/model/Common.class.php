<?php

namespace user\model;

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

        $this->nameCheck();
        $this->emailCheck();
        $this->passCheck();

        return $this->errArr;
    }

    private function createErrorMessage()
    {
        foreach($this->dataArr as $key => $val){
            $this->errArr[ $key ] = '';
        }
    }

    private function nameCheck()
    {
        if ($this->dataArr['name'] === ''){
            $this->errArr[ 'name' ] = 'お名前（フルネーム）を入力してください';
        }
    }

    public function emailCheck()
    {
        if ($this->dataArr['email'] === "") {
            $this->errArr['email'] = "メールアドレスを入力してください";
        } else if(preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+[a-zA-Z0-9\._-]+$/',$this->dataArr['email']) === 0){
            $this->errArr[ 'email' ] = 'メールアドレスを正しい形式で入力してください';
        }else {
            $table = ' user';
            $col = ' email';
            $where = ' email = ?'; 
            $arrVal = [$this->dataArr['email']];
            $res = $this->db->select($table, $col, $where, $arrVal);
            if (count($res) > 0) {
                $this->errArr['email'] = 'このメールアドレスは登録されています';
            }
        }
    }

    public function requestEmailCheck($email)
    {
        if ($_POST['email'] === "") {
            $this->errArr['email'] = "メールアドレスを入力してください";
        } else if(preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+[a-zA-Z0-9\._-]+$/',$email) === 0){
            $this->errArr[ 'email' ] = 'メールアドレスを正しい形式で入力してください';
        }
    }

    public function passCheck()
    {
        // パスワードが8文字未満の場合、エラーメッセージを表示
        if (preg_match('/^[a-zA-Z0-9!"#$%&\'()*+,-.\/:;<=>?@[\]^_`{|}~]{8,15}$/', $this->dataArr['pass']) === 0){
            $this->errArr['pass'] = 'パスワードを正しい形式で入力してください';
        } 
        if (preg_match('/^[a-zA-Z0-9!"#$%&\'()*+,-.\/:;<=>?@[\]^_`{|}~]{8,15}$/', $this->dataArr['pass']) === 0){
            $this->errArr['pass_confirm'] = 'パスワードを正しい形式で入力してください';
        } 
        if ($this->dataArr['pass'] !== $this->dataArr['pass_confirm']) {
            $this->errArr['pass_confirm'] = "パスワードが異なります"; 
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
}
?>