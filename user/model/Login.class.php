<?php
// //セッションを使うことを宣言

namespace user\model;
 
use user\model\PDODatabase;
use user\model\Bootstrap;

class Login 
{
    public $message = [];
    public $manager_message = '';
    public $res = [];
    public $db = null;
    public $dbh = null;

    public function __construct()
    {
        $db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
        $this->db = $db;
        $this->dbh = $db->dbh;
    }
    public function checkLogin()
    {
        //セッションを使うことを宣言
        session_start();
        
        // ログイン状態の場合ログイン後のページにリダイレクト
        if (isset($_SESSION["user_id"])) {
            session_regenerate_id(TRUE);
            header("Location: top.php");
            exit();
        }
        
        $not_empty_flg = true;
        if (empty($_POST['name'])) {
            $message['name'] = '名前を入力してください';
            $not_empty_flg = false;
        }
        if (empty($_POST['email'])) {
            $message['email'] = 'メールアドレスを入力してください';
            $not_empty_flg = false;
        }
        if (empty($_POST['pass'])) {
            $message['pass'] = 'パスワードを入力してください';
            $not_empty_flg = false;
        }
        //全ての値が送信されて来た場合
        if ($not_empty_flg) {
            //post送信されてきたemailがデータベースにあるか検索
            $table = 'user';
            $col = '';
            $where = 'email = ?'; 
            $arrVal = [$_POST['email']];
            $res = $this->db->select($table, $col, $where, $arrVal);

            //検索したユーザー名に対してパスワードが正しいかを検証
            //正しくないとき

            if (!password_verify($_POST['pass'], $res[0]['pass'])) {
                $message['pass']="パスワードが違います";
            }
            //正しいとき
            else {
                session_regenerate_id(TRUE); //セッションidを再発行
                $_SESSION["user_id"] = $res[0]['id']; //セッションにログイン情報を登録
                $_SESSION["name"] = $res[0]['name']; 
                $_SESSION["manager_flg"] = $res[0]['manager_flg']; 
                $_SESSION["delete_flg"] = $res[0]['delete_flg']; 
                if($_SESSION['delete_flg'] === '0'){ // 削除済みの会員はログイン不可
                    header("Location: top.php");
                } else {
                    $message['name']= 'このアカウントは削除されています';
                }
            }
        }
        
        $this->message = $message;
    }


    public function loginByCsrfToken($res_csrftoken_check)
    {
        if($res_csrftoken_check) {
            $this->checkLogin();
        }
    }
    public function managerCheck()
    {
        if($_SESSION["manager_flg"] === "1"){
            $manager_message = '管理者の方はこちら';
        }
        $this->manager_message = $manager_message;
    }
}
