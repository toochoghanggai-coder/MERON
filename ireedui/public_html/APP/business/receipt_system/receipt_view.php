<?php
function receipt_stamp_src() {
    $path = __DIR__ . '/assets/stamp.png';
    if (!is_file($path)) return '';
    return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
}

function receipt_tax_label($rate) {
    return rtrim(rtrim(number_format((float)$rate, 2), '0'), '.');
}

function receipt_issue_date_label($date) {
    if (!$date) return '';
    $ts = strtotime($date);
    return $ts ? date('Y年m月d日', $ts) : '';
}

function render_receipt_sheet($receipt) {
    $issueDate = receipt_issue_date_label($receipt['issue_date'] ?? '');
    $receiptNo = $receipt['receipt_no'] ?? '';
    $customerName = $receipt['customer_name'] ?? '';
    $description = trim((string)($receipt['description'] ?? ''));
    $total = (int)($receipt['total_amount'] ?? 0);
    $subtotal = (int)($receipt['subtotal'] ?? 0);
    $tax = (int)($receipt['tax_amount'] ?? 0);
    $taxRate = receipt_tax_label($receipt['tax_rate'] ?? 10);
    $stampSrc = receipt_stamp_src();
?>
<main class="receipt-sheet receipt-layout-classic" id="receiptSheet">
  <div class="classic-frame">
    <header class="receipt-document-header">
      <h1 class="classic-title">領収証</h1>
      <div class="classic-meta">
        <div class="classic-no">No. <span><?=h($receiptNo)?></span></div>
        <div class="classic-date"><?=h($issueDate)?></div>
      </div>
    </header>
    <div class="classic-customer"><span><?=h($customerName)?></span><b>様</b></div>
    <div class="classic-amount-row">
      <div class="classic-amount-label">金額</div>
      <div class="classic-amount-box"><span class="classic-yen">￥</span><span class="classic-amount-value"><?=money($total)?></span><span class="classic-currency">円</span></div>
    </div>
    <div class="classic-description"><span>但し</span><div><?=nl2br(h($description))?></div></div>
    <div class="classic-content-row">
      <section class="classic-breakdown-area">
        <div class="classic-received-text">上記の金額正に領収いたしました。</div>
        <div class="classic-section-title">内訳</div>
        <table class="classic-breakdown-table">
          <tr><th>税抜金額</th><td>￥<?=money($subtotal)?></td></tr>
          <tr><th>消費税額等（<?=h($taxRate)?>%）</th><td>￥<?=money($tax)?></td></tr>
        </table>
      </section>
      <section class="classic-company-area">
        <div class="classic-company-info">
          <div class="classic-company-name"><?=h(receipt_company_name())?></div>
          <div>〒<?=h(receipt_company_postal_code())?></div>
          <div><?=h(receipt_company_address())?></div>
          <div>TEL：<?=h(receipt_company_tel())?></div>
          <div>FAX：<?=h(receipt_company_fax())?></div>
          <div>登録番号：<?=h(receipt_company_registration_number())?></div>
        </div>
        <?php if ($stampSrc): ?><img class="classic-stamp" src="<?=h($stampSrc)?>" alt="会社印"><?php endif; ?>
      </section>
    </div>
  </div>
</main>
<?php } ?>
