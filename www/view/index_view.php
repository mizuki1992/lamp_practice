<?php header("X-FRAME-OPTIONS: DENY"); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  
  <title>商品一覧</title>
  <link rel="stylesheet" href="<?php print(h(STYLESHEET_PATH . 'index.css')); ?>">
</head>
<body>
  <?php include VIEW_PATH . 'templates/header_logined.php'; ?>
  

  <div class="container">
    <h1>商品一覧</h1>
    <?php include VIEW_PATH . 'templates/messages.php'; ?>

    <form name='sortForm'>
      <select id="sort" name="sort">
        <option <?php if($sort === 'new'){ print h('selected');} ?> value="new">新着順</option>
        <option <?php if($sort === 'cheap'){ print h('selected');} ?> value="cheap">価格の安い順</option>
        <option <?php if($sort === 'expensive'){ print h('selected');} ?> value="expensive">価格の高い順</option>
      </select>
      <input type="submit" value="並び替え">
    </form>

    <div class="card-deck">
      <div class="row">
      <?php foreach($items as $item){ ?>
        <div class="col-6 item">
          <div class="card h-100 text-center">
            <div class="card-header">
              <?php print(h($item['name'])); ?>
            </div>
            <figure class="card-body">
              <img class="card-img" src="<?php print(h(IMAGE_PATH . $item['image'])); ?>">
              <figcaption>
                <?php print(h(number_format($item['price']))); ?>円
                <?php if($item['stock'] > 0){ ?>
                  <form action="index_add_cart.php" method="post">
                    <input type="submit" value="カートに追加" class="btn btn-primary btn-block">
                    <input type="hidden" name="item_id" value="<?php print(h($item['item_id'])); ?>">
                    <input type="hidden" name="sort" value="<?php print h($sort); ?>">
                    <input type="hidden" name="current_page" value="<?php print h($current_page); ?>">
                    <input type="hidden" name="csrf_token" value="<?php print h($token); ?>">
                  </form>
                <?php } else { ?>
                  <p class="text-danger">現在売り切れです。</p>
                <?php } ?>
              </figcaption>
            </figure>
          </div>
        </div>
      <?php } ?>
      </div>
    </div>
    <div>
    <nav class="navbar">
      <ul class="pagination">
        <?php for($i = 1;$i <= $total_pages;$i++){ ?>
          <li class="page-item">
            <a class="page-link <?php $current_page === $i ? print h('current_page') : '' ; ?>" href="index.php?page=<?php print h($i); ?>&sort=<?php print h($sort); ?>"><?php print h($i); ?></a>
          </li>
        <?php } ?>
      </ul>
    </nav>
  </div>
  <script type='text/javascript' src='<?php print(h(JS_PATH . 'sort.js')); ?>'></script>
</body>
</html>