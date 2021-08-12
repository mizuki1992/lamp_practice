<?php
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// データベース関数ファイルを読み込み
require_once MODEL_PATH . 'db.php';

// DB利用

// 商品データ取得用関数
// 引数１：PDO利用
// 引数２：データを取得したい商品ID
// 戻り値：クエリ読み込み用関数（取得データはひとつ）を利用して取得したデータ
function get_item($db, $item_id){
  // itemテーブルから引数２の商品IDの商品ID、商品名、在庫数、値段、商品画像ファイル名、公開ステータスを取得するSQL文
  $sql = "
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
    WHERE
      item_id = :item_id
  ";

  // クエリ読み込み用関数（取得データはひとつ）を利用して取得したデータを返す
  return fetch_query($db, $sql, array(':item_id' => $item_id));
}

// 商品データ取得用関数（公開ステータスで切り分け）
// 引数１：PDO利用
// 引数２：公開ステータス
// 戻り値：クエリ読み込み用関数（取得データは全て）を利用して取得したデータ
function get_items($db, $is_open = false, $sort = '', $page = NULL){
  // itemテーブルから全ての商品データを取得
  $sql = '
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
  ';
  // ステータスが公開の場合
  if($is_open === true){
    // 上のSQL文にステータスの条件を追加
    $sql .= '
      WHERE status = 1
    ';
  }
  // 新着順で並べる場合
  if($sort === 'new'){
      // 上のSQL文に新着順の条件を追加
      $sql .= '
        ORDER BY
         created DESC
      ';
  // 安い順で並べる場合
  }elseif($sort === 'cheap'){
      // 上のSQL文に安い順の条件を追加
      $sql .= '
        ORDER BY
         price ASC
      ';
  // 高い順で並べる場合
  }elseif($sort === 'expensive'){
      // 上のSQL文に高い順の条件を追加
      $sql .= '
        ORDER BY
         price DESC
      ';
  }
  // ページ数に値が入っていたら、８件分を表示させるSQL文を追加
  if(is_null($page) !== TRUE){
    $sql .= '
    LIMIT :start_item,8;
  ';
    // 表示させる商品の開始位置を指定
    $start_item = ((int)$page - 1) * 8;
    // クエリ読み込み用関数（取得データは全て）を利用して取得したデータを返す
    return fetch_all_query($db, $sql, array(':start_item' => $start_item));
  } else {
    // ページ数に値が入ってい無い場合は、全件表示させる
    // クエリ読み込み用関数（取得データは全て）を利用して取得したデータを返す
    return fetch_all_query($db, $sql);
  }
}

// 現在のページ数取得用関数
function get_current_page($page){
  // $_GET['page']から値を取得して整数型に変換
  $current_page = (int)(get_get('page'));
  // ページ数に値が入っていない場合、文字列が入っていた場合は、１ページ目とする
  if($current_page === 0){
    $current_page = 1;
  }
  return $current_page;
}

// 商品数取得用関数
function count_items($db){
  // count(*)で商品数を取得して、items_amountとして別名をつける
  $sql = '
    SELECT
      count(*) AS items_amount
    FROM
      items
    WHERE
      status = 1
  ';
  // クエリ読み込み用関数（取得データはひとつ）を利用して取得した商品数を返す
  return fetch_query($db, $sql);
}


// 全ての商品データ取得用関数
// 引数：PDO利用
// 戻り値：商品データ取得用関数（公開ステータスで切り分け）を利用して取得したデータを返す
function get_all_items($db){
  // 商品データ取得用関数（公開ステータスで切り分け）を利用
  // 公開ステータスに関係なく全てのデータを取得するため引数２は省略
  return get_items($db);
}

// ステータスが公開になっている商品データ取得用関数
// 引数：PDO利用
// 戻り値：商品データ取得用関数（公開ステータスで切り分け）を利用してステータスが公開のデータ
function get_open_items($db, $sort, $page){
  // 商品データ取得用関数（公開ステータスで切り分け）を利用
  // 公開ステータスの商品を取得するため引数２を公開
  return get_items($db, true, $sort, $page);
}


function regist_item($db, $name, $price, $stock, $status, $image){
  $filename = get_upload_filename($image);
  if(validate_item($name, $price, $stock, $filename, $status) === false){
    return false;
  }
  return regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename);
}

function regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename){
  $db->beginTransaction();
  if(insert_item($db, $name, $price, $stock, $filename, $status) 
    && save_image($image, $filename)){
    $db->commit();
    return true;
  }
  $db->rollback();
  return false;
  
}

function insert_item($db, $name, $price, $stock, $filename, $status){
  $status_value = PERMITTED_ITEM_STATUSES[$status];
  $sql = "
    INSERT INTO
      items(
        name,
        price,
        stock,
        image,
        status
      )
    VALUES(:name, :price, :stock, :filename, :status_value);
  ";

  return execute_query($db, $sql, array(':name' => $name, ':price' => $price, ':stock' => $stock, ':filename' => $filename, ':status_value' => $status_value));
}

function update_item_status($db, $item_id, $status){
  $sql = "
    UPDATE
      items
    SET
      status = :status
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  
  return execute_query($db, $sql, array(':status' => $status, ':item_id' => $item_id));
}

function update_item_stock($db, $item_id, $stock){
  $sql = "
    UPDATE
      items
    SET
      stock = :stock
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  
  return execute_query($db, $sql, array(':stock' => $stock, ':item_id' => $item_id));
}

function destroy_item($db, $item_id){
  $item = get_item($db, $item_id);
  if($item === false){
    return false;
  }
  $db->beginTransaction();
  if(delete_item($db, $item['item_id'])
    && delete_image($item['image'])){
    $db->commit();
    return true;
  }
  $db->rollback();
  return false;
}

function delete_item($db, $item_id){
  $sql = "
    DELETE FROM
      items
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  
  return execute_query($db, $sql, array(':item_id' => $item_id));
}


// 非DB

function is_open($item){
  return $item['status'] === 1;
}

function validate_item($name, $price, $stock, $filename, $status){
  $is_valid_item_name = is_valid_item_name($name);
  $is_valid_item_price = is_valid_item_price($price);
  $is_valid_item_stock = is_valid_item_stock($stock);
  $is_valid_item_filename = is_valid_item_filename($filename);
  $is_valid_item_status = is_valid_item_status($status);

  return $is_valid_item_name
    && $is_valid_item_price
    && $is_valid_item_stock
    && $is_valid_item_filename
    && $is_valid_item_status;
}

function is_valid_item_name($name){
  $is_valid = true;
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_price($price){
  $is_valid = true;
  if(is_positive_integer($price) === false){
    set_error('価格は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_stock($stock){
  $is_valid = true;
  if(is_positive_integer($stock) === false){
    set_error('在庫数は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_filename($filename){
  $is_valid = true;
  if($filename === ''){
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_status($status){
  $is_valid = true;
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){
    $is_valid = false;
  }
  return $is_valid;
}

