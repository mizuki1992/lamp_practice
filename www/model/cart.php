<?php 
// 汎用関数ファイルの読み込み
require_once MODEL_PATH . 'functions.php';
// データベース接続用関数ファイルの読み込み
require_once MODEL_PATH . 'db.php';

// ユーザーのカートの中身取得用関数
// 引数１：PDO利用、引数２：ユーザーID
function get_user_carts($db, $user_id){
  // SQL文
  // 商品IDでcartsテーブルとitemsテーブルを結合させ、ユーザーIDを条件に
  // 商品ID、商品名、商品価格、在庫数、公開ステータス、商品画像、カートID、ユーザーID、購入数を取得
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = :user_id
  ";
  // SQL文を実行し、取得した全ての行を配列で返す
  return fetch_all_query($db, $sql, array(':user_id' => $user_id));
}

// 商品別のユーザーのカートの中身取得用関数
function get_user_cart($db, $user_id, $item_id){
  // SQL文
  // 商品IDでcartsテーブルとitemsテーブルを結合させ、ユーザーIDと商品IDを条件に
  // 商品ID、商品名、商品価格、在庫数、公開ステータス、商品画像、カートID、ユーザーID、購入数を取得
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = :user_id
    AND
      items.item_id = :item_id
  ";

  // SQL文を実行し、取得した行を配列で返す
  return fetch_query($db, $sql, array(':user_id' => $user_id, ':item_id' => $item_id));

}

// カート追加用関数
function add_cart($db, $user_id, $item_id ) {
  // 商品別のユーザーのカートの中身取得用関数を利用してカートの中身を取得
  $cart = get_user_cart($db, $user_id, $item_id);
  // カートの中身が空だった場合
  if($cart === false){
    return insert_cart($db, $user_id, $item_id);
  }
  // 
  return update_cart_amount($db, $cart['cart_id'], $cart['amount'] + 1);
}

// カート追加用関数(初回)
// 引数１：PDO利用、引数２：ユーザーID、引数３：商品ID、引数４：購入数に１
function insert_cart($db, $user_id, $item_id, $amount = 1){
  // SQL文
  // cartsテーブルに商品ID、ユーザーID、購入数を追加する
  $sql = "
    INSERT INTO
      carts(
        item_id,
        user_id,
        amount
      )
    VALUES(:item_id, :user_id, :amount)
  ";

  return execute_query($db, $sql, array(':item_id' => $item_id, ':user_id' => $user_id, ':amount' => $amount));
}

function update_cart_amount($db, $cart_id, $amount){
  $sql = "
    UPDATE
      carts
    SET
      amount = :amount
    WHERE
      cart_id = :cart_id
    LIMIT 1
  ";
  return execute_query($db, $sql, array(':amount' => $amount, ':cart_id' => $cart_id));
}

function delete_cart($db, $cart_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      cart_id = :cart_id
    LIMIT 1
  ";

  return execute_query($db, $sql, array(':cart_id' => $cart_id));
}

function purchase_carts($db, $carts){
  if(validate_cart_purchase($carts) === false){
    return false;
  }
  foreach($carts as $cart){
    if(update_item_stock(
        $db, 
        $cart['item_id'], 
        $cart['stock'] - $cart['amount']
      ) === false){
      set_error($cart['name'] . 'の購入に失敗しました。');
    }
  }
  
  delete_user_carts($db, $carts[0]['user_id']);
}

function delete_user_carts($db, $user_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      user_id = :user_id
  ";

  execute_query($db, $sql, array(':user_id' => $user_id));
}


function sum_carts($carts){
  $total_price = 0;
  foreach($carts as $cart){
    $total_price += $cart['price'] * $cart['amount'];
  }
  return $total_price;
}

function validate_cart_purchase($carts){
  if(count($carts) === 0){
    set_error('カートに商品が入っていません。');
    return false;
  }
  foreach($carts as $cart){
    if(is_open($cart) === false){
      set_error($cart['name'] . 'は現在購入できません。');
    }
    if($cart['stock'] - $cart['amount'] < 0){
      set_error($cart['name'] . 'は在庫が足りません。購入可能数:' . $cart['stock']);
    }
  }
  if(has_error() === true){
    return false;
  }
  return true;
}

// 購入履歴テーブルの追加
// 引数：$db、ユーザーID、合計金額
// 戻り値：処理が成功したらtrue、失敗したらfalse
function insert_purchase_history($db,$user_id,$total_price){
  $sql = "
  INSERT INTO 
    purchase_history(
      user_id,
      total_price
    )
    VALUES(:user_id, :total_price)
  ";
  return execute_query($db, $sql, array(':user_id' => $user_id, ':total_price' => $total_price));
}

// 履歴明細テーブルの追加
// 引数：$db、商品ID、単価、購入数、history_id
// 戻り値：処理が成功したらtrue、失敗したらfalse
function insert_purchase_history_detail($db,$item_id,$price,$amount,$history_id){
  $sql = "
  INSERT INTO 
    purchase_history_detail(
      item_id,
      price,
      amount,
      history_id
    )
    VALUES(:item_id, :price, :amount, :history_id)
  ";
  return execute_query($db, $sql, array(':item_id' => $item_id, ':price' => $price, ':amount' => $amount, ':history_id' => $history_id));
}