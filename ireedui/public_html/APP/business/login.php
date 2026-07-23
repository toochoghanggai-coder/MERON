<?php
require_once __DIR__ . '/common/auth.php';
if (is_logged_in()) { header('Location: menu.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM users WHERE login_id = ? AND is_active = 1');
    $stmt->execute([$login_id]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['user_name'] ?: $user['login_id'];
        header('Location: menu.php'); exit;
    }
    $error = 'ログインIDまたはパスワードが違います。';
}
?>
<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ログイン</title><link rel="stylesheet" href="<?=asset_url('invoice_system/style.css')?>"></head><body>
<div class="login-box card"><h1>ログイン</h1><?php if($error): ?><div class="msg err"><?=h($error)?></div><?php endif; ?>
<form method="post"><div class="form-row"><label>ログインID</label><input name="login_id" autocomplete="username" required></div><div class="form-row"><label>パスワード</label><input type="password" name="password" autocomplete="current-password" required></div><button class="btn primary" type="submit">ログイン</button></form></div>
</body></html>
