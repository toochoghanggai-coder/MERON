function defaultItemDate(){
  const el=document.getElementById('invoice_date');
  const v=el ? el.value : '';
  if(!v) return '';
  const d=new Date(v+'T00:00:00');
  return (d.getMonth()+1)+'月'+d.getDate()+'日';
}
function typeOptions(selected){
  const values=['商品','その他'];
  return values.map(v=>`<option value="${v}" ${v===selected?'selected':''}>${v}</option>`).join('');
}
function gradeOptions(selected){
  const values=['','NEW','A','B','5mm','7mm','9mm','11mm','13mm','15mm'];
  return values.map(v=>`<option value="${v}" ${v===selected?'selected':''}>${v}</option>`).join('');
}
function addRow(){
  const t=document.getElementById('itemsTable');
  if(!t) return;
  const tr=t.insertRow(-1);
  tr.innerHTML=`<td><input name="item_date[]" value="${defaultItemDate()}"></td><td><select name="type[]">${typeOptions('商品')}</select></td><td><input name="product_name[]"></td><td><select name="rank_name[]">${gradeOptions('')}</select></td><td><input type="number" name="quantity[]" oninput="calcRow(this)"></td><td><input type="number" name="unit_price[]" oninput="calcRow(this)"></td><td><input type="number" name="amount_view[]" oninput="calcTotalsOnly()"></td><td><button type="button" class="btn red" onclick="this.closest('tr').remove()">削除</button></td>`;
}
function calcRow(el){
  const tr=el.closest('tr');
  const q=parseInt(tr.querySelector('[name="quantity[]"]').value||0,10);
  const p=parseInt(tr.querySelector('[name="unit_price[]"]').value||0,10);
  const amount=tr.querySelector('[name="amount_view[]"]');
  if(amount && q > 0 && p > 0) amount.value=q*p;
}
function calcTotalsOnly(){
  // 金額欄の手入力を許可するため、ここでは行金額の再計算は行わない。
  // 登録時の小計・消費税・合計はサーバー側で金額欄を元に再計算する。
}
function setupPreventEnterSubmit(){
  const form=document.getElementById('invoiceForm');
  if(!form) return;
  form.addEventListener('keydown', function(e){
    if(e.key === 'Enter' && e.target && e.target.tagName !== 'TEXTAREA'){
      e.preventDefault();
    }
  });
}
function toggleCustomerForm(){
  const body=document.getElementById('customerRegisterBody');
  if(body) body.classList.toggle('is-open');
}


function normalizeDigits(value){
  return String(value || '').replace(/[０-９]/g, ch => String.fromCharCode(ch.charCodeAt(0) - 0xFEE0)).replace(/\D/g, '');
}
function formatPostal(value){
  const d = normalizeDigits(value);
  if(d.length === 7) return d.slice(0,3) + '-' + d.slice(3);
  return d;
}
function formatPhone(value){
  const d = normalizeDigits(value);
  if(!d) return '';
  if(d.length === 11 && /^(050|070|080|090)/.test(d)) return d.slice(0,3) + '-' + d.slice(3,7) + '-' + d.slice(7);
  if(d.length === 10 && /^(0120|0570|0800)/.test(d)) return d.slice(0,4) + '-' + d.slice(4,7) + '-' + d.slice(7);
  if(d.length === 10 && /^(03|06)/.test(d)) return d.slice(0,2) + '-' + d.slice(2,6) + '-' + d.slice(6);
  if(d.length === 10) return d.slice(0,3) + '-' + d.slice(3,6) + '-' + d.slice(6);
  return d;
}
function setupCustomerAutoHyphen(){
  const postalInputs = document.querySelectorAll('input[name="postal_code"]');
  const phoneInputs = document.querySelectorAll('input[name="tel"], input[name="fax"]');
  postalInputs.forEach(input => {
    input.addEventListener('blur', () => { input.value = formatPostal(input.value); });
  });
  phoneInputs.forEach(input => {
    input.addEventListener('blur', () => { input.value = formatPhone(input.value); });
  });
}
document.addEventListener('DOMContentLoaded', function(){
  setupCustomerAutoHyphen();
  setupPreventEnterSubmit();
});
