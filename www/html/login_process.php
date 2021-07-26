<?php
//  定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// userデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';

// ログインチェックを行うため、セッションを開始する
session_start();

// ログインチェック用関数を利用
if(is_logined() === true){
  // ログインできていた場合はホームページにリダイレクト
  redirect_to(HOME_URL);
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

// post値取得用関数を利用してpostされたユーザー名を取得
$name = get_post('name');
// post値取得用関数を利用してpostされたパスワードを取得
$password = get_post('password');

// PDO取得
$db = get_db_connect();


// ユーザー情報取得用関数を利用してpostされたユーザー名、パスワードからユーザー情報を取得する
$user = login_as($db, $name, $password);
// ユーザー情報が取得できているか確認する
if( $user === false){
  // ユーザー情報が取得できなかった場合はエラーメッセージをセッション変数に格納
  set_error('ログインに失敗しました。');
  // リダイレクト用関数を利用してログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

// ログインできている場合はログイン完了メッセージをセッション変数に格納
set_message('ログインしました。');
// ユーザータイプが管理者であるか確認する
if ($user['type'] === USER_TYPE_ADMIN){
  // 管理者である場合はリダイレクト用関数を利用して管理者ページにリダイレクト
  redirect_to(ADMIN_URL);
}

// リダイレクト用関数を利用してホームページにリダイレクト
redirect_to(HOME_URL);