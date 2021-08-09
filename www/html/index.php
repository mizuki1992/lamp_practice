<?php
//  定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// userデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';
// itemデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'item.php';

// ログインチェックを行うため、セッションを開始する
session_start();

// ログインチェック用関数を利用
if(is_logined() === false){
  // ログインできていない場合はログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

// PDO取得
$db = get_db_connect();
// PDOを利用してログインユーザーのデータを取得
$user = get_login_user($db);

$sort = get_get('sort');
if($sort === ''){
  $sort = 'new';
}

// 商品一覧用の商品データを取得
$items = get_open_items($db,$sort);

// トークン生成用関数を利用してトークンを取得
$token = get_csrf_token();

// ビューファイルの読み込み
include_once VIEW_PATH . 'index_view.php';