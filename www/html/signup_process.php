<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// userデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';

// ログインチェックを行うため、セッションを開始する
session_start();

// ログインチェック用関数を利用
if(is_logined() === true){
  // ログインできていない場合はホームページにリダイレクト
  redirect_to(HOME_URL);
}

// post値取得用関数を利用してpostされたユーザー名を取得
$name = get_post('name');
// post値取得用関数を利用してpostされたパスワードを取得
$password = get_post('password');
// post値取得用関数を利用してpostされた確認用パスワードを取得
$password_confirmation = get_post('password_confirmation');

// PDO取得
$db = get_db_connect();


try{
  // ユーザー登録用関数を利用してpostされた入力値を確認する
  $result = regist_user($db, $name, $password, $password_confirmation);
  // postされた入力値で登録できなかった場合
  if( $result=== false){
    // エラーメッセージをセッション変数に格納
    set_error('ユーザー登録に失敗しました。');
    // リダイレクト用関数を利用してサインアップページにリダイレクト
    redirect_to(SIGNUP_URL);
  }
  // try内で例外があった場合
}catch(PDOException $e){
  // エラーメッセージをセッション変数に格納
  set_error('ユーザー登録に失敗しました。');
  // リダイレクト用関数を利用してサインアップページにリダイレクト
  redirect_to(SIGNUP_URL);
}

// postされた入力値でユーザー登録できた場合は登録完了メッセージをセッション変数に格納
set_message('ユーザー登録が完了しました。');
// ユーザー情報取得用関数を利用してpostされたユーザー名、パスワードからユーザー情報を取得する
login_as($db, $name, $password);
// リダイレクト用関数を利用してホームページにリダイレクト
redirect_to(HOME_URL);