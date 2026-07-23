<?php
require_once __DIR__ . '/auth.php';

function receipt_calc_tax_from_total($totalAmount, $taxRate) {
    $totalAmount = (int)$totalAmount;
    $rateInt = (int)round(((float)$taxRate) * 100); // 10.00 => 1000
    if ($rateInt <= 0) {
        return ['subtotal' => $totalAmount, 'tax_amount' => 0, 'total_amount' => $totalAmount];
    }
    // 浮動小数点誤差を避けるため整数計算する。
    // 税抜 = 税込 * 10000 / (10000 + 税率*100)
    $subtotal = intdiv($totalAmount * 10000, 10000 + $rateInt);
    $taxAmount = $totalAmount - $subtotal;
    return ['subtotal' => $subtotal, 'tax_amount' => $taxAmount, 'total_amount' => $totalAmount];
}

function generate_receipt_no($pdo, $issueDate = null) {
    // 領収書番号：00000001 ～ 99999999 の8桁連番
    // 発行日には依存しない。
    $stmt = $pdo->query("SELECT receipt_no FROM receipts WHERE receipt_no REGEXP '^[0-9]{8}$' ORDER BY receipt_no DESC LIMIT 1");
    $last = $stmt->fetchColumn();

    $nextNo = 1;
    if ($last !== false && $last !== null && preg_match('/^\d{8}$/', (string)$last)) {
        $nextNo = (int)$last + 1;
    }

    if ($nextNo > 99999999) {
        throw new Exception('領収書番号が上限に達しました。');
    }

    return str_pad((string)$nextNo, 8, '0', STR_PAD_LEFT);
}

function get_tax_rates($pdo) {
    return $pdo->query('SELECT id, tax_rate_name, tax_rate FROM tax_rates ORDER BY tax_rate DESC, id ASC')->fetchAll();
}

function default_tax_rate($taxRates) {
    foreach ($taxRates as $r) {
        if ((float)$r['tax_rate'] === 10.0) return (string)$r['tax_rate'];
    }
    return isset($taxRates[0]) ? (string)$taxRates[0]['tax_rate'] : '10.00';
}

function receipt_company_name() { return defined('COMPANY_NAME') ? COMPANY_NAME : '株式会社 IREEDUI'; }
function receipt_company_postal_code() { return defined('COMPANY_POSTAL_CODE') ? COMPANY_POSTAL_CODE : '355-0813'; }
function receipt_company_address() { return defined('COMPANY_ADDRESS') ? COMPANY_ADDRESS : '埼玉県比企郡滑川町月輪1597-8'; }
function receipt_company_tel() { return '0493-81-6707'; }
function receipt_company_fax() { return '0493-81-6708'; }
function receipt_company_registration_number() { return defined('COMPANY_REGISTRATION_NUMBER') ? COMPANY_REGISTRATION_NUMBER : 'T4030001095529'; }
?>
