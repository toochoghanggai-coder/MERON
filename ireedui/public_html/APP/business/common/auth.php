<?php
session_start();
require_once __DIR__ . '/db.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

function asset_url($path) {
    return $path . '?v=' . rawurlencode(APP_VERSION);
}

function business_url($path = '') {
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $baseNames = ['invoice_system', 'receipt_system', 'material_system'];
    if (in_array(basename($dir), $baseNames, true)) {
        $dir = dirname($dir);
    }
    return rtrim($dir, '/') . '/' . ltrim($path, '/');
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . business_url('login.php'));
        exit;
    }
}

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function money($v) {
    return number_format((int)$v);
}

function ymd_to_jp($date) {
    if (!$date) return '';
    $t = strtotime($date);
    if (!$t) return h($date);
    return date('Y年n月j日', $t);
}

function md_to_jp($date) {
    if (!$date) return '';
    $t = strtotime($date);
    if (!$t) return h($date);
    return date('n月j日', $t);
}


function normalize_digits($v) {
    $v = strtr((string)$v, '０１２３４５６７８９', '0123456789');
    return preg_replace('/\D+/', '', $v);
}
function format_postal_auto($v) {
    $digits = normalize_digits($v);
    if (strlen($digits) === 7) {
        return substr($digits, 0, 3) . '-' . substr($digits, 3);
    }
    return $digits;
}
function format_phone_auto($v) {
    $digits = normalize_digits($v);
    $len = strlen($digits);
    if ($len === 0) return '';
    if ($len === 11 && preg_match('/^(050|070|080|090)/', $digits)) {
        return substr($digits, 0, 3) . '-' . substr($digits, 3, 4) . '-' . substr($digits, 7);
    }
    if ($len === 10 && preg_match('/^(0120|0570|0800)/', $digits)) {
        return substr($digits, 0, 4) . '-' . substr($digits, 4, 3) . '-' . substr($digits, 7);
    }
    if ($len === 10 && preg_match('/^(03|06)/', $digits)) {
        return substr($digits, 0, 2) . '-' . substr($digits, 2, 4) . '-' . substr($digits, 6);
    }
    if ($len === 10) {
        return substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6);
    }
    return $digits;
}
function normalize_customer_input($data) {
    $data['postal_code'] = format_postal_auto($data['postal_code'] ?? '');
    $data['tel'] = format_phone_auto($data['tel'] ?? '');
    $data['fax'] = format_phone_auto($data['fax'] ?? '');
    return $data;
}

function is_full_width_text($v) {
    if ($v === '') return true;
    return !preg_match('/[A-Za-z0-9]/u', $v);
}
function is_postal_code($v) {
    if ($v === '') return true;
    return preg_match('/^\\d{3}-?\\d{4}$/', $v) === 1;
}
function is_half_number_hyphen($v) {
    if ($v === '') return true;
    return preg_match('/^[0-9\\-]+$/', $v) === 1;
}
function is_half_email($v) {
    if ($v === '') return true;
    return preg_match('/^[A-Za-z0-9._%+\\-@]+$/', $v) === 1 && filter_var($v, FILTER_VALIDATE_EMAIL);
}
function validate_customer_input($data) {
    $errors = [];
    $company = trim($data['company_name'] ?? '');
    $postal = trim($data['postal_code'] ?? '');
    $address = trim($data['address'] ?? '');
    $tel = trim($data['tel'] ?? '');
    $fax = trim($data['fax'] ?? '');
    $email = trim($data['email'] ?? '');
    if ($company === '') $errors[] = '会社名は必須です。';
    if ($postal !== '' && !is_postal_code($postal)) $errors[] = '郵便番号は 123-4567 の形式で入力してください。';
    if ($tel !== '' && !is_half_number_hyphen($tel)) $errors[] = '電話番号は半角数字とハイフンで入力してください。';
    if ($fax !== '' && !is_half_number_hyphen($fax)) $errors[] = 'FAXは半角数字とハイフンで入力してください。';
    if ($email !== '' && !is_half_email($email)) $errors[] = 'メールは半角英数字のメール形式で入力してください。';
    return $errors;
}
?>
