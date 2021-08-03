<?php header("X-FRAME-OPTIONS: DENY"); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  
  <title>購入明細</title>
  <link rel="stylesheet" href="<?php print(h(STYLESHEET_PATH . 'index.css')); ?>">
</head>
<body>
  <?php include VIEW_PATH . 'templates/header_logined.php'; ?>

  <h1>購入明細</h1>
  
    <div class="container">
        <?php include VIEW_PATH . 'templates/messages.php'; ?>

        <table class="table table-bordered text-center">
            <thead class="thead-light">
                <tr>
                    <th>注文番号</th>
                    <th>購入日時</th>
                    <th>合計金額</th>
                </tr>
            </thead>
            <tbody>
                <td><?php print(h($history['history_id'])); ?></td>
                <td><?php print(h($history['created'])); ?></td>
                <td><?php print(h(number_format($history['total_price']))); ?>円</td>
            </tbody>
        </table>


        <table class="table table-bordered text-center"> 
            <thead class="thead-light">
                <tr>
                    <th>商品名</th>
                    <th>商品価格</th>
                    <th>購入数</th>
                    <th>小計</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($history_details as $history_detail){ ?>
                    <tr>
                        <td><?php print(h($history_detail['name'])); ?></td>
                        <td><?php print(h(number_format($history_detail['price']))); ?>円</td>
                        <td><?php print(h($history_detail['amount'])); ?></td>
                        <td><?php print(h(number_format($history_detail['price'] * $history_detail['amount']))); ?>円</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>