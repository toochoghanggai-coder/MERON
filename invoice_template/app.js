const tbody = document.querySelector('#editTable tbody');
const sampleRows = [
  {date:'3月11日', name:'275/70R22.5', type:'A', qty:63, unit:'本', price:1500},
  {date:'', name:'275/70R22.5', type:'B', qty:15, unit:'本', price:500},
  {date:'', name:'275/70R22.5', type:'R1', qty:57, unit:'本', price:400},
  {date:'', name:'275/70R22.5(R1)', type:'5mm', qty:25, unit:'本', price:1000},
];
function addRow(data={date:'',name:'',type:'',qty:'',unit:'本',price:''}){
  const tr=document.createElement('tr');
  tr.innerHTML=['date','name','type','qty','unit','price'].map(k=>`<td><input data-k="${k}" value="${data[k]??''}"></td>`).join('')+
    '<td><button type="button" onclick="this.closest(\'tr\').remove();renderInvoice()">削除</button></td>';
  tbody.appendChild(tr);
}
function yen(n){return Math.round(n).toLocaleString('ja-JP')}
function dateJP(v){if(!v)return ''; const d=new Date(v+'T00:00:00'); return `${d.getFullYear()}年${d.getMonth()+1}月${d.getDate()}日`}
function renderInvoice(){
  document.getElementById('outDate').textContent=dateJP(invoiceDate.value);
  outClosing.textContent=closing.value; outZip.textContent=customerZip.value; outAddress.textContent=customerAddress.value; outName.textContent=customerName.value;
  const rows=[...tbody.querySelectorAll('tr')].map(tr=>Object.fromEntries([...tr.querySelectorAll('input')].map(i=>[i.dataset.k,i.value])));
  const body=document.getElementById('invoiceRows'); body.innerHTML=''; let sub=0, qtyTotal=0;
  rows.forEach(r=>{ const qty=Number(r.qty)||0, price=Number(r.price)||0, amount=qty*price; sub+=amount; qtyTotal+=qty; body.insertAdjacentHTML('beforeend',`<tr><td>${r.date}</td><td>${r.name}</td><td>${r.type}</td><td>${qty||''}</td><td>${r.unit}</td><td>${price?yen(price):''}</td><td>${amount?yen(amount):'0'}</td></tr>`); });
  for(let i=rows.length;i<22;i++) body.insertAdjacentHTML('beforeend','<tr><td></td><td colspan="2"></td><td></td><td></td><td></td><td>0</td></tr>');
  const tax=Math.round(sub*0.1), grand=sub+tax;
  subtotal.textContent=yen(sub); taxEl=document.getElementById('tax'); taxEl.textContent=yen(tax); grandTotal.textContent=yen(grand); grandTotalTop.textContent='¥'+yen(grand); totalQty.textContent=yen(qtyTotal);
}
sampleRows.forEach(addRow); renderInvoice();
