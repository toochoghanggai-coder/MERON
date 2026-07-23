<?php
require_once __DIR__ . '/receipt_functions.php'; require_login();
$pdo = db();
$err = '';
$taxRates = get_tax_rates($pdo);
$id = (int)($_GET['id'] ?? 0);
$receipt = null;
if ($id) {
    $st = $pdo->prepare('SELECT * FROM receipts WHERE id=?');
    $st->execute([$id]);
    $receipt = $st->fetch();
    if (!$receipt) { echo 'データがありません'; exit; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $customer_name = trim($_POST['customer_name'] ?? '');
        $issue_date = $_POST['issue_date'] ?? date('Y-m-d');
        $receipt_no = trim($_POST['receipt_no'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $total_amount = (int)str_replace(',', '', (string)($_POST['total_amount'] ?? 0));
        $tax_rate = (float)($_POST['tax_rate'] ?? 10);
        $note = trim($_POST['note'] ?? '');
        if ($customer_name === '') throw new Exception('宛先を入力してください。');
        if ($issue_date === '') throw new Exception('発行日を入力してください。');
        if ($receipt_no === '') $receipt_no = generate_receipt_no($pdo);
        if ($total_amount <= 0) throw new Exception('合計金額を入力してください。');
        $calc = receipt_calc_tax_from_total($total_amount, $tax_rate);
        if ($id) {
            $st = $pdo->prepare('SELECT COUNT(*) c FROM receipts WHERE receipt_no=? AND id<>?');
            $st->execute([$receipt_no, $id]);
        } else {
            $st = $pdo->prepare('SELECT COUNT(*) c FROM receipts WHERE receipt_no=?');
            $st->execute([$receipt_no]);
        }
        if ((int)$st->fetch()['c'] > 0) throw new Exception('同じ領収書番号がすでに存在します。');
        if ($id) {
            $st = $pdo->prepare('UPDATE receipts SET receipt_no=?, customer_name=?, issue_date=?, description=?, subtotal=?, tax_rate=?, tax_amount=?, total_amount=?, note=?, updated_by=? WHERE id=?');
            $st->execute([$receipt_no,$customer_name,$issue_date,$description,$calc['subtotal'],$tax_rate,$calc['tax_amount'],$total_amount,$note,$_SESSION['user_name'] ?? 'admin',$id]);
        } else {
            $st = $pdo->prepare('INSERT INTO receipts(receipt_no,customer_name,issue_date,description,subtotal,tax_rate,tax_amount,total_amount,note,created_by,updated_by) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
            $st->execute([$receipt_no,$customer_name,$issue_date,$description,$calc['subtotal'],$tax_rate,$calc['tax_amount'],$total_amount,$note,$_SESSION['user_name'] ?? 'admin',null]);
            $id = (int)$pdo->lastInsertId();
        }
        header('Location: receipt_list.php?search=1&receipt_no=' . urlencode($receipt_no) . '&view_id=' . $id);
        exit;
    } catch (Exception $e) { $err = $e->getMessage(); }
}
$defaultRate = default_tax_rate($taxRates);
$receipt = $receipt ?: ['id'=>'','receipt_no'=>generate_receipt_no($pdo), 'customer_name'=>'', 'issue_date'=>date('Y-m-d'), 'description'=>'', 'total_amount'=>'', 'tax_rate'=>$defaultRate, 'subtotal'=>'', 'tax_amount'=>'', 'note'=>''];
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>領収書作成</title><link rel="stylesheet" href="<?=asset_url('style.css')?>&receipt_v=20260723-07"><!-- v20260723-07: 領収書画面のCSSキャッシュバージョンを一覧画面と統一 --><link rel="stylesheet" href="<?=asset_url('receipt_layout.css')?>&receipt_v=20260723-07"></head><body><?php include 'header.php'; ?>
<div class="container"><h1><?= $id ? '領収書編集' : '領収書作成' ?></h1><?php if ($err): ?><div class="msg err"><?=h($err)?></div><?php endif; ?>
<form method="post" id="receiptForm" class="card no-print form-compact receipt-form-card"><input type="hidden" name="id" value="<?=h($receipt['id'] ?? '')?>"><div class="grid">
<div><label>宛先</label><input name="customer_name" value="<?=h($receipt['customer_name'])?>" required></div>
<div><label>発行日</label><input class="small-date" type="date" name="issue_date" id="issue_date" value="<?=h($receipt['issue_date'])?>" required></div>
<div><label>領収書番号</label><input name="receipt_no" id="receipt_no" value="<?=h($receipt['receipt_no'])?>" required></div>
<div><label>合計金額</label><input type="number" name="total_amount" id="total_amount" value="<?=h($receipt['total_amount'])?>" required></div>
<div class="wide"><label>但し書き</label><textarea name="description"><?=h($receipt['description'])?></textarea></div>
<div><label>税率</label><select name="tax_rate" id="tax_rate"><?php foreach($taxRates as $r): ?><option value="<?=h($r['tax_rate'])?>" <?=((float)$receipt['tax_rate'] === (float)$r['tax_rate']) ? 'selected' : ''?>><?=h($r['tax_rate_name'])?></option><?php endforeach; ?></select></div>
<div><label>税抜金額</label><input class="readonly" type="number" id="subtotal" value="<?=h($receipt['subtotal'])?>" readonly></div>
<div><label>消費税額</label><input class="readonly" type="number" id="tax_amount" value="<?=h($receipt['tax_amount'])?>" readonly></div>
<div class="wide"><label>備考</label><textarea name="note"><?=h($receipt['note'])?></textarea></div>
</div><div class="btns"><button class="btn green" type="submit">登録</button></div></form>
<div class="card receipt-print-card"><p class="mobile-note no-print">登録後、領収書一覧の詳細から正式レイアウトを確認できます。</p></div></div>
<script src="<?=asset_url('receipt_form.js')?>&receipt_v=20260719-02"></script></body></html>
