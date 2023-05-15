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

// // formに埋め込むcsrf tokenの生成
// if (empty($_SESSION['csrf_token'])) {
//     $csrf->tokenCreate();
//     $conf_token = $_SESSION['csrf_token'];
// }

// $csrf_token = $_SESSION['csrf_token'];
if (isset($_POST['submit'])){
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

    // パスワードが8文字以上英数字ではない場合、エラーメッセージを表示
    if (preg_match('/^[a-zA-Z0-9!"#$%&\'()*+,-.\/:;<=>?@[\]^_`{|}~]{8,15}$/', $_POST['password']) === 0){
        $err_pass = 'パスワードを正しい形式で入力してください';
    } 
    if (preg_match('/^[a-zA-Z0-9!"#$%&\'()*+,-.\/:;<=>?@[\]^_`{|}~]{8,15}$/', $_POST['password_confirmation']) === 0){
        $err_pass_confirm = 'パスワードを正しい形式で入力してください';
    }
    if ($_POST['password'] !== $_POST['password_confirmation']) {
        $err_pass_confirm = "パスワードが異なります"; 
    } 

    if(!isset($err_pass) & !isset($err_pass_confirm)){
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
    } else {
        $context = [];
        $context['err_pass'] = $err_pass;
        $context['err_pass_confirm'] = $err_pass_confirm;
        $template = $twig->loadTemplate('user/view/reset_form.html.twig');
        $template->display($context);
    }
} else {
    // クエリからtokenを取得
    $passwordResetToken = $_GET['token'];

    // tokenに合致するユーザーを取得
    $table = 'password_resets';
    $col = '';
    $where = 'token = ?'; 
    $arrVal = [$passwordResetToken];

    $passwordResetUser = $common->db->select($table, $col, $where, $arrVal);
    // 合致するユーザーがいなければ無効なトークンなので、処理を中断
    if (!$passwordResetUser) {
        exit("無効なURLです<br><a href='login.php'>Sign in?</a>");
    }

    // 今回はtokenの有効期間を24時間とする
    $tokenValidPeriod = (new \DateTime())->modify("24 hour")->format('Y-m-d H:i:s');
    // パスワードの変更リクエストが24時間以上前の場合、有効期限切れとする
    if ($passwordResetUser[0]['token_sent_at'] > $tokenValidPeriod) {
        exit("有効期限切れです<br><a href='login.php'>Sign in?</a>");
    }

    $context = [];
    $context['passwordResetToken'] = $passwordResetToken;
    $template = $twig->loadTemplate('user/view/reset_form.html.twig');
    $template->display($context);
}

?>