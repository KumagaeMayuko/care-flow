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

    public function getPagenationData($table, $whereVal, $per_page = 5)
    {
        // GETで現在のページ数を取得する（未入力の場合は1を挿入）
        if (isset($_GET['page'])) {
            $page = (int)$_GET['page'];
        } else {
            $page = 1;
        }

        // スタートのポジションを計算する
        if ($page > 1) {
            // 例：２ページ目の場合は、『(2 × 10) - 10 = 10』
            $start = ($page * $per_page) - $per_page;
        } else {
            $start = 0;
        }

        // userの総数を取得
        $dbh_a = mysqli_connect(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME);
        $query = "SELECT COUNT( * ) FROM {$table} WHERE {$whereVal}"; 
        $res = mysqli_query($dbh_a, $query);
        $data_tmp = [];
        while ($row = mysqli_fetch_assoc($res)) {
            array_push($data_tmp, $row);
        }
        $data_tmp_n = (int)$data_tmp[0]["COUNT( * )"];
        $page_num = ceil($data_tmp_n/$per_page);

        // userテーブルから5件のデータを取得する
        $query = "SELECT * FROM {$table} WHERE {$whereVal} LIMIT {$start}, {$per_page} "; 
        $res = mysqli_query($dbh_a, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($res)) {
            array_push($data, $row);
        }

        mysqli_close($dbh_a);
        return ['data'=>$data, 'page_num'=>$page_num, 'page'=>$page];
    }

    // ※上の関数と重なりあり。5件のみ取得したい場合
    public function getDataByLimit($ctg_id, $start, $per_page)
    {
        $dbh_a = mysqli_connect(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME);
        // userテーブルから5件のデータを取得する
        $query ="SELECT * FROM info i
                LEFT JOIN info_category ic ON i.id = ic.info_id
                LEFT JOIN category c ON ic.ctg_id = c.id
                WHERE ic.ctg_id = {$ctg_id} AND i.delete_flg = '0' AND i.check_flg = '0'
                LIMIT {$start}, {$per_page} ";

        $res = mysqli_query($dbh_a, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($res)) {
            array_push($data, $row);
        }

        mysqli_close($dbh_a);
        return [$data];
    }
}
?>