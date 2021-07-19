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

// PDOを利用してログインユーザーのデータを取得
$user = get_login_user($db);

// adminユーザーチェック用関数を利用
if(is_admin($user) === false){
  // adminユーザーではない場合はログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

// post値取得用の関数を利用してpostされた商品名を取得
$name = get_post('name');
// post値取得用の関数を利用してpostされた値段を取得
$price = get_post('price');
// post値取得用の関数を利用してpostされたステータスの値を取得
$status = get_post('status');
// post値取得用の関数を利用してpostされた在庫数を取得
$stock = get_post('stock');

// HTTP POSTでのファイルアップロードチェック用関数を利用してファイルの値を取得
$image = get_file('image');

// 商品登録用関数を利用して入力された商品を登録する
if(regist_item($db, $name, $price, $stock, $status, $image)){
// 正常に登録できた場合は登録完了メッセージをセッション変数に格納
  set_message('商品を登録しました。');
}else {
  // 登録できなかった場合はエラーメッセージをセッション変数に格納
  set_error('商品の登録に失敗しました。');
}

// リダイレクト用関数を利用して商品管理ページにリダイレクト
redirect_to(ADMIN_URL);