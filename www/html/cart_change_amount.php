<?php
//  定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// userデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';
// itemデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'item.php';
// cartデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'cart.php';

// ログインチェックを行うためセッションを開始する
session_start();

// ログインチェック用関数を利用
if(is_logined() === false){
  // ログインしていない場合はログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

// post値取得用関数を利用してpostされたトークンを取得
$token = get_post('csrf_token');
// postされたトークンとセッションに保存しているトークンを照合
if(is_valid_csrf_token($token) === false){
  // 照合できない場合リダイレクト用関数を利用してログインページにリダイレクト
  redirect_to(LOGIN_URL);
}
// セッションに保存されているトークンを破棄
unset($_SESSION['csrf_token']);

// PDO取得
$db = get_db_connect();

// PDOを利用してログインユーザーデータを取得
$user = get_login_user($db);

// post値取得用関数を利用してpostされたカートIDを取得
$cart_id = get_post('cart_id');
// post値取得用関数を利用して新しい購入数を取得
$amount = get_post('amount');

// 購入数変更用関数を利用して購入数を変更する
if(update_cart_amount($db, $cart_id, $amount)){
  // 正常に変更できた場合は変更完了メッセージをセッション変数に格納
  set_message('購入数を更新しました。');
} else {
  // 変更できなかった場合はエラーメッセージをセッション変数に格納
  set_error('購入数の更新に失敗しました。');
}

// リダイレクト用関数を利用してカートページにリダイレクト
redirect_to(CART_URL);