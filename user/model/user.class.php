<?php

namespace user\model;

use common\model\PDODatabase;
use common\model\Bootstrap;  

Class User {

    public $db = null;
    public $dbh = null;

    public function __construct()
    {
        $db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
        $this->db = $db;
        $this->dbh = $db->dbh;

    }

    // testテーブルの取得
    public function getTestData()
    {
        $table = 'test';
        $column = '';

        return  $this->db->select($table, $column);
    }
    // idからtestテーブルの取得
    public function getTestDataById($test_id)
    {
        $table = 'test';
        $column = '';
        $where = 'id = ?';
        $arrVal = [$test_id];

        return  $this->db->select($table, $column, $where, $arrVal);
    }
    // idからquestionテーブルの取得
    public function getQuestionDataById($test_id)
    {
        $table = 'question';
        $column = '';
        $where = 'test_id = ?';
        $arrVal = [$test_id];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

    // user_answerテーブルにデータ追加作成
    public function insertUserAnswer($user_id, $question_id, $choice_no, $created_at){
        $table = 'user_answer';
        $insData = [
            'user_id' => $user_id,
            'question_id' => $question_id,
            'choice_no' => $choice_no,
            'created_at' => $created_at
        ];
        return $this->db->insert($table, $insData);
    }

    // // user_idからuser_answerテーブルの取得
    // public function getUserAnswerDataByUserId($user_id,$created_at)
    // {
    //     $table = 'user_answer';
    //     $column = '';
    //     $where = 'user_id = ? AND created_at = ?';
    //     $arrVal = [$user_id, $created_at];

    //     return  $this->db->select($table, $column, $where, $arrVal);
    // }
    // quetion_idからquestionテーブルの取得
 public function getUQuestionDataByQuestionId($question_ids)
{
    $table = 'question';
    $column = '';
    // 配列の要素の数だけ、where (?, ?, ?)を作成
    $question_marks = implode(',', array_fill(0, count($question_ids), '?'));
    $where = "id IN ($question_marks)";
    $arrVal = $question_ids;

    return $this->db->select($table, $column, $where, $arrVal);
}

}