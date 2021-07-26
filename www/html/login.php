<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// ログインチェックを行うため、セッションを開始する
session_start();

// ログインチェック用関数を利用
if(is_logined() === true){
  // ログインできている場合はホームページにリダイレクト
  redirect_to(HOME_URL);
}

// トークン生成用関数を利用してトークンを取得
$token = get_csrf_token();

// ビューファイルの読み込み
include_once VIEW_PATH . 'login_view.php';