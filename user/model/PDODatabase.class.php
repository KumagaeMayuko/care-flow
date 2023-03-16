<?php

namespace user\model;
class PDODatabase
{
    public $dbh = null;
    public $db_host = '';
    public $db_user = '';
    public $db_pass = '';
    public $db_name = '';
    public $db_type = '';
    private $order = '';
    private $limit = '';
    private $offset = '';
    private $groupby = '';

    public function __construct( $db_host, $db_user, $db_pass, $db_name, $db_type )
        {
        $this->dbh = $this->connectDB( $db_host, $db_user, $db_pass, $db_name, $db_type );
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_name = $db_name;

        //SQL関連
        $this->order = '';
        $this->limit = '';
        $this->offset = '';
        $this->groupby = '';
        }

    public function connectDB( $db_host, $db_user, $db_pass, $db_name, $db_type )
    {
        try { // 接続エラー発生→PDOExceptionオブジェクトがスローされる→例外処理をキャッチする
            switch($db_type) {
                case 'mysql':
                    $dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name;
                    $dbh = new \PDO($dsn, $db_user, $db_pass);
                    $dbh->query('SET NAMES utf8');
                    break;
                
                case 'pgsql':
                    $dsn = 'pgsql:dbname=' . $db_name . ' host=' . $db_host . 'port=5432';
                    $dbh = new \PDO($dsn, $db_user, $db_pass);
                    break;
            }
        } catch (\PDOException $e) {
                var_dump($e->getMessage());
                exit();
        }

        return $dbh;
    }

    public function setQuery($query = '', $arrVal = [])
    {
        $stmt = $this->dbh->prepare($query);  //プリペアドステートメント
        $stmt->execute($arrVal);
    }

    public function select($table, $column = '', $where = '', $arrVal = [])
    {
        $sql = $this->getSql('select', $table, $where, $column);
        $this->sqlLogInfo($sql, $arrVal);
        $stmt = $this->dbh->prepare($sql); //準備：まだ「?」のまま
        $res = $stmt->execute($arrVal);  //ここで「?」にGET通信で送られた値が入ってくる
        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }

        // // // データを連想配列に格納
        $data = [];
        while($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            array_push($data, $result);
        }
        return $data;
    }

    public function count($table, $where = '', $arrVal = [])
    {
        $sql = $this->getSql('count', $table, $where);

        $this->sqlLogInfo($sql, $arrVal);
        $stmt = $this->dbh->prepare($sql);

        $res = $stmt->execute($arrVal);

        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return intval($result['NUM']);
    }

    public function setOrder($order = '')
    {
        if ($order !== '') {
            $this->order = 'ORDER BY' . $order;
        }
    }

    public function setLimitOff($limit = '', $offset = '')
    {
        if ($limit !== "") {
            $this->limit = "LIMIT" . $limit;
        }
        if ($offset !== "") {
            $this->offset = "OFFSET" . $offset;
        }
    }

    public function setGroupBy($groupby)
    {
        if ($groupby !== ""){
            $this->groupby = ' GROUP BY' . $groupby;
        }
    }
    //  $table = 'session', $column = 'customer_no', $where = 'session_key = ?', $type = 'see';
    public function getSql($type, $table, $where = '', $column = '')
    {
        switch ($type) {
            case 'select':
                $columnKey = ($column !== '') ? $column : "*";
                break;

            case 'count':
                $columnKey = 'COUNT(*) AS NUM';
                break;
            
            default:
                break;
        }

        $whereSQL = ($where !== '') ? ' WHERE ' . $where:'';
        $other = $this->groupby . " " . $this->order . " " . $this->limit . " ". $this->offset;

        // sql文の作成
        // SELECT customer_no FROM session WHERE session_key = ?;    //$other= '';
        $sql = "SELECT " . $columnKey . " FROM " . $table . " " . $whereSQL . " " . $other;
        return $sql;
    }

    public function insert($table, $insData = [])
    {
        $insDataKey = [];
        $insDataVal = [];
        $preCnt = [];

        $columns = '';
        $preSt = '';

        foreach ($insData as $col => $val) {
            $insDataKey[] = $col;
            $insDataVal[] = $val;
            $preCnt[] = '?'; 
        }

        $columns = implode(",", $insDataKey);
        $preSt = implode(",", $preCnt);

        $sql = " INSERT INTO "
             . $table // cart
             . " ("
             . $columns  // $customer_no, $item_id
             . ") VALUES ("
             . $preSt // ?
             . ") ";
        $this->sqlLogInfo($sql, $insDataVal);  // $insDataVal = ['$customer_no, $item_id']
        
        $stmt = $this->dbh->prepare($sql); // プリペアドステートメント
        $res = $stmt->execute($insDataVal);

        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }

        return $res;
    }
    public function update($table, $where, $insData = [], $arrWhereVal = [])
    {
        $arrPreSt = [];
        foreach ($insData as $col => $val) {
            $arrPreSt[] = $col . "=?";
        }
        $preSt = implode(',', $arrPreSt);

        // sql文の作成
        $sql = "UPDATE "
             . $table
             . " SET "
             . $preSt  // delete_flg = ?;
             . " WHERE "
             . $where;  // crt_id = ?;
        // array_merge():配列を結合する。
        // array_values():全ての値を取り出す 
        $insDataVal = array_values($insData);
        $insDataVal[] = $arrWhereVal;
        $updateData = $insDataVal;
        // $updateData = array_merge(array_values($insData), $arrWhereVal);
        $this->sqlLogInfo($sql, $updateData);
        $stmt = $this->dbh->prepare($sql);
        $res = $stmt->execute($updateData);
        
        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }
        return $res;
    }
    public function delete($table, $where, $arrWhereVal = [])
    {

        // sql文の作成
        $sql = "DELETE FROM "
             . $table
             . " WHERE "
             . $where;  // crt_id = ?;

        $this->sqlLogInfo($sql, $arrWhereVal);
        $stmt = $this->dbh->prepare($sql);
        $res = $stmt->execute($arrWhereVal);

        if ($res === false) {
            $this->catchError($stmt->errorInfo());
        }
        return $res;
    }

    public function getLastId()
    {
        return $this->dbh->lastInsertId();
    }

    private function catchError($errArr = [])
    {
        $errMsg = (!empty($errArr[2]))? $errArr[2]:"";
        // die():文字列を表示させて終了させる
        die("SQLエラーが発生しました。" . $errMsg);
    }

    private function makeLogFile()
    {
        $logDir = dirname(__DIR__) . "/logs";
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777);
        }
        $logPath = $logDir . '/shopping.log';
        if (!file_exists($logPath)) {
            touch($logPath);
        }
        return $logPath;
    }

    private function sqlLogInfo($str, $arrVal = [])
    {
        $logPath = $this->makeLogFile();  // /logs/shopping.logのパスが入る
        // $str = $sql, $arrVal = $session_key = ['session_key']
        $logData = sprintf("[SQL_LOG:%s]: %s [%s]\n", date('Y-m-d H:i:s'), $str, implode(",", $arrVal)); 
        error_log($logData, 3, $logPath);
    }

}