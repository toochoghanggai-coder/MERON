<?php
require_once __DIR__ . '/receipt_functions.php'; require_login();
try {
    $id=(int)($_GET['id'] ?? 0);
    if ($id <= 0) throw new Exception('削除対象が不正です。');
    $st=db()->prepare('DELETE FROM receipts WHERE id=?');
    $st->execute([$id]);
    header('Location: receipt_list.php?msg=' . urlencode('削除しました。'));
    exit;
} catch(Exception $e) {
    header('Location: receipt_list.php?err=' . urlencode($e->getMessage()));
    exit;
}
?>
