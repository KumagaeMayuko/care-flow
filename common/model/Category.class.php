<?php

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

    public function recursiveGetChildCategories($parent_id)
    {
        $table = 'category';
        $columns = 'id, ctg_name, parent_id';
        $where = 'parent_id = ? AND delete_flg = ?';
        $arrVal = [$parent_id, '0'];
        $result = [];

        // 子カテゴリーを取得
        $children = $this->db->select($table, $columns, $where, $arrVal);

        foreach ($children as $child) {
            // 子カテゴリーを結果に追加
            $result[] = $child;

            // 再帰的に子カテゴリーの子カテゴリーを取得
            $result = array_merge($result, $this->recursiveGetChildCategories($child['id']));
        }

        return $result;
    }

        // 親カテゴリーリストの取得
    public function getCategorieById($id)
    {
        $table = 'category';
        $col = 'id, ctg_name, parent_id';
        $where = 'id = ? AND delete_flg = ?';
        $arrVal = [$id, '0'];
        $res = $this->db->select($table, $col, $where, $arrVal);
        return $res;
    }
}

