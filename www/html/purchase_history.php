<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// userデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';
// itemデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'item.php';
// 購入履歴データに関する関数ファイルを読み込み
require_once MODEL_PATH . 'history.php';

// ログインチェックを行うためセッションを開始する
session_start();

// ログインチェック用関数を利用
if(is_logined() === false){
  // ログインしていない場合はログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

// PDO取得
$db = get_db_connect();

// PDOを利用してログインユーザのデータを取得
$user = get_login_user($db);

// adminユーザーチェック用関数を利用してユーザーチェック
if(is_admin($user) === TRUE){
  // adminユーザーの場合は全購入履歴を取得
  $histories = get_purchase_histories_all($db);
}else{
  // adminユーザーでは無い場合はユーザーIDを条件に注文番号、購入日時、合計金額を取得する
  $histories = get_purchase_histories($db,$user['user_id']);
}


// ビューファイルの読み込み
include_once VIEW_PATH . '/purchase_history_view.php';
