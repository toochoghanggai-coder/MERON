<?php
require_once __DIR__ . '/auth.php'; require_login();
$pdo=db(); $msg=''; $err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try{
        $_POST = normalize_customer_input($_POST);
        $errors = validate_customer_input($_POST);
        if ($errors) { throw new Exception(implode('<br>', $errors)); }
        $st=$pdo->prepare('INSERT INTO customers(company_name,postal_code,address,tel,fax,email,contact_name) VALUES(?,?,?,?,?,?,?)');
        $st->execute([trim($_POST['company_name']),trim($_POST['postal_code']??''),trim($_POST['address']??''),trim($_POST['tel']??''),trim($_POST['fax']??''),trim($_POST['email']??''),trim($_POST['contact_name']??'')]);
        $msg='登録しました。';
    }catch(Exception $e){$err=$e->getMessage();}
}
$page=max(1,(int)($_GET['page']??1)); $limit=10; $offset=($page-1)*$limit;
$total=(int)$pdo->query('SELECT COUNT(*) c FROM customers')->fetch()['c'];
$rows=$pdo->query("SELECT * FROM customers ORDER BY company_name ASC, id ASC LIMIT $limit OFFSET $offset")->fetchAll();
$showForm = $err ? true : false;
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>請求先一覧</title><link rel="stylesheet" href="<?=asset_url('style.css')?>"></head><body><?php include 'header.php'; ?><div class="container"><h1>請求先一覧</h1><?php if($msg): ?><div class="msg ok"><?=h($msg)?></div><?php endif; ?><?php if($err): ?><div class="msg err"><?=h($err)?></div><?php endif; ?>
<div class="card no-print customer-register-card"><button type="button" class="section-toggle" onclick="toggleCustomerForm()">登録エリア</button><div id="customerRegisterBody" class="customer-register-body <?= $showForm ? 'is-open' : '' ?>"><form method="post"><div class="grid"><div><label>会社名</label><input name="company_name" value="<?=h($_POST['company_name']??'')?>" required></div><div><label>郵便番号</label><input name="postal_code" value="<?=h($_POST['postal_code']??'')?>" inputmode="numeric" pattern="[0-9]{3}-?[0-9]{4}" placeholder="123-4567" title="123-4567 の形式で入力してください"></div><div><label>住所</label><input name="address" value="<?=h($_POST['address']??'')?>"></div><div><label>電話番号</label><input name="tel" value="<?=h($_POST['tel']??'')?>" inputmode="numeric" pattern="[0-9\-]+" title="半角数字とハイフンで入力してください"></div><div><label>FAX</label><input name="fax" value="<?=h($_POST['fax']??'')?>" inputmode="numeric" pattern="[0-9\-]+" title="半角数字とハイフンで入力してください"></div><div><label>メール</label><input name="email" value="<?=h($_POST['email']??'')?>" type="email" inputmode="email" pattern="[A-Za-z0-9._%+\-@]+" title="半角英数字のメール形式で入力してください"></div><div><label>担当者</label><input name="contact_name" value="<?=h($_POST['contact_name']??'')?>"></div></div><div class="btns"><button class="btn green">登録</button></div></form></div></div>
<div class="card"><h2>一覧</h2><div class="table-wrap"><table class="list customers-list"><tr><th>会社名</th><th>住所</th><th>電話番号</th><th>編集</th></tr><?php foreach($rows as $r): ?><tr><td><?=h($r['company_name'])?></td><td><?=h($r['address'])?></td><td><?=h($r['tel'])?></td><td><a class="btn gray compact" href="customer_edit.php?id=<?=$r['id']?>">編集</a></td></tr><?php endforeach; ?></table></div><?php $pages=max(1,ceil($total/$limit)); if($pages>1): ?><div class="pagination"><?php for($p=1;$p<=$pages;$p++): ?><?=$p==$page?'<span class="current">'.$p.'</span>':'<a href="?page='.$p.'">'.$p.'</a>'?><?php endfor; ?></div><?php endif; ?></div>
</div><script src="<?=asset_url('app.js')?>"></script></body></html>
