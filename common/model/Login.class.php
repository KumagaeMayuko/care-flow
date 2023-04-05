<?php
// //セッションを使うことを宣言

namespace common\model;
 
use common\model\PDODatabase;
use common\model\Bootstrap;

class Login 
{
    public $message = '';
    public $res = [];
    public $db = null;
    public $dbh = null;

    public function __construct()
    {
        $db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
        $this->db = $db;
        $this->dbh = $db->dbh;
    }
    public function checkSession()
    {
        //セッションを使うことを宣言
        session_start();

        // ログイン状態の場合ログイン後のページにリダイレクト
        if (isset($_SESSION["user_id"]) ) {
            session_regenerate_id(TRUE);
            header("Location: success.php");
            exit();
        }

        //postされて来なかったとき
        if (count($_POST) === 0) {
            $message = "";
        }
        //postされて来た場合
        else {
        //ユーザー名またはパスワードが送信されて来なかった場合
            if(empty($_POST["name"]) || empty($_POST["email"]) || empty($_POST["pass"])) {
                $message = "ユーザー名とメールアドレスとパスワードを入力してください";
            }
            //ユーザー名とパスワードが送信されて来た場合
            else {
                //post送信されてきたemailがデータベースにあるか検索
                $table = 'user';
                $col = '';
                $where = 'email = ?'; 
                $arrVal = [$_POST['email']];
                $res = $this->db->select($table, $col, $where, $arrVal);

                //検索したユーザー名に対してパスワードが正しいかを検証
                //正しくないとき

                if (!password_verify($_POST['pass'], $res[0]['pass'])) {
                    $message="パスワードが違います";
                }
                //正しいとき
                else {
                    session_regenerate_id(TRUE); //セッションidを再発行
                    $_SESSION["user_id"] = $res[0]['id']; //セッションにログイン情報を登録
                    $_SESSION["name"] = $res[0]['name']; //セッションにログイン情報を登録
                    $_SESSION["manager_flg"] = $res[0]['manager_flg']; //セッションにログイン情報を登録
                    // 管理者か確認→管理者だった場合は管理者画面へ遷移
                    if($_SESSION["manager_flg"] === "1"){
                        header("Location: success.php");
                    } else {
                        $message = '管理者ではないのでログインできません。';
                    }
                    exit();
                }
            }
        }

        $this->message = $message;
    }
}

?>