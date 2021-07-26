<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// userデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';
// itemデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'item.php';
// cartデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'cart.php';

// ログインチェックを行うため、セッションを開始する
session_start();

// ログインチェック用関数を利用
if(is_logined() === false){
  // ログインできていない場合はログインページにリダレクト
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


// post値取得用関数を利用してpostされた商品IDを取得
$item_id = get_post('item_id');

// カート追加用関数を利用してカートに商品を追加する
if(add_cart($db,$user['user_id'], $item_id)){
  // 正常にカートに追加できた場合は完了メッセージをセッション変数に格納
  set_message('カートに商品を追加しました。');
} else {
  // カートに追加できなかった場合はエラーメッセージをセッション変数に格納
  set_error('カートの更新に失敗しました。');
}

// リダイレクト用関数を利用してホームページにリダイレクト
redirect_to(HOME_URL);