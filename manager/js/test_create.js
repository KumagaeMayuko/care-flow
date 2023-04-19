$(document).ready(function () {
    let max_fields = 100; // 最大フィールド数
    let wrapper = $(".input_fields_wrap"); // フィールドラッパー
    let add_button = $(".add_field_button"); // ボタン
    let x = 1; // 初期フィールドの数
    $(add_button).click(function (e) { // ボタンがクリックされたら
        e.preventDefault();
        if (x < max_fields) { // 最大フィールド数以下なら
            x++; // フィールド数を増やす
            $(wrapper).append(
                '<div><table><tr><th>問題番号:' + 
                x + 
                ' 問題:<textarea name="question_' +
                x + 
                '" rows="5" cols="33"></textarea></th></tr><tr><th>問題' + 
                x + 
                'に対する答えの番号（1:⚪︎ 2:×）：<input type="number" name="answer_no_' +
                x +
                '" min="0" max="100">'+
                '</th></tr><a href="#" class="remove_field">削除</a></div>'); // フィールドを追加する
        }
    });
    $(wrapper).on("click", ".remove_field", function (e) { // 追加したフィールドを削除する
        e.preventDefault(); $(this).parent('div').remove(); x--;
    })
});