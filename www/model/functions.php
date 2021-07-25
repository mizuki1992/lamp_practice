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
// 引数１：値をセットさせたいキー
// 引数２：セットしたい値
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

function has_error(){
  return isset($_SESSION['__errors']) && count($_SESSION['__errors']) !== 0;
}

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

function is_logined(){
  return get_session('user_id') !== '';
}

function get_upload_filename($file){
  if(is_valid_upload_image($file) === false){
    return '';
  }
  $mimetype = exif_imagetype($file['tmp_name']);
  $ext = PERMITTED_IMAGE_TYPES[$mimetype];
  return get_random_string() . '.' . $ext;
}

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



function is_valid_length($string, $minimum_length, $maximum_length = PHP_INT_MAX){
  $length = mb_strlen($string);
  return ($minimum_length <= $length) && ($length <= $maximum_length);
}

function is_alphanumeric($string){
  return is_valid_format($string, REGEXP_ALPHANUMERIC);
}

function is_positive_integer($string){
  return is_valid_format($string, REGEXP_POSITIVE_INTEGER);
}

function is_valid_format($string, $format){
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
