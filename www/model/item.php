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
// 引数：ページ数
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
// 引数：PDO利用
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
// 引数１：PDO利用、引数２：並べ替え値、引数３：ページ数
// 戻り値：商品データ取得用関数（公開ステータスで切り分け）を利用してステータスが公開のデータ
function get_open_items($db, $sort, $page){
  // 商品データ取得用関数（公開ステータスで切り分け）を利用
  // 公開ステータスの商品を取得するため引数２を公開
  return get_items($db, true, $sort, $page);
}

// 商品登録用関数
// 引数１：PDO利用、引数２：商品名、引数３：商品価格、引数４：在庫数、引数５：公開ステータス、引数６：商品画像
function regist_item($db, $name, $price, $stock, $status, $image){
  // アップロードされたファイル名取得用関数を利用してファイル名を取得
  $filename = get_upload_filename($image);
  // 入力された商品情報チェック用関数を利用して不正な値が入っていないか確認
  if(validate_item($name, $price, $stock, $filename, $status) === false){
    // 入力された商品情報のいずれかに不正な値が入っていた場合はfalseを返す
    return false;
  }
  // 商品情報が正しく入力されていた場合は商品登録処理用関数を利用して商品情報を追加する
  return regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename);
}

// 商品登録処理用関数
// 引数１：PDO利用、引数２：商品名、引数３：商品価格、引数４：在庫数、引数５：公開ステータス、引数６：商品画像、引数７：変換された画像ファイル名
function regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename){
  // トランザクション開始
  $db->beginTransaction();
  // 商品情報追加用関数を利用してitemsテーブルに商品情報を追加、アップロードされた画像ファイル保存用関数を利用して画像ファイルを保存
  if(insert_item($db, $name, $price, $stock, $filename, $status) 
    && save_image($image, $filename)){
    // 商品情報追加、画像ファイルの保存が正しく実行される場合は処理確定（コミット）
    $db->commit();
    // 商品情報追加、画像ファイルの保存が正しく実行された場合はtrueを返す
    return true;
  }
  // 商品情報追加、画像ファイルの保存が正しく実行されなかった場合は処理取り消し（ロールバック）
  $db->rollback();
  // 商品情報追加、画像ファイルの保存が正しく実行されなかった場合はfalseを返す
  return false;
  
}

// 商品情報追加用関数
// 引数１：PDO利用、引数２：商品名、引数３：商品価格、引数４：在庫数、引数５：変換された画像ファイル名、引数６：公開ステータス
function insert_item($db, $name, $price, $stock, $filename, $status){
  // 公開ステータスの値をopen、closeから1、0に変換
  $status_value = PERMITTED_ITEM_STATUSES[$status];
  // itemテーブルに商品名、商品価格、在庫数、アップロードされた画像ファイル名、公開ステータスを追加するSQL文
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
  // クエリ実行用関数を利用してSQL文を実行してtrueを返す
  return execute_query($db, $sql, array(':name' => $name, ':price' => $price, ':stock' => $stock, ':filename' => $filename, ':status_value' => $status_value));
}

// 公開ステータス変更用関数
// 引数１：PDO利用、引数２：商品ID、引数３：変更後の公開ステータス
function update_item_status($db, $item_id, $status){
  //  itemsテーブルの公開ステータスを更新するSQL文
  $sql = "
    UPDATE
      items
    SET
      status = :status
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  // クエリ実行用関数を利用してSQL文を実行してtrueを返す
  return execute_query($db, $sql, array(':status' => $status, ':item_id' => $item_id));
}

// 在庫数変更用関数
// 引数１：PDO利用、引数２：商品ID、引数３：変更後の在庫数
function update_item_stock($db, $item_id, $stock){
  //  itemsテーブルの在庫数を更新するSQL文
  $sql = "
    UPDATE
      items
    SET
      stock = :stock
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  // クエリ実行用関数を利用してSQL文を実行してtrueを返す
  return execute_query($db, $sql, array(':stock' => $stock, ':item_id' => $item_id));
}

// 商品情報削除用関数
// 引数１：PDO利用、引数２：商品ID
function destroy_item($db, $item_id){
  // 商品データ取得用関数を利用して削除したい商品情報を取得
  $item = get_item($db, $item_id);
  // 商品情報が無い場合はfalseを返す
  if($item === false){
    return false;
  }
  // 商品情報がある場合はトランザクション開始
  $db->beginTransaction();
  // テーブルから商品情報削除用関数を利用して該当商品IDの商品情報を削除、保存済み画像ファイル削除用関数を利用して保存場所から画像ファイルを削除
  if(delete_item($db, $item['item_id'])
    && delete_image($item['image'])){
    // 商品情報削除、画像ファイルの削除が正しく実行できる場合は処理確定（コミット）
    $db->commit();
    // 商品情報削除、画像ファイルの削除が正しく実行できた場合はtrueを返す
    return true;
  }
  // 商品情報削除、画像ファイルの削除が正しく実行されなかった場合は処理取り消し（ロールバック）
  $db->rollback();
  // 商品情報削除、画像ファイルの削除が正しく実行されなかった場合はfalseを返す
  return false;
}

