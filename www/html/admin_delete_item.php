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

// PDOを取得
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


// 商品削除用関数を利用して商品を削除する
if(destroy_item($db, $item_id) === true){
  // 正常に商品を削除できた場合は削除完了メッセージをセッション変数に格納
  set_message('商品を削除しました。');
} else {
  // 削除できなかった場合はエラーメッセージをセッション変数に格納
  set_error('商品削除に失敗しました。');
}


// リダイレクト用関数を利用して商品管理ページにリダイレクト
redirect_to(ADMIN_URL);