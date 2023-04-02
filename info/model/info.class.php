<?php

namespace info\model;

use common\model\PDODatabase;
use common\model\Bootstrap;

class Info {
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

    public function infoCategoryInsert($insdata){
       $this->db->insert("info_category", $insdata);
    }

    public function getLastId(){
        return $this->dbh->lastInsertId();
    }

    public function insertReadStatusData($info_id, $user_id){
        $table = ' read_status';
        $insData = [
            'info_id' => $info_id,
            'user_id' => $user_id
        ];
        return $this->db->insert($table, $insData);
    }

    public function getInfoCategoryData($ctg_id)
    {
        $table = ' info i LEFT JOIN info_category ic ON i.id = ic.info_id LEFT JOIN category c ON ic.ctg_id = c.id';
        $column = ' i.id, i.title, i.create_at, user_id';
        $where = ' ic.ctg_id = ?'; 
        $arrVal = [$ctg_id];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

    public function getCategoryDataByInfoID($info_id)
    {
        $table = ' info i LEFT JOIN info_category ic ON i.id = ic.info_id LEFT JOIN category c ON ic.ctg_id = c.id';
        $column = ' i.id, i.title, i.create_at, user_id';
        $where = ' ic.ctg_id = ?'; 
        $arrVal = [$info_id];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

    public function searchInfoData($search)
    {
        $search = "%" . $search . "%";
        $table = ' info';
        $column = ' *';
        $where = ' title like ? OR content like ?'; 
        $arrVal = [$search,$search];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

    public function getInfoUserData($info_id)
    {
        $table = ' info i LEFT JOIN user u ON i.user_id = u.id';
        $column = 'i.*, u.name';
        $where = ' i.id = ?'; 
        $arrVal = [$info_id];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

    public function getUserInfoData($user_id)
    {
        $table = ' info i LEFT JOIN user u ON i.user_id = u.id';
        $column = 'i.*, u.name';
        $where = ' i.user_id = ?'; 
        $arrVal = [$user_id];

        return  $this->db->select($table, $column, $where, $arrVal);
    }
    public function getReadStatusData($info_id, $user_id)
    {
        $table = ' read_status';
        $column = ' *';
        $where = ' info_id = ? AND user_id = ?'; 
        $arrVal = [$info_id, $user_id];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

    public function updateInfoData($info_id, $dataArr)
    {
        $table = ' info';
        $insData = $dataArr;
        $where = ' id = ? ';
        $arrWhereVal = [$info_id];

        return $this->db->update($table, $where, $insData, $arrWhereVal);
    }

    public function deleteInfoCategoryData($info_id)
    {
        $table = ' info_category';
        $where = ' info_id = ? ';
        $arrWhereVal = [$info_id];

        return $this->db->delete($table, $where, $arrWhereVal);
    }

    public function deleteInfoData($info_id)
    {
        $table = ' info';
        $where = ' id = ? ';
        $arrWhereVal = [$info_id];

        return $this->db->delete($table, $where, $arrWhereVal);
    }

        // 既読チェック：初回のみ、詳細画面へ遷移した場合にread_statusテーブルを作成
    public function createReadStatusDataNotExists($info_id, $user_id)
    {
        $readStatusRes = $this->getReadStatusData($info_id, $user_id);
        if (empty($readStatusRes)){
            $res = $this->insertReadStatusData($info_id, $user_id);
        }
        return $res;
    }

}