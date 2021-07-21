<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// ログインチェックを行うため、セッションを開始する
session_start();

// セッション変数に保持している全ての情報を初期化する
$_SESSION = array();
// セッションクッキーのパラメータを取得する
$params = session_get_cookie_params();

// ブラウザに保存されているクッキー情報を無効にする
// session_name()はクッキーに保存されているセッションIDを無効にする
// ''はクッキーに保存されている値を無効にする
// time()-42000はクッキーの期限を過去にする
setcookie(session_name(), '', time() - 42000,
  // クッキー情報が保存されているパスを無効にする
  $params["path"], 
  // クッキーが有効なドメインを無効にする
  $params["domain"],
  // クッキーのセキュアフラグを無効にする
  $params["secure"],
  // クッキーの通信フラグを無効にする 
  $params["httponly"]
);

// セッションに関連づけられた全てのデータを破棄
session_destroy();

// リダイレクト用関数を利用してログインページにリダレクト
redirect_to(LOGIN_URL);

