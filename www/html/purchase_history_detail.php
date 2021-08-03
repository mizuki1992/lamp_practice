<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// userデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';
// itemデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'item.php';
// 購入履歴データに関する関数ファイルを読み込み
require_once MODEL_PATH . 'history.php';

// ログインチェックを行うためセッションを開始する
session_start();

// ログインチェック用関数を利用
if(is_logined() === false){
  // ログインしていない場合はログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

// PDO取得
$db = get_db_connect();

// PDOを利用してログインユーザのデータを取得
$user = get_login_user($db);

// get値取得用関数を利用して送信されたhistory_idを取得
$history_id = get_get('history_id');
 
// 注文番号、購入日時、合計金額を取得する
$history = get_purchase_history($db,$history_id);

// 注文番号を条件に商品名、購入時の商品価格、購入数を取得する
$history_details = get_purchase_history_detail($db,$history_id);



// ビューファイルの読み込み
include_once VIEW_PATH . '/purchase_history_detail_view.php';
