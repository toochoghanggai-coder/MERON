function receiptCalcTax(){
  const totalEl=document.getElementById('total_amount');
  const rateEl=document.getElementById('tax_rate');
  const subtotalEl=document.getElementById('subtotal');
  const taxEl=document.getElementById('tax_amount');
  if(!totalEl || !rateEl || !subtotalEl || !taxEl) return;
  const total=parseInt(totalEl.value||0,10);
  const rate=Math.round(parseFloat(rateEl.value||0)*100);
  if(!total || total < 0){ subtotalEl.value=''; taxEl.value=''; return; }
  if(rate <= 0){ subtotalEl.value=total; taxEl.value=0; return; }
  const subtotal=Math.floor((total*10000)/(10000+rate));
  subtotalEl.value=subtotal;
  taxEl.value=total-subtotal;
}
function setupReceiptPreventEnterSubmit(){
  const form=document.getElementById('receiptForm');
  if(!form) return;
  form.addEventListener('keydown', function(e){
    if(e.key === 'Enter' && e.target && e.target.tagName !== 'TEXTAREA') e.preventDefault();
  });
}
async function setupReceiptNoRefresh(){
  const issueDate=document.getElementById('issue_date');
  const receiptNo=document.getElementById('receipt_no');
  const idEl=document.querySelector('input[name="id"]');
  if(!issueDate || !receiptNo || (idEl && idEl.value)) return;
  issueDate.addEventListener('change', async function(){
    if(!issueDate.value) return;
    try{
      const res=await fetch('receipt_no_api.php?issue_date='+encodeURIComponent(issueDate.value), {cache:'no-store'});
      const data=await res.json();
      if(data.receipt_no) receiptNo.value=data.receipt_no;
    }catch(e){console.error(e);}
  });
}
document.addEventListener('DOMContentLoaded', function(){
  setupReceiptPreventEnterSubmit();
  setupReceiptNoRefresh();
  const totalEl=document.getElementById('total_amount');
  const rateEl=document.getElementById('tax_rate');
  if(totalEl) totalEl.addEventListener('input', receiptCalcTax);
  if(rateEl) rateEl.addEventListener('change', receiptCalcTax);
  receiptCalcTax();
});
