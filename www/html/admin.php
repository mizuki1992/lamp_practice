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

// PDO取得
$db = get_db_connect();

// PDOを利用してログインユーザのデータを取得
$user = get_login_user($db);

// adminユーザーチェック用関数を利用
if(is_admin($user) === false){
  // adminユーザーではない場合はログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

// 全てのアイテム取得用関数を利用
$items = get_all_items($db);

$token = get_csrf_token();

// ビューファイルの読み込み
include_once VIEW_PATH . '/admin_view.php';
