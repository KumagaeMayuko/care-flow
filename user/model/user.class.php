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
        $where = 'test_id = ? AND delete_flg = ?';
        $arrVal = [$test_id, '0'];

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
    // questionテーブルの全てのデータを取得
    public function getUQuestionData()
    {
        $table = 'question';
        $column = '';
        
        return $this->db->select($table, $column);
    }

    // user_idからuser_answerテーブルの取得(そのユーザーの回答した日時が新しい順)
    public function getUserAnswerDataByUserId($user_id)
    {
        $table = 'user_answer';
        $column = '';
        $where = 'user_id = ? ORDER BY created_at DESC';
        $arrVal = [$user_id];

        return  $this->db->select($table, $column, $where, $arrVal);
    }

        // testテーブルにquestionテーブルをjoin
        public function getTestDataByUserId($user_id)
    {
        $table = 'user_answer ua LEFT JOIN question q ON ua.question_id = q.id LEFT JOIN test t ON q.test_id = t.id';
        $column = 'ua.created_at as ua_created_at, ua.choice_no, q.answer_no, t.title';
        $where = 'ua.user_id = ?';
        $arrVal = [$user_id];
        return  $this->db->select($table, $column, $where, $arrVal);
    }

    // testテーブルにquestionテーブルをjoin
    public function getTestScoreByUserId($user_id) 
    {
        $sql = "SELECT ua_created_at, COUNT(*) as record_count, SUM(IF(choice_no=answer_no, 1, 0)) as matched_count, MAX(title) as title
                FROM (
                    SELECT ua.created_at as ua_created_at, ua.choice_no, q.answer_no, t.title
                    FROM user_answer ua 
                    LEFT JOIN question q ON ua.question_id = q.id
                    LEFT JOIN test t ON q.test_id = t.id
                    WHERE ua.user_id = :user_id
                ) t1
                GROUP BY ua_created_at";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // そのユーザーがまだ行なっていないテストのデータを取得
    public function getUncompletedTestData($user_id) 
    {
        $sql = "SELECT title
                FROM test
                WHERE id NOT IN (
                    SELECT t.id
                    FROM user_answer ua 
                    LEFT JOIN question q ON ua.question_id = q.id
                    LEFT JOIN test t ON q.test_id = t.id
                    WHERE ua.user_id = :user_id
                )";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

