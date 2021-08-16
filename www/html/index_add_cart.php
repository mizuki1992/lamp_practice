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

// post値取得用関数を利用してpost送信された並べ替えの値を取得
$sort = get_post('sort');
// post値取得用関数を利用してpost送信された現在のページ数を取得
$current_page = get_post('current_page');

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

// 並べ替え機能、ページ数を変更している場合は、カート追加処理後にリダイレクト用関数を利用して並べ替えの値とページ数を保持したページにリダイレクト
if($sort !== '' && is_null($current_page) !== true){
  redirect_to(HOME_URL."?page=".$current_page."&sort=".$sort);
}
// 並べ替え機能、ページ数を変更していない場合はカート追加処理後、リダイレクト用関数を利用してindex.phpにリダイレクト
redirect_to(HOME_URL);
