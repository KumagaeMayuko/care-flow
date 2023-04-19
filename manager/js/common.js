// DOMが読み込まれた時に実行
document.addEventListener('DOMContentLoaded', function () {
    // カテゴリー作成ボタンを取得し、ループ処理
    document.querySelectorAll('.category_create_btn').forEach(function (btn) {
        // クリック回数をカウント
        let n_clicks = 0;
        // カテゴリー作成ボタンにクリックイベントを設定
        btn.addEventListener('click', function () {
            // カテゴリー名入力フォームを取得
            let input = btn.nextElementSibling;
            // カテゴリー名入力フォームを表示
            input.style.display = 'block';

            // カウントが偶数回クリックされた場合、フォームを非表示にする
            btn.count = (btn.count || 0) + 1;
            if (btn.count % 2 === 0) {
                input.style.display = 'none';
            }
        });

        // カテゴリー名入力フォームの送信ボタンにイベントを設定
        btn.nextElementSibling.querySelector('form').addEventListener('submit', function (e) {
            // デフォルトの送信処理を停止
            e.preventDefault();
            // フォームを送信
            this.submit();
        });
    });
    // カテゴリー削除ボタンをクリックした時の処理

});


