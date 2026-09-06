<?php
require_once __DIR__ . '/receipt_functions.php'; require_login(); require_once __DIR__ . '/receipt_view.php';
$customer_name = trim($_GET['customer_name'] ?? '');
$receipt_no = trim($_GET['receipt_no'] ?? '');
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$searched = isset($_GET['search']);
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10; $offset = ($page - 1) * $limit;
$where=[]; $params=[];
if ($searched) {
    if ($customer_name !== '') { $where[]='customer_name LIKE ?'; $params[]='%'.$customer_name.'%'; }
    if ($receipt_no !== '') { $where[]='receipt_no = ?'; $params[]=$receipt_no; }
    if ($date_from !== '') { $where[]='issue_date >= ?'; $params[]=$date_from; }
    if ($date_to !== '') { $where[]='issue_date <= ?'; $params[]=$date_to; }
}
$sqlWhere = $where ? ('WHERE '.implode(' AND ', $where)) : '';
$rows=[]; $totalRows=0;
if ($searched) {
    $stmt=db()->prepare("SELECT COUNT(*) c FROM receipts $sqlWhere"); $stmt->execute($params); $totalRows=(int)$stmt->fetch()['c'];
    $stmt=db()->prepare("SELECT * FROM receipts $sqlWhere ORDER BY issue_date DESC, id DESC LIMIT $limit OFFSET $offset"); $stmt->execute($params); $rows=$stmt->fetchAll();
}
$viewReceipt=null;
if (!empty($_GET['view_id'])) { $stmt=db()->prepare('SELECT * FROM receipts WHERE id=?'); $stmt->execute([(int)$_GET['view_id']]); $viewReceipt=$stmt->fetch(); }
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>領収書一覧</title><link rel="stylesheet" href="<?=asset_url('style.css')?>&receipt_v=20260906-09"><!-- v20260906-09: PCプレビュー下部の見切れ修正版CSSを確実に再取得させるためキャッシュバージョンを更新 --><link rel="stylesheet" href="<?=asset_url('receipt_layout.css')?>&receipt_v=20260906-09"></head><body><?php include 'header.php'; ?>
<div class="container"><h1>領収書一覧</h1><?php if($msg): ?><div class="msg ok"><?=h($msg)?></div><?php endif; ?><?php if($err): ?><div class="msg err"><?=h($err)?></div><?php endif; ?>
<div class="card no-print search-card receipt-search-card"><form method="get"><input type="hidden" name="search" value="1"><div class="grid search-grid"><div><label>宛先</label><input name="customer_name" value="<?=h($customer_name)?>"></div><div><label>領収書番号</label><input name="receipt_no" value="<?=h($receipt_no)?>"></div><div><label>発行日 FROM</label><input class="small-date" type="date" name="date_from" value="<?=h($date_from)?>"></div><div><label>発行日 TO</label><input class="small-date" type="date" name="date_to" value="<?=h($date_to)?>"></div></div><div class="btns"><button class="btn primary">検索</button></div></form></div>
<div class="card no-print"><h2>一覧</h2><?php if(!$searched): ?><p>初期表示では検索しません。条件を入力して検索してください。</p><?php else: ?><div class="table-wrap"><table class="list receipt-list"><tr><th>領収書番号</th><th class="col-date">発行日</th><th>宛先</th><th>但し書き</th><th class="col-amount">合計金額</th><th class="col-action">操作</th></tr><?php foreach($rows as $r): ?><tr><td><?=h($r['receipt_no'])?></td><td><?=h($r['issue_date'])?></td><td><?=h($r['customer_name'])?></td><td><?=h(mb_strimwidth($r['description'] ?? '',0,28,'...','UTF-8'))?></td><td style="text-align:right;">¥<?=money($r['total_amount'])?></td><td><div class="action-cell"><a class="btn gray compact" href="?<?=h(http_build_query(array_merge($_GET,['view_id'=>$r['id']])))?>">詳細</a><a class="btn gray compact" href="receipt_create.php?id=<?=$r['id']?>">編集</a><a class="btn red compact" href="receipt_delete.php?id=<?=$r['id']?>" onclick="return confirm('この領収書を削除しますか？\nこの操作は元に戻せません。');">削除</a></div></td></tr><?php endforeach; ?></table></div><?php $pages=max(1,ceil($totalRows/$limit)); if($pages>1): ?><div class="pagination"><?php for($p=1;$p<=$pages;$p++): $q=array_merge($_GET,['page'=>$p]); ?><?= $p==$page ? '<span class="current">'.$p.'</span>' : '<a href="?'.h(http_build_query($q)).'">'.$p.'</a>' ?><?php endfor; ?></div><?php endif; ?><?php endif; ?></div>
<?php if($viewReceipt): ?><div class="card receipt-print-card print-card"><div class="btns no-print"><button class="btn primary" onclick="window.print()">PDF/印刷</button></div><?php render_receipt_sheet($viewReceipt); ?></div><?php endif; ?>
</div></body></html>
