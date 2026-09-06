<?php
require_once __DIR__ . '/auth.php'; require_login(); require_once __DIR__ . '/invoice_view.php';
$document_type = $_GET['document_type'] ?? '';
$customer_id = $_GET['customer_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$amount_from = $_GET['amount_from'] ?? '';
$amount_to = $_GET['amount_to'] ?? '';
$customers = db()->query('SELECT id, company_name FROM customers ORDER BY company_name ASC, id ASC')->fetchAll();
$searched = isset($_GET['search']);
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10; $offset = ($page - 1) * $limit;
$where=[]; $params=[];
if ($searched) {
    if ($document_type !== '') { $where[]='document_type = ?'; $params[]=$document_type; }
    if ($customer_id !== '') { $where[]='customer_id = ?'; $params[]=(int)$customer_id; }
    if ($date_from !== '') { $where[]='invoice_date >= ?'; $params[]=$date_from; }
    if ($date_to !== '') { $where[]='invoice_date <= ?'; $params[]=$date_to; }
    if ($amount_from !== '') { $where[]='total_amount >= ?'; $params[]=(int)$amount_from; }
    if ($amount_to !== '') { $where[]='total_amount <= ?'; $params[]=(int)$amount_to; }
}
$sqlWhere = $where ? ('WHERE '.implode(' AND ', $where)) : '';
$rows=[]; $totalRows=0;
if ($searched) {
    $stmt=db()->prepare("SELECT COUNT(*) c FROM invoices $sqlWhere"); $stmt->execute($params); $totalRows=(int)$stmt->fetch()['c'];
    $stmt=db()->prepare("SELECT * FROM invoices $sqlWhere ORDER BY invoice_date DESC, id DESC LIMIT $limit OFFSET $offset"); $stmt->execute($params); $rows=$stmt->fetchAll();
}
$viewInvoice=null; $viewItems=[];
if (!empty($_GET['view_id'])) {
    $stmt=db()->prepare('SELECT * FROM invoices WHERE id=?'); $stmt->execute([(int)$_GET['view_id']]); $viewInvoice=$stmt->fetch();
    if ($viewInvoice) { $st=db()->prepare('SELECT * FROM invoice_items WHERE invoice_id=? ORDER BY sort_order,id'); $st->execute([$viewInvoice['id']]); $viewItems=$st->fetchAll(); }
}
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>請求書一覧</title><!-- v20260906-04: 画面・印刷の配色調整版CSSを確実に再取得させるためキャッシュバージョンを更新 --><link rel="stylesheet" href="<?=asset_url('style.css')?>&invoice_v=20260906-04"></head><body><?php include 'header.php'; ?>
<div class="container"><h1>請求書一覧</h1>
<div class="card no-print search-card"><form method="get"><input type="hidden" name="search" value="1"><div class="grid search-grid"><div class="search-doc-type"><label>明細書種別</label><select name="document_type"><option value="" <?=$document_type===''?'selected':''?>>すべて</option><option value="請求明細書" <?=$document_type==='請求明細書'?'selected':''?>>請求明細書</option><option value="見積明細書" <?=$document_type==='見積明細書'?'selected':''?>>見積明細書</option></select></div><div class="search-customer"><label>請求先会社名</label><select name="customer_id"><option value="">すべて</option><?php foreach($customers as $customer): ?><option value="<?=$customer['id']?>" <?=((string)$customer_id === (string)$customer['id']) ? 'selected' : ''?>><?=h($customer['company_name'])?></option><?php endforeach; ?></select></div><div class="search-date-from"><label>請求日 FROM</label><input type="date" name="date_from" value="<?=h($date_from)?>"></div><div class="search-date-to"><label>請求日 TO</label><input type="date" name="date_to" value="<?=h($date_to)?>"></div><div class="search-amount-from"><label>合計金額 FROM</label><input type="number" name="amount_from" value="<?=h($amount_from)?>"></div><div class="search-amount-to"><label>合計金額 TO</label><input type="number" name="amount_to" value="<?=h($amount_to)?>"></div></div><div class="btns"><button class="btn primary">検索</button></div></form></div>
<div class="card no-print"><h2>一覧</h2><?php if(!$searched): ?><p>初期表示では検索しません。条件を入力して検索してください。</p><?php else: ?><div class="table-wrap"><table class="list invoice-list"><tr><th class="col-type">明細書種別</th><th class="col-date">請求日</th><th>請求先</th><th class="col-amount">合計金額</th><th class="col-action">操作</th></tr><?php foreach($rows as $r): ?><tr><td><?=h($r['document_type'])?></td><td><?=h($r['invoice_date'])?></td><td><?=h($r['customer_name'])?></td><td style="text-align:right;">¥<?=money($r['total_amount'])?></td><td><div class="action-cell"><a class="btn gray compact" href="?<?=h(http_build_query(array_merge($_GET,['view_id'=>$r['id']])))?>">表示</a><a class="btn gray compact" href="invoice_create.php?id=<?=$r['id']?>">編集</a></div></td></tr><?php endforeach; ?></table></div><?php $pages=max(1,ceil($totalRows/$limit)); if($pages>1): ?><div class="pagination"><?php for($p=1;$p<=$pages;$p++): $q=array_merge($_GET,['page'=>$p]); ?><?= $p==$page ? '<span class="current">'.$p.'</span>' : '<a href="?'.h(http_build_query($q)).'">'.$p.'</a>' ?><?php endfor; ?></div><?php endif; ?><?php endif; ?></div>
<?php if($viewInvoice): ?><div class="card print-card"><div class="btns no-print"><button class="btn primary" onclick="window.print()">PDF/印刷</button></div><?php render_invoice_sheet($viewInvoice,$viewItems); ?></div><?php endif; ?>
</div></body></html>
