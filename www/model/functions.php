<?php

// 変数の値を確認する関数
// 引数：表示させたい変数
function dd($var){
  // $varの値を表示させる
  var_dump($var);
  // 表示させたら処理終了
  exit();
}

// リダイレクト用関数
// 引数：リダイレクトさせたい各ページ（定数ファイルで定義）
function redirect_to($url){
  // リダイレクトさせたいページへリダイレクト
  header('Location: ' . $url);
  // リダイレクトしたら処理終了
  exit;
}

// get値取得用関数
// 引数；取得したいget値
function get_get($name){
  // 値が存在するか確認
  if(isset($_GET[$name]) === true){
    // 値が存在する場合
    // 戻り値：getで取得した値
    return $_GET[$name];
  };
  // 値が存在しない場合
  // 戻り値：空文字を返す
  return '';
}

// post値取得用関数
// 引数：取得したいpost値
function get_post($name){
  // 値が存在するか確認
  if(isset($_POST[$name]) === true){
    // 値が存在する場合
    // 戻り値：postで取得した値
    return $_POST[$name];
  };
  // 値が存在しない場合
  // 戻り値：空文字を返す
  return '';
}

// HTTPPOSTでのファイルアップロードチェック関数
// 引数：アップロードしたファイル名
function get_file($name){
  // 値が存在するか確認
  if(isset($_FILES[$name]) === true){
    // 値が存在する場合
    // 戻り値：アップロードされたファイル名
    return $_FILES[$name];
  };
  // 値が存在しない場合
  // 戻り値：空の配列を返す
  return array();
}

// セッション変数に保存されている値取得用関数
// 引数：取得したいセッション変数
function get_session($name){
  // 値が存在するか確認
  if(isset($_SESSION[$name]) === true){
    // 値が存在する場合
    // 戻り値：セッション変数に保存されている値
    return $_SESSION[$name];
  };
  // 値が存在しない場合
  // 戻り値：空文字を返す
  return '';
}

// セッション変数に値をセット用関数
// 引数１：値をセットさせたいキー、引数２：セットしたい値
function set_session($name, $value){
  // セッション変数のキーに値をセット
  $_SESSION[$name] = $value;
}

// 
function set_error($error){
  $_SESSION['__errors'][] = $error;
}

function get_errors(){
  $errors = get_session('__errors');
  if($errors === ''){
    return array();
  }
  set_session('__errors',  array());
  return $errors;
}

// セッションに保存されているエラーの有無確認用関数
function has_error(){
  // エラーが存在して、尚且つエラーが０では無い場合はtrueを返す
  return isset($_SESSION['__errors']) && count($_SESSION['__errors']) !== 0;
}

// 
function set_message($message){
  $_SESSION['__messages'][] = $message;
}

function get_messages(){
  $messages = get_session('__messages');
  if($messages === ''){
    return array();
  }
  set_session('__messages',  array());
  return $messages;
}

// ログインチェック用関数
function is_logined(){
  // セッション変数に保存されている値取得用関数を利用して$_SESSION['user_id']が空文字ではない場合trueを返す
  return get_session('user_id') !== '';
}

// アップロードされたファイル名取得用関数
function get_upload_filename($file){
  if(is_valid_upload_image($file) === false){
    return '';
  }
  $mimetype = exif_imagetype($file['tmp_name']);
  $ext = PERMITTED_IMAGE_TYPES[$mimetype];
  return get_random_string() . '.' . $ext;
}

// ランダムな文字列取得用関数
// 引数：文字数（省略した場合20文字）
function get_random_string($length = 20){
  return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
}

function save_image($image, $filename){
  return move_uploaded_file($image['tmp_name'], IMAGE_DIR . $filename);
}

function delete_image($filename){
  if(file_exists(IMAGE_DIR . $filename) === true){
    unlink(IMAGE_DIR . $filename);
    return true;
  }
  return false;
  
}


// 文字数確認用関数
// 引数１：文字数を取得したい文字列、引数２：最小文字数、引数３：最大文字数（PHP がサポートする整数型の最大値）
function is_valid_length($string, $minimum_length, $maximum_length = PHP_INT_MAX){
  // mb_strlen関数を利用して文字数を取得したい文字列の文字数を取得
  $length = mb_strlen($string);
  // 取得した文字列が最小文字数以上で、最大文字数以下である場合trueを返す
  return ($minimum_length <= $length) && ($length <= $maximum_length);
}

// 半角英数字チェック用関数
// 引数：半角英数字で入力されているかチェックしたい文字列
function is_alphanumeric($string){
  // 入力された文字列の形式チェック用関数を利用し、定義済みの正規表現パターンとマッチする場合はtrueを返す
  return is_valid_format($string, REGEXP_ALPHANUMERIC);
}

// 正整数チェック用関数
// 引数：正整数で入力されているかチェックしたい文字列
function is_positive_integer($string){
  // 入力された文字列の形式チェック用関数を利用し、定義済みの正規表現パターンとマッチする場合はtrueを返す
  return is_valid_format($string, REGEXP_POSITIVE_INTEGER);
}

// 入力された文字列の形式チェック用関数
// 引数１：形式チェックする文字列、引数２：形式チェックで使用する正規表現のパターン
function is_valid_format($string, $format){
  // preg_match関数で正規表現を使用して文字列が有効であった場合「１」になり、trueを返す
  return preg_match($format, $string) === 1;
}


function is_valid_upload_image($image){
  if(is_uploaded_file($image['tmp_name']) === false){
    set_error('ファイル形式が不正です。');
    return false;
  }
  $mimetype = exif_imagetype($image['tmp_name']);
  if( isset(PERMITTED_IMAGE_TYPES[$mimetype]) === false ){
    set_error('ファイル形式は' . implode('、', PERMITTED_IMAGE_TYPES) . 'のみ利用可能です。');
    return false;
  }
  return true;
}

function h($str){
  $str = htmlspecialchars($str , ENT_QUOTES, 'UTF-8');
  return $str;
}

// トークンの生成
function get_csrf_token(){
  // get_random_string()はユーザー定義関数。
  $token = get_random_string(30);
  // set_session()はユーザー定義関数。
  set_session('csrf_token', $token);
  return $token;
}

// トークンのチェック
function is_valid_csrf_token($token){
  if($token === '') {
    return false;
  }
  // get_session()はユーザー定義関数
  return $token === get_session('csrf_token');
}