// テーブルから商品情報削除用関数
// 引数１：PDO利用、引数２：削除したい商品ID
function delete_item($db, $item_id){
  //  itemsテーブルの削除したい商品IDの商品情報を削除するSQL文
  $sql = "
    DELETE FROM
      items
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  // クエリ実行用関数を利用してSQL文を実行してtrueを返す
  return execute_query($db, $sql, array(':item_id' => $item_id));
}


// 非DB

// 公開ステータス確認用関数
// 引数：取得してきた商品データ
function is_open($item){
  // 取得してきた商品データの公開ステータスが１だったらtrueを返す
  return $item['status'] === 1;
}

// 入力された商品情報チェック用関数
// 引数１：入力された商品名、引数２：入力された商品価格、引数３：入力された在庫数、引数４：アップロードされた画像ファイル名、引数５：選択された公開ステータス
function validate_item($name, $price, $stock, $filename, $status){
  // 入力された商品名チェック用関数を利用、正しく商品名が入力されていたらtrue、正しくない場合はfalseが返る
  $is_valid_item_name = is_valid_item_name($name);
  // 入力された商品価格チェック用関数を利用、正しく商品価格が入力されていたらtrue、正しくない場合はfalseが返る
  $is_valid_item_price = is_valid_item_price($price);
  // 入力された在庫数チェック用関数を利用、正しく在庫数が入力されていたらtrue、正しくない場合はfalseが返る
  $is_valid_item_stock = is_valid_item_stock($stock);
  // // アップロードされた画像ファイル名チェック用関数を利用、ファイル形式が正しい場合はtrue、正しくない場合はfalseが返る
  $is_valid_item_filename = is_valid_item_filename($filename);
  // 選択された公開ステータスチェック用関数を利用、正しく公開ステータスが選択されていたらtrue、正しくない場合はfalseが返る
  $is_valid_item_status = is_valid_item_status($status);

  // 入力された商品名、入力された商品価格、入力された在庫数、、選択された公開ステータスが全てtrueであるか確認
  // 全てtrueである場合はtrueを返す、一つでもfalseがある場合はfalseを返す
  return $is_valid_item_name
    && $is_valid_item_price
    && $is_valid_item_stock
    && $is_valid_item_filename
    && $is_valid_item_status;
}

// 入力された商品名チェック用関数
// 引数：入力された商品名
function is_valid_item_name($name){
  // 判定用にtrueを代入
  $is_valid = true;
  // 文字数確認用関数を利用、商品名の最小文字数以上、最大文字数以内ではない場合はfalseが返ってくる
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){
    // 商品名の最小文字数以上、最大文字数以内ではない場合はエラーメッセージ格納用関数を利用してエラーメッセージを格納
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。');
    // 戻り値用のfalseを代入
    $is_valid = false;
  }
  // チェック判定結果をtrueかfalseで返す
  return $is_valid;
}

// 入力された商品価格チェック用関数
// 引数：入力された商品価格
function is_valid_item_price($price){
  // 判定用にtrueを代入
  $is_valid = true;
  // 正整数チェック用関数関数を利用、正整数で入力されていない場合はfalseが返ってくる
  if(is_positive_integer($price) === false){
    // 正整数で入力されていない場合はエラーメッセージ格納用関数を利用してエラーメッセージを格納
    set_error('価格は0以上の整数で入力してください。');
    // 戻り値用のfalseを代入
    $is_valid = false;
  }
  // チェック判定結果をtrueかfalseで返す
  return $is_valid;
}

// 入力された在庫数チェック用関数
// 引数：入力された在庫数
function is_valid_item_stock($stock){
  // 判定用にtrueを代入
  $is_valid = true;
  // 正整数チェック用関数関数を利用、正整数で入力されていない場合はfalseが返ってくる
  if(is_positive_integer($stock) === false){
    // 正整数で入力されていない場合はエラーメッセージ格納用関数を利用してエラーメッセージを格納
    set_error('在庫数は0以上の整数で入力してください。');
    // 戻り値用のfalseを代入
    $is_valid = false;
  }
  // チェック判定結果をtrueかfalseで返す
  return $is_valid;
}

// アップロードされた画像ファイル名チェック用関数
// 引数：アップロードされた画像ファイル名
function is_valid_item_filename($filename){
  // 判定用にtrueを代入
  $is_valid = true;
  // アップロードされた画像ファイル名にファイル形式が正しくない場合は空文字が返ってくる
  if($filename === ''){
    // 戻り値用のfalseを代入
    $is_valid = false;
  }
  // チェック判定結果をtrueかfalseで返す
  return $is_valid;
}

// 選択された公開ステータスチェック用関数
//  引数：選択された公開ステータス（openかclose）
function is_valid_item_status($status){
  // 判定用にtrueを代入
  $is_valid = true;
  // 公開ステータスに値が存在するか確認、存在しない場合はfalseが返ってくる
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){
    // 戻り値用のfalseを代入
    $is_valid = false;
  }
  // チェック判定結果をtrueかfalseで返す
  return $is_valid;
}

