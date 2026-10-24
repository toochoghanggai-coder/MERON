<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/invoice_view.php';

$pdo = db();
$msg = '';
$err = '';
$gradeOptions = ['NEW','A','B','5mm','7mm','9mm','11mm','13mm','15mm'];
$typeOptions = ['商品','その他'];
$customers = $pdo->query('SELECT * FROM customers ORDER BY company_name')->fetchAll();

$id = (int)($_GET['id'] ?? 0);
$invoice = null;
$items = [];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM invoices WHERE id=?');
    $st->execute([$id]);
    $invoice = $st->fetch();
    $st = $pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id=? ORDER BY sort_order,id');
    $st->execute([$id]);
    $items = $st->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $customer_id = (int)($_POST['customer_id'] ?? 0);
        $st = $pdo->prepare('SELECT * FROM customers WHERE id=?');
        $st->execute([$customer_id]);
        $customer = $st->fetch();
        if (!$customer) throw new Exception('請求先を選択してください。');

        $document_type = $_POST['document_type'] ?? '請求明細書';
        $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
        $dates = $_POST['item_date'] ?? [];
        $types = $_POST['type'] ?? [];
        $names = $_POST['product_name'] ?? [];
        $ranks = $_POST['rank_name'] ?? [];
        $qtys = $_POST['quantity'] ?? [];
        $prices = $_POST['unit_price'] ?? [];
        $amounts = $_POST['amount_view'] ?? [];

        $saveItems = [];
        $subtotal = 0;
        $totalQty = 0;
        $rowCount = max(count($dates), count($types), count($names), count($ranks), count($qtys), count($prices), count($amounts));

        for ($i = 0; $i < $rowCount; $i++) {
            $type = $types[$i] ?? '商品';
            if (!in_array($type, $typeOptions, true)) $type = '商品';

            $productName = trim((string)($names[$i] ?? ''));
            $rankName = trim((string)($ranks[$i] ?? ''));
            // v20261022-05: 候補外のグレードも手入力値として保存する。

            $q = (int)($qtys[$i] ?? 0);
            $price = (int)($prices[$i] ?? 0);
            $manualAmount = (int)str_replace(',', '', (string)($amounts[$i] ?? 0));
            $amount = ($q > 0 && $price > 0) ? ($q * $price) : $manualAmount;

            if (trim((string)($dates[$i] ?? '')) === '' && $type === '商品' && $productName === '' && $rankName === '' && $q === 0 && $price === 0 && $amount === 0) continue;

            $subtotal += $amount;
            if ($type === '商品') $totalQty += $q;
            $saveItems[] = [
                'item_date' => $dates[$i] ?? '',
                'type' => $type,
                'product_name' => $productName,
                'rank_name' => $rankName,
                'quantity' => $q,
                'unit_price' => $price,
                'amount' => $amount,
                'sort_order' => $i + 1,
            ];
        }

        $tax = (int)floor($subtotal * 0.10);
        $total = $subtotal + $tax;
        $pdo->beginTransaction();

        if ($id) {
            $st = $pdo->prepare('UPDATE invoices SET document_type=?, invoice_date=?, customer_id=?, customer_name=?, customer_postal_code=?, customer_address=?, customer_tel=?, customer_fax=?, subtotal=?, tax_amount=?, total_amount=?, total_quantity=? WHERE id=?');
            $st->execute([$document_type, $invoice_date, $customer_id, $customer['company_name'], $customer['postal_code'], $customer['address'], $customer['tel'], $customer['fax'], $subtotal, $tax, $total, $totalQty, $id]);
            $pdo->prepare('DELETE FROM invoice_items WHERE invoice_id=?')->execute([$id]);
        } else {
            $st = $pdo->prepare('INSERT INTO invoices(document_type,invoice_date,customer_id,customer_name,customer_postal_code,customer_address,customer_tel,customer_fax,subtotal,tax_amount,total_amount,total_quantity,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $st->execute([$document_type, $invoice_date, $customer_id, $customer['company_name'], $customer['postal_code'], $customer['address'], $customer['tel'], $customer['fax'], $subtotal, $tax, $total, $totalQty, 'draft']);
            $id = (int)$pdo->lastInsertId();
        }

        $st = $pdo->prepare('INSERT INTO invoice_items(invoice_id,item_date,`type`,product_name,rank_name,quantity,unit_price,amount,sort_order) VALUES(?,?,?,?,?,?,?,?,?)');
        foreach ($saveItems as $it) {
            $st->execute([$id, $it['item_date'], $it['type'], $it['product_name'], $it['rank_name'], $it['quantity'], $it['unit_price'], $it['amount'], $it['sort_order']]);
        }
        $pdo->commit();
        header('Location: invoice_list.php?search=1&view_id=' . $id);
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $err = $e->getMessage();
    }
}

