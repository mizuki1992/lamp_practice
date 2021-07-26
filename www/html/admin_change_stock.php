<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// userデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';
// itemデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'item.php';

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

// PDOを利用してログインユーザーのデータを取得
$user = get_login_user($db);

// adminユーザーチェック用関数を利用
if(is_admin($user) === false){
  // adminユーザーではない場合はログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

// post値取得用関数を利用してpostされた商品IDを取得
$item_id = get_post('item_id');
// post値取得用関数を利用してpostされた新しい在庫数を取得
$stock = get_post('stock');

// 在庫数変更用関数を利用して在庫数を変更する
if(update_item_stock($db, $item_id, $stock)){
  // 正常に在庫数を変更できた場合は変更完了メッセージをセッション変数に格納
  set_message('在庫数を変更しました。');
} else {
  // 在庫数を変更できなかった場合はエラーメッセージをセッション変数に格納
  set_error('在庫数の変更に失敗しました。');
}

// リダイレクト用関数を利用して商品管理ページにリダイレクト
redirect_to(ADMIN_URL);