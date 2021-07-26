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
  // ログインできていない場合はログインページにリダイレクト
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

// カートの中身取得用関数を利用してユーザーのカートの中身を取得する
$carts = get_user_carts($db, $user['user_id']);

// 商品購入用関数を利用してステータスが非公開に変更されていないか、在庫数が足りているか確認する
if(purchase_carts($db, $carts) === false){
  // 商品が購入できなかった場合はエラーメッセージをセッション変数に格納
  set_error('商品が購入できませんでした。');
  // リダイレクト用関数を利用してカートページにリダイレクト
  redirect_to(CART_URL);
} 

// カートの合計金額算出用関数を利用してカートの合計金額を算出する
$total_price = sum_carts($carts);

// ビューファイルの読み込み
include_once '../view/finish_view.php';