$invoice = $invoice ?: ['id'=>'', 'document_type'=>'請求明細書', 'invoice_date'=>date('Y-m-d'), 'customer_id'=>''];
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>請求書作成</title>
  <!-- v20261022-05: グレード自由入力対応版のキャッシュバージョンへ更新 -->
  <link rel="stylesheet" href="<?=asset_url('style.css')?>&invoice_v=20261022-05">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
  <h1>請求書作成</h1>
  <?php if ($err): ?><div class="msg err"><?=h($err)?></div><?php endif; ?>
  <form method="post" id="invoiceForm" class="card no-print form-compact">
    <input type="hidden" name="id" value="<?=h($invoice['id'] ?? '')?>">
    <div class="grid">
      <div>
        <label>明細書種別</label>
        <select name="document_type" id="document_type">
          <option <?=($invoice['document_type'] ?? '') === '請求明細書' ? 'selected' : ''?>>請求明細書</option>
          <option <?=($invoice['document_type'] ?? '') === '見積明細書' ? 'selected' : ''?>>見積明細書</option>
        </select>
      </div>
      <div>
        <label>請求日</label>
        <input class="small-date" type="date" name="invoice_date" id="invoice_date" value="<?=h($invoice['invoice_date'])?>" required>
      </div>
      <div>
        <label>請求先会社名</label>
        <select name="customer_id" id="customer_id" required>
          <option value="">選択してください</option>
          <?php foreach ($customers as $c): ?>
            <option value="<?=$c['id']?>" <?=((int)($invoice['customer_id'] ?? 0) === (int)$c['id']) ? 'selected' : ''?>><?=h($c['company_name'])?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <h2 class="detail-heading">明細</h2>
    <!-- v20261022-05: 既存候補を残しながらグレードを自由入力できるようにする。 -->
    <datalist id="gradeOptions">
      <?php foreach ($gradeOptions as $grade): ?>
        <option value="<?=h($grade)?>"></option>
      <?php endforeach; ?>
    </datalist>
    <div class="table-wrap">
      <table class="list" id="itemsTable">
        <tr>
          <th>日付</th>
          <th>種別</th>
          <th>商品名</th>
          <th>グレード</th>
          <th>数量(本)</th>
          <th>単価</th>
          <th>金額</th>
          <th></th>
        </tr>
        <?php $init = $items ?: array_fill(0, 1, []); foreach ($init as $it):
          $rowType = $it['type'] ?? '商品';
          if (!in_array($rowType, $typeOptions, true)) $rowType = 'その他';
          $rowGrade = $it['rank_name'] ?? '';
        ?>
        <tr>
          <td><input name="item_date[]" value="<?=h($it['item_date'] ?? md_to_jp($invoice['invoice_date']))?>"></td>
          <td>
            <select name="type[]">
              <option value="商品" <?=$rowType === '商品' ? 'selected' : ''?>>商品</option>
              <option value="その他" <?=$rowType === 'その他' ? 'selected' : ''?>>その他</option>
            </select>
          </td>
          <td><input name="product_name[]" value="<?=h($it['product_name'] ?? '')?>"></td>
          <td><input name="rank_name[]" list="gradeOptions" value="<?=h($rowGrade)?>" autocomplete="off"></td>
          <td><input type="number" name="quantity[]" value="<?=h($it['quantity'] ?? '')?>" oninput="calcRow(this)"></td>
          <td><input type="number" name="unit_price[]" value="<?=h($it['unit_price'] ?? '')?>" oninput="calcRow(this)"></td>
          <td><input type="number" name="amount_view[]" value="<?=h($it['amount'] ?? '')?>" oninput="calcTotalsOnly()"></td>
          <td><button type="button" class="btn red" onclick="this.closest('tr').remove()">削除</button></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <div class="btns items-actions">
      <button class="btn gray" type="button" onclick="addRow()">明細追加</button>
      <button class="btn green" type="submit">登録</button>
    </div>
  </form>
  <div class="card print-card"><p class="mobile-note no-print">登録後、請求書一覧の表示から正式レイアウトを確認できます。</p></div>
</div>
<script src="<?=asset_url('app.js')?>&invoice_v=20261022-05"></script>
</body>
</html>
