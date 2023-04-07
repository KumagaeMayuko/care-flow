<?php

namespace manager\model;

use common\model\PDODatabase;
use common\model\Bootstrap;  

Class manager {

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

    // 取得した配列を分解し変数に文字を代入
    public function characterAssignment()
    {
        $users_data = $this->getUsersData();
        foreach($users_data as $value){
            $value['manager_message'] = '';
            $value['message'] = '';
            if($value['delete_flg'] === '1'){
                $value['message'] = '(削除済み)';
            }
            if($value['manager_flg'] === '1'){
                $value['manager_message'] = '(管理者)';
            }
            $users[] = $value;
        }
        return $users;
    }

    // 会員削除、削除取り消し機能、それ以外（ただの表示）
    public function conditionalBranch()
    {
        $users = $this->characterAssignment();

        if(isset($_POST['delete_user_id']) ){ // delete_flgが0(削除されていない)場合
            $insData = ['delete_flg' => '1'];
            $res = $this->updateUserDataDeleteFlg($_POST['delete_user_id'], $insData);
            $message = '削除しました';
        } else if (isset($_POST['non_delete_user_id'])){ // delete_flgが1(削除されている)場合
            $insData = ['delete_flg' => '0'];
            $res = $this->updateUserDataDeleteFlg($_POST['non_delete_user_id'], $insData);
            $message = '削除を取り消しました';
        } else {
            $template = 'manager/view/user_list.html.twig';
            $context['users'] = $users;
        }

        if (isset($_POST['delete_user_id']) || isset($_POST['non_delete_user_id'])) {
            $url = '../../manager/controller/user_list.php';
            $url_message = '会員一覧画面へ戻る';
            
            $context['message'] = $message;
            $context['url'] = $url;
            $context['url_message'] = $url_message;
            
            $template = 'user/view/process_complete.html.twig';
            unset($_POST);
        }
        $this->context = $context;
        $this->template = $template;
        return $res;
    }
    // 管理者設定と解除
    public function managerSetting()
    {
        $users = $this->characterAssignment();

        if(isset($_POST['non_manager_user_id']) ){ // manager_flgが0(削除されていない)場合
            $insData = ['manager_flg' => '1'];
            $res = $this->updateUserDataDeleteFlg($_POST['non_manager_user_id'], $insData);
            $message = '管理者へ登録完了しました';
        } else if (isset($_POST['manager_user_id'])){ // manager_flgが1(削除されている)場合
            $insData = ['manager_flg' => '0'];
            $res = $this->updateUserDataDeleteFlg($_POST['manager_user_id'], $insData);
            $message = '管理者を解除しました';
        } else {
            $template = 'manager/view/user_list.html.twig';
            $context['users'] = $users;
        }

        if (isset($_POST['non_manager_user_id']) || isset($_POST['manager_user_id'])) {
            $url = '../../manager/controller/user_list.php';
            $url_message = '会員一覧画面へ戻る';
            
            $context['message'] = $message;
            $context['url'] = $url;
            $context['url_message'] = $url_message;
            
            $template = 'user/view/process_complete.html.twig';
            unset($_POST);
        }
        $this->context = $context;
        $this->template = $template;
        return $res;
    }

}