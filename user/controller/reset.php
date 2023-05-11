<?php
namespace user\controller;

require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Common;
use user\model\Bootstrap;
use common\model\CSRF;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );

$common = new Common();
$csrf = new CSRF();

// // csrf tokenが正しければOK
// if (
//     empty($_POST['csrf_token'])
//     || empty($_SESSION['csrf_token'])
//     || $_POST['csrf_token'] !== $_SESSION['csrf_token']
// ) {
//     exit('不正なリクエストです');
// }

// tokenに合致するユーザーを取得
$table = 'password_resets';
$col = '';
$where = 'token = ?'; 
$arrVal = [$_POST['password_reset_token']];
$passwordResetUser = $common->db->select($table, $col, $where, $arrVal);

// どのレコードにも合致しない無効なtokenであれば、処理を中断
if (!$passwordResetUser) {
    exit('無効なURLです<br><a href="request_form.php">Forgot password?</a>');
}

// テーブルに保存するパスワードをハッシュ化
$hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);

// usersテーブルとpassword_resetsテーブルの原子性を原始性を保証するため、トランザクションを設置
try {
    $common->db->dbh->beginTransaction();

    // 該当ユーザーのパスワードを更新
    $dataArr = [
        'pass' => $hashedPassword,
    ];
    $res = $common->db->update('user', 'email = ?', $dataArr, $passwordResetUser[0]['email']);
    // 用が済んだので、パスワードリセットテーブルから削除
    $delete_res = $common->db->delete('password_resets', 'email = ?', [$passwordResetUser[0]['email']]);

    $common->db->dbh->commit();

} catch (\Exception $e) {
    $common->db->dbh->rollBack();

    exit($e->getMessage());
}

$message = 'パスワードの変更が完了しました。';
$url = 'login.php';
$url_message = 'ログイン画面へ';
$context = [];

$context['message'] = $message;
$context['url'] = $url;
$context['url_message'] = $url_message;
$template = $twig->loadTemplate('user/view/not_login_base.html.twig');
$template->display( $context );
?>