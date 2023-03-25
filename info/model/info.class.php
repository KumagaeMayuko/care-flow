<?php

namespace info\model;

use user\model\PDODatabase;
use user\model\Bootstrap;

class Info{
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
        $this->imageCheck();
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
    public function reselectImage(){
        if(isset($_FILES['image']) && isset($_POST['image_x'])){
            unset($_POST['image_x']);
        }
        return; 
    }
    public function getTmpImage(){
        if(isset($_FILES['image'])){
            $tmp_image = $_FILES['image'];
        } elseif ($_POST['image_x']) {
            $_POST['image'] = $_POST['image_x'];
            unset($_POST['image_x']);
            $tmp_image = $_POST['image'];
        }
        return $tmp_image;
    }
    public function imageCheck(){
        $this->reselectImage();
        $tmp_image = $this->getTmpImage();
        if (isset($tmp_image) && $tmp_image['error'] === 0 && $tmp_image['size'] !== 0) {
            if (is_uploaded_file($tmp_image['tmp_name']) === true) {
                $image_info = getimagesize($tmp_image['tmp_name']);
                $image_mime = $image_info['mime'];
                $now = time(); 
                if ($tmp_image['size'] > 1048576) {
                    $errArr['image'] = 'アップロードできる画像のサイズは、1MBまでです';
                } elseif (preg_match('/^image¥/jpeg$/', $image_mime) === 0) {
                    $errArr['image'] = 'アップロードできる画像の形式は、JPEG形式だけです';
                } elseif (move_uploaded_file($tmp_image['tmp_name'], './images/upload_' . $now . '.jpeg') === true) {         
                    header('Location:' . Bootstrap::ENTRY_URL .  'post_confirm.php');
                }
            }
        }
        return true;
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