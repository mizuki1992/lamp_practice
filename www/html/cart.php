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

// ログインチェックを行うためセッションを開始する
session_start();

// ログインチェック用関数を利用
if(is_logined() === false){
  // ログインしていない場合はログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

// PDO取得
$db = get_db_connect();

// PDOを利用してログインユーザーデータを取得 
$user = get_login_user($db);

// カートの中身取得用関数を利用してユーザーのカートの中身を取得する
$carts = get_user_carts($db, $user['user_id']);

// カートの合計金額算出用関数を利用してカートの合計金額を算出する
$total_price = sum_carts($carts);

// トークン生成用関数を利用してトークンを取得
$token = get_csrf_token();

// ビューファイルの読み込み
include_once VIEW_PATH . 'cart_view.php';