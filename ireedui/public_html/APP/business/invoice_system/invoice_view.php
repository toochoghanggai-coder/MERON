<?php
function render_invoice_sheet($invoice, $items, $customer = null) {
    $doc = $invoice['document_type'] ?? '請求明細書';
    $date = $invoice['invoice_date'] ?? '';
    $customerName = $invoice['customer_name'] ?? ($customer['company_name'] ?? '');
    $postal = $invoice['customer_postal_code'] ?? ($customer['postal_code'] ?? '');
    $address = $invoice['customer_address'] ?? ($customer['address'] ?? '');
    $tel = $invoice['customer_tel'] ?? ($customer['tel'] ?? '');
    $fax = $invoice['customer_fax'] ?? ($customer['fax'] ?? '');
    $total = (int)($invoice['total_amount'] ?? 0);
    $subtotal = (int)($invoice['subtotal'] ?? 0);
    $tax = (int)($invoice['tax_amount'] ?? 0);
    $qty = 0;
    foreach (($items ?? []) as $calcItem) {
        if (($calcItem['type'] ?? '商品') === '商品') {
            $qty += (int)($calcItem['quantity'] ?? 0);
        }
    }
    $isEstimate = mb_strpos($doc, '見積') !== false;
    $lead = $isEstimate ? '下記の通りお見積り申し上げます。' : '下記の通りご請求申し上げます。';
    $amountLabel = $isEstimate ? '今回お見積金額' : '今回ご請求金額';
?>
<main class="invoice" id="invoiceSheet">
  <h1 class="title"><?=h($doc)?></h1>
  <hr class="top-line" />

  <section class="top-row">
    <div>
      <div class="customer-address">
        <?php if ($postal !== ''): ?>〒<?=h($postal)?><br /><?php endif; ?>
        <?=nl2br(h($address))?>
      </div>

      <div class="customer-name"><?=h($customerName)?>　御中</div>

      <div class="contact">
        <?php if ($tel !== ''): ?>TEL：<?=h($tel)?><br /><?php endif; ?>
        <?php if ($fax !== ''): ?>FAX：<?=h($fax)?><?php endif; ?>
      </div>
    </div>

    <div>
      <div class="date-text"><?=ymd_to_jp($date)?>　締切分</div>

      <div class="company-area">
        <div class="company-info">
          <div class="company-name"><?=h(COMPANY_NAME)?></div>
          〒<?=h(COMPANY_POSTAL_CODE)?><br />
          <?=h(COMPANY_ADDRESS)?><br />
          TEL：<?=h(COMPANY_TEL)?><br />
          FAX：<?=h(COMPANY_FAX)?><br />
          登録番号：<?=h(COMPANY_REGISTRATION_NUMBER)?>
        </div>

        <img class="stamp-img" src="img/stamp.png" alt="会社印" />
      </div>
    </div>
  </section>

  <p class="lead"><?=h($lead)?></p>

  <section class="amount-box-new">
    <div class="amount-label"><?=h($amountLabel)?></div>
    <div class="amount-value">¥<?=money($total)?></div>
  </section>

  <table class="detail-table">
    <thead>
      <tr>
        <th style="width: 11%;">日付</th>
        <th style="width: 31%;">商品名</th>
        <th style="width: 18%;">グレード</th>
        <th style="width: 12%;">数量(本)</th>
        <th style="width: 14%;">単価</th>
        <th style="width: 14%;">金額</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($items as $it):
        $hasValue = trim((string)($it['item_date'] ?? '')) !== ''
          || trim((string)($it['product_name'] ?? '')) !== ''
          || trim((string)($it['rank_name'] ?? '')) !== ''
          || (int)($it['quantity'] ?? 0) !== 0
          || (int)($it['unit_price'] ?? 0) !== 0
          || (int)($it['amount'] ?? 0) !== 0;
        if (!$hasValue) continue;
      ?>
      <tr>
        <td class="date"><?=h($it['item_date'] ?? '')?></td>
        <td><?=h($it['product_name'] ?? '')?></td>
        <td class="note"><?=h($it['rank_name'] ?? '')?></td>
        <td class="qty"><?=money((int)($it['quantity'] ?? 0))?></td>
        <td class="price"><?=money((int)($it['unit_price'] ?? 0))?></td>
        <td class="amount"><?=money((int)($it['amount'] ?? 0))?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="summary-wrap">
    <table class="total-count-box">
      <tbody>
        <tr>
          <th>合計本数</th>
          <td><?=money($qty)?>本</td>
        </tr>
      </tbody>
    </table>

    <table class="summary-table">
      <tbody>
        <tr>
          <th>小計</th>
          <td><?=money($subtotal)?></td>
        </tr>
        <tr>
          <th>消費税（10%）</th>
          <td><?=money($tax)?></td>
        </tr>
        <tr class="grand">
          <th>合計金額</th>
          <td><?=money($total)?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <section class="bank-box">
    <div>
      <div class="bank-title">お振込先</div>
      三菱UFJ銀行　蕨支店　普通0133764<br />
      口座名義：<?=h(COMPANY_NAME)?>
    </div>
    <div class="bank-note">※振込手数料は貴社にてご負担ください。</div>
  </section>

  <footer class="footer"><?=h(COMPANY_NAME)?></footer>
</main>
<?php } ?>
