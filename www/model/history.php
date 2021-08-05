<?php
// 汎用関数ファイルの読み込み
require_once MODEL_PATH . 'functions.php';
// データベース接続用関数ファイルの読み込み
require_once MODEL_PATH . 'db.php';

// purchase_historyテーブルからユーザごとの情報を取得
function get_purchase_histories($db,$user_id){
    $sql = '
    SELECT
      history_id,
      created,
      total_price
    FROM
      purchase_history
    WHERE
      user_id = :user_id
    ORDER BY
      created DESC';
    
    return fetch_all_query($db, $sql, array(':user_id' => $user_id));
}


// purchase_historyテーブルから全情報を取得（adminユーザー）
function get_purchase_histories_all($db){
  $sql = '
  SELECT
    history_id,
    created,
    total_price
  FROM
    purchase_history
  ORDER BY
    created DESC';
  
  return fetch_all_query($db, $sql);
}

// history_idを条件にpurchase_historyテーブルから購入履歴を取得
function get_purchase_history($db,$history_id){
  $sql = '
  SELECT
    history_id,
    created,
    total_price
  FROM
    purchase_history
  WHERE
    history_id = :history_id';

  return fetch_query($db, $sql, array(':history_id' => $history_id));
}

// history_idを条件にpurchase_history_detailテーブルから購入明細を取得
function get_purchase_history_detail($db,$history_id){
  $sql = '
  SELECT
    items.name,
    purchase_history_detail.price,
    purchase_history_detail.amount
  FROM
    purchase_history_detail
  INNER JOIN 
    items
  ON
    purchase_history_detail.item_id = items.item_id
  WHERE
    history_id = :history_id';

  return fetch_all_query($db, $sql, array(':history_id' => $history_id));
}