<?php
namespace user\controller;

require_once dirname( __FILE__, 2) . '/model/Bootstrap.class.php';

use user\model\Common;
use user\model\Bootstrap;

$loader = new \Twig_Loader_Filesystem( Bootstrap::TEMPLATE_DIR );
$twig = new \Twig_Environment( $loader, [ 
    'cache' => Bootstrap::CACHE_DIR
] );
$common = new Common();
$common->requestEmailCheck($_POST['email']);

$table = 'user';
$col = '';
$where = 'email = ?'; 
$arrVal = [$_POST['email']];
$res = $common->db->select($table, $col, $where, $arrVal);

// 未登録のメールアドレスであっても、送信完了画面を表示
// 「未登録です」と表示すると、万が一そのメールアドレスを知っている別人が入力していた場合、「このメールアドレスは未登録である」と情報を与えてしまう

if (!$res) {
    $message = 'このメールドレスは登録されていません';
    $url = 'regist.php';
    $url_message = '会員登録画面へ';
    $context = [];

    $context['message'] = $message;
    $context['url'] = $url;
    $context['url_message'] = $url_message;
    $template = $twig->loadTemplate('user/view/process_complete.html.twig');
    $template->display( $context );
} else {
    // 既にパスワードリセットのフロー中（もしくは有効期限切れ）かどうかを確認
    // $passwordResetUserが取れればフロー中、取れなければ新規のリクエストということ
    $table = 'password_resets';
    $col = '';
    $where = 'email = ?'; 
    $arrVal = [$_POST['email']];
    $passwordResetUser = $common->db->select($table, $col, $where, $arrVal);

    // password reset token生成
    $passwordResetToken = bin2hex(random_bytes(32));

    $dataArr = [
        'email'      => $_POST['email'],
        'token'       => $passwordResetToken,
        'token_sent_at' => date('Y-m-d H:i:s', time()),
    ];

    // password_resetsテーブルへの変更とメール送信は原子性を保ちたいため、トランザクションを設置する
    // メール送信に失敗した場合は、パスワードリセット処理自体も失敗させる
    try {
        $common->dbh->beginTransaction();

        // 以下、mail関数でパスワードリセット用メールを送信
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");

        // URLはご自身の環境に合わせてください
        $url = "http://localhost:8888/CF/user/controller/show_reset_form.php?token={$passwordResetToken}";

        $subject =  'パスワードリセット用URLをお送りします';

        $body = <<<EOD
            24時間以内に下記URLへアクセスし、パスワードの変更を完了してください。
            {$url}
            EOD;

        // Fromはご自身の環境に合わせてください
        $headers = "From : mayukokumagae315@gmail.com\n";
        // text/htmlを指定し、html形式で送ることも可能
        $headers .= "Content-Type : text/plain";

        // mb_send_mailは成功したらtrue、失敗したらfalseを返す
        $isSent = mb_send_mail($dataArr['email'], $subject, $body, $headers);

        if (!$isSent) throw new \Exception('メール送信に失敗しました。');

        // メール送信まで成功したら、password_resetsテーブルへの変更を確定
        if (!$passwordResetUser) {
            // $passwordResetUserがいなければ、仮登録としてテーブルにインサート
            $res = $common->db->insert("password_resets", $dataArr);
        } else {
            // 既にフロー中の$passwordResetUserがいる場合、tokenの再発行と有効期限のリセットを行う
            $res = $common->db->update('password_resets', 'email = ?', $dataArr, $_POST['email']);
        }

        $common->dbh->commit();

    } catch (\Exception $e) {
        $common->dbh->rollBack();

        exit($e->getMessage());
    }

    $message = 'メールを送信しました';
    $context = [];

    $context['message'] = $message;

    $template = $twig->loadTemplate('user/view/process_complete.html.twig');
    $template->display( $context );
}

?>