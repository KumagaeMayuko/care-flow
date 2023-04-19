<?php

// 商品に関するプログラムのクラスファイル、Model

namespace common\model;

class Category
{
    public $cateArr = [];
    public $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // カテゴリーリストの取得
    public function getCategories()
    {
        $table = 'category';
        $col = 'id, ctg_name, parent_id';
        $where = 'delete_flg = ?';
        $arrVal = ['0'];
        $res = $this->db->select($table, $col, $where, $arrVal);
        return $res;
    }
    // カテゴリーのツリー構造を再帰関数で取得
    public function buildTree($categories = [], $parentId = null) {
    $branch = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] === $parentId) {
            $children = $this->buildTree($categories, $category['id']);
            if ($children) {
                $category['children'] = $children;
            }
            $branch[] = $category;
        }
    }
    return $branch;
}
    // // 商品リストを取得する
    // public function getItemList($ctg_id)
    // {
    //     // カテゴリーによって表示させるアイテムをかえる
    //     $table = ' item ';
    //     $col = ' item_id, item_name, price, image, ctg_id';
    //     $where = ($ctg_id !== '') ? '  ctg_id = ? ' : '';
    //     $arrVal = ($ctg_id !== '') ? [$ctg_id] : [];

    //     $res = $this->db->select($table, $col, $where, $arrVal);

    //     return ($res !== false && count($res) !== 0) ? $res : false;
    // }
    // public function getSearchList($search)
    // {
    //     // カテゴリーによって表示させるアイテムをかえる
    //     $query = "SELECT "
    //             ." * "
    //             . " FROM "
    //             . "item "
    //             . " WHERE "
    //             . " item_name"
    //             . " like "
    //             . "'%"
    //             . $search
    //             . "%'";

    //     $res = mysqli_query($this->db, $query);
    //     $data = [];
    //     while ($row = mysqli_fetch_assoc($res)) {
    //         array_push($data, $row);
    //     }
    //     return ($data !== false && count($data) !== 0) ? $data : false;
    // }

    // // 商品の詳細情報を取得する
    // public function getItemDetailData($item_id)
    // {
    //     $table = ' item';
    //     $col = ' item_id, item_name, detail, price, image, ctg_id';

    //     $where = ($item_id !== '') ? ' item_id = ?' : '';
    //     //カテゴリーによって表示させるアイテムをかえる
    //     $arrVal = ($item_id !== '') ? [$item_id] : [];

    //     $res = $this->db->select($table, $col, $where, $arrVal);

    //     return ($res !== false && count($res) !== 0) ? $res : false;
    // }
}