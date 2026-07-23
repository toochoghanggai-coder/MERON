<?php require_once __DIR__ . '/common/auth.php'; require_login(); ?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>メニュー</title>
  <link rel="stylesheet" href="<?=asset_url('invoice_system/style.css')?>">
</head>
<body>
<div class="topbar no-print"><div class="topbar__inner"><div><strong>業務メニュー</strong></div><nav><a href="logout.php">ログアウト</a></nav></div></div>
<div class="container">
  <h1>メニュー</h1>
  <div class="card menu-card no-print">
    <a class="menu-tile" href="invoice_system/invoice_list.php">
      <span class="menu-tile__title">請求書</span>
      <span class="menu-tile__desc">請求書の検索・作成・印刷</span>
    </a>
    <a class="menu-tile" href="receipt_system/receipt_create.php">
      <span class="menu-tile__title">領収書</span>
      <span class="menu-tile__desc">領収書の検索・作成・印刷</span>
    </a>
  </div>
</div>
</body>
</html>
