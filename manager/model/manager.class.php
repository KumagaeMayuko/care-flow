<?php

namespace manager\model;

use common\model\Bootstrap;
use common\model\PDODatabase;  

Class Manager {

    public $db = null;
    public $dbh = null;
    public $context = [];
    public $template = '';

    public function __construct()
    {
        $db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
        $this->db = $db;
        $this->dbh = $db->dbh;

    }

    // Userテーブルから全ての会員情報を取得
    public function getUsersData()
    {
        $table = ' user';
        return  $this->db->select($table);
    }

    // infoテーブルから全ての情報を取得
    public function getInfosData()
    {
        $table = 'info';
        return  $this->db->select($table);
    }
    // infoテーブルからcheck_flgが１の情報を取得
    public function getInfosDataByCheckFlg($check_flg)
    {
        $table = 'info';
        $colomn = '';
        $where = 'check_flg = ?';
        $arrVal = [$check_flg];

        return  $this->db->select($table, $colomn, $where, $arrVal);
    }

    // Userテーブルからuser_idを指定して全ての会員情報を取得
    public function getUserData($user_id)
    {
        $table = ' user';
        $colomn = '';
        $where = ' id = ?';
        $arrVal = [$user_id];

        return  $this->db->select($table, $colomn, $where, $arrVal);
    }

    // userテーブルから送信したuser_idの情報を更新（delete_flgを1に変更）
    public function updateUserDataDeleteFlg($id, $insData)
    {
        $table = ' user';
        $where = ' id = ?'; 
        $arrWhereVal = [$id];

        return  $this->db->update($table, $where, $insData, $arrWhereVal);
    }
    // userテーブルの名前を更新
        public function updateUserName($user_id, $dataArr)
    {
        $table = ' user';
        $where = ' id = ?'; 
        $insData = ['name' => $dataArr];
        $arrWhereVal = [$user_id];

        return  $this->db->update($table, $where, $insData, $arrWhereVal);
    }
    // userテーブルのメアドを更新
        public function updateUserEmail($user_id, $dataArr)
    {
        $table = ' user';
        $where = ' id = ?'; 
        $insData = ['email' => $dataArr];
        $arrWhereVal = [$user_id];

        return  $this->db->update($table, $where, $insData, $arrWhereVal);
    }

    // 会員削除、削除取り消し機能、それ以外（ただの表示）
    public function conditionalBranch()
    {
        if(isset($_POST['delete_user_id']) ){ // delete_flgが0(削除されていない)場合
            $insData = ['delete_flg' => '1'];
            $res = $this->updateUserDataDeleteFlg($_POST['delete_user_id'], $insData);
            unset($_POST);
            header('Location:user_list.php');
        } else if (isset($_POST['non_delete_user_id'])){ // delete_flgが1(削除されている)場合
            $insData = ['delete_flg' => '0'];
            $res = $this->updateUserDataDeleteFlg($_POST['non_delete_user_id'], $insData);
            unset($_POST);
            header('Location:user_list.php');
        } else if(isset($_POST['non_manager_user_id']) ){ // manager_flgが0(削除されていない)場合
            $insData = ['manager_flg' => '1'];
            $res = $this->updateUserDataDeleteFlg($_POST['non_manager_user_id'], $insData);
            unset($_POST);
            header('Location:user_list.php');
        } else if (isset($_POST['manager_user_id'])){ // manager_flgが1(削除されている)場合
            $insData = ['manager_flg' => '0'];
            $res = $this->updateUserDataDeleteFlg($_POST['manager_user_id'], $insData);
            unset($_POST);
            header('Location:user_list.php');
        } else {
            $res = '';
        }

        return $res;
    }

    // read_statusテーブルから全てのデータを取得
    public function getReadStatusData()
    {
        $table = 'read_status';
        $column = '*';
        $where = ''; 
        $arrVal = [];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

    // userテーブルにread_statusテーブルをjoin
        public function getUserReadStatusData()
    {
        $table = 'user u LEFT JOIN read_status rs ON u.id = rs.user_id LEFT JOIN info i ON rs.info_id = i.id';
        $column = 'u.name, u.id AS user_id, rs.id AS read_status_id, i.title, i.id AS info_id';
        $where = '';
        $arrVal = [];

        return  $this->db->select($table, $column, $where, $arrVal);
    }
    // userテーブルにread_statusテーブルをjoin(user_idで取得)
        public function getUserReadStatusDataByUserId($user_id)
    {
        $table = 'user u LEFT JOIN read_status rs ON u.id = rs.user_id LEFT JOIN info i ON rs.info_id = i.id';
        $column = 'u.name, u.id AS user_id, rs.id AS read_status_id, i.title, i.id AS info_id';
        $where = 'u.id = ?';
        $arrVal = [$user_id];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

    // 未読のinfo_id,titleを取得
    public function getUnReadInfos($user_id){
        $infos = $this->getInfosData();
        $readStatusByUserId = $this->getUserReadStatusDataByUserId($user_id);
        $alreadyReadInfos = array_column($readStatusByUserId, 'title', 'info_id'); // 配列から'id''title'キーの値を取り出して新しい配列を作成
        $infoAll = array_column($infos, 'title', 'id');
        $unreadInfos = array_diff($infoAll, $alreadyReadInfos); // $infoAllから$alreadyReadInfoIdを取り除いた配列を作る
        return $unreadInfos;
    }

    // categoryテーブルにカテゴリーを追加
    public function insertCategoryData($ctg_name, $parent_id){
        $table = 'category';
        $now = time();
        $create_at = date("Y/m/d H:i:s", $now);
        $insData = [
            'ctg_name' => $ctg_name,
            'parent_id' => $parent_id,
            'create_at' => $create_at
        ];
        return $this->db->insert($table, $insData);
    }
    
    // categoryテーブルの親要素を指定した子カテゴリーの取得
    public function getCategoryByParentId($id)
    {
        $table = 'category';
        $column = '';
        $where = 'parent_id = ?';
        $arrVal = [$id];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

    // categoryテーブルを更新(where = id)
        public function updateCategoryDataByDeleteFlg($id)
    {
        $table = 'category';
        $now = time();
        $update_at = date("Y/m/d H:i:s", $now);
        $delete_at = date("Y/m/d H:i:s", $now);
        $insData = [
            'update_at' => $update_at,
            'delete_at' => $delete_at,
            'delete_flg' => 1
        ];
        $where = 'id = ? ';
        $arrWhereVal = [$id];

        return $this->db->update($table, $where, $insData, $arrWhereVal);
    }
    // categoryテーブルを更新(where = parent_id)
        public function updateCategoryDataByParentId($parent_id)
    {
        $table = 'category';
        $now = time();
        $update_at = date("Y/m/d H:i:s", $now);
        $delete_at = date("Y/m/d H:i:s", $now);
        $insData = [
            'update_at' => $update_at,
            'delete_at' => $delete_at,
            'delete_flg' => 1
        ];
        $where = 'parent_id = ?';
        $arrWhereVal = [$parent_id];

        return $this->db->update($table, $where, $insData, $arrWhereVal);
    }

        // testテーブルにタイトルを追加
    public function insertTestData($title){
        $table = 'test';
        $now = time();
        $created_at = date("Y/m/d H:i:s", $now);
        $insData = [
            'title' => $title,
            'created_at' => $created_at
        ];
        $res = $this->db->insert($table, $insData);
        return $this->db->getLastId();
    }
        // questionテーブルに問題を追加
    public function insertQuestionData($test_id, $question, $answer_no){
        $table = 'question';
        $now = time();
        $created_at = date("Y/m/d H:i:s", $now);
        $insData = [
            'test_id' => $test_id,
            'question' => $question,
            'answer_no' => $answer_no,
            'created_at' => $created_at
        ];
        return $this->db->insert($table, $insData);
    }

        // testテーブルの取得
    public function getTestData()
    {
        $table = 'test';
        $column = '';
        $where = 'delete_flg = ?';
        $arrVal = ['0'];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

    // testテーブルのタイトルを更新
    public function updateTestTitle($id, $title)
    {
        $table = 'test';
        $where = 'id = ?'; 
        $now = time();
        $updated_at = date("Y/m/d H:i:s", $now);
        $insData = [
            'title' => $title,
            'updated_at' => $updated_at
        ];
        $arrWhereVal = [$id];

        return  $this->db->update($table, $where, $insData, $arrWhereVal);
    }
    // testテーブルのdelete_flgを更新
    public function updateTestDeleteFlg($test_id, $delete_flg)
    {
        $table = 'test';
        $where = 'id = ?'; 
        $now = time();
        $updated_at = date("Y/m/d H:i:s", $now);
        $insData = [
            'delete_flg' => $delete_flg,
            'deleted_at' => $updated_at,
            'updated_at' => $updated_at,
        ];
        $arrWhereVal = [$test_id];

        return  $this->db->update($table, $where, $insData, $arrWhereVal);
    }

    // questionテーブルのレコードのdelete_flgを更新
    public function updateQuestionDataByDeleteFlg($question_id, $delete_flg)
    {
        $table = 'question';
        $where = 'id = ?'; 
        $now = time();
        $updated_at = date("Y/m/d H:i:s", $now);
        $insData = [
            'delete_flg' => $delete_flg,
            'updated_at' => $updated_at,
            'deleted_at' => $updated_at
        ];
        $arrWhereVal = [$question_id];

        return  $this->db->update($table, $where, $insData, $arrWhereVal);
    }
    // questionテーブルから送信したtest_idの情報を更新（問題文、答え）
    public function updateQuestionDataByQuestionId($question_id, $question, $answer_no)
    {
        $table = 'question';
        $where = 'id = ?'; 
        $now = time();
        $updated_at = date("Y/m/d H:i:s", $now);
        $insData = [
            'question' => $question,
            'answer_no' => $answer_no,
            'updated_at' => $updated_at,
        ];
        $arrWhereVal = [$question_id];

        return  $this->db->update($table, $where, $insData, $arrWhereVal);
    }

    // questionテーブルのテスト問題自体（test_id）のdelete_flgを１へ変更
    public function updateQuestionDataByTestId($test_id, $delete_flg)
    {
        $table = 'question';
        $where = 'test_id = ?'; 
        $now = time();
        $updated_at = date("Y/m/d H:i:s", $now);
        $insData = [
            'delete_flg' => $delete_flg,
            'updated_at' => $updated_at,
            'deleted_at' => $updated_at
        ];
        $arrWhereVal = [$test_id];

        return  $this->db->update($table, $where, $insData, $arrWhereVal);
    }
}