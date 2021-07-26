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
// post値取得用関数を利用してpostされた新しいステータスの値を取得
$changes_to = get_post('changes_to');


// 取得した新しいステータスの値がopenである場合
if($changes_to === 'open'){
  // ステータス変更用関数を利用してステータスを１に変更する
  update_item_status($db, $item_id, ITEM_STATUS_OPEN);
  // 正常にステータスを変更できた場合は変更完了メッセージをセッション変数に格納
  set_message('ステータスを変更しました。');
  // 取得した新しいステータスの値がcloseである場合
}else if($changes_to === 'close'){
  // ステータス変更用関数を利用してステータスを０に変更する
  update_item_status($db, $item_id, ITEM_STATUS_CLOSE);
  // 正常にステータスを変更できた場合は変更完了メッセージをセッション変数に格納
  set_message('ステータスを変更しました。');
}else {
  // ステータスを変更できなかった場合はエラーメッセージをセッション変数に格納
  set_error('不正なリクエストです。');
}


// リダイレクト用関数を利用して商品管理ページにリダイレクト
redirect_to(ADMIN_URL);