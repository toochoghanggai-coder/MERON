invoice_system v18

■ファイル名変更
- invoices.php → invoice_list.php
- invoice_form.php → invoice_create.php
- customers.php → customer_list.php
- customer_form.php → customer_edit.php

■共通化
DB接続は business/common/db.php を利用します。
DB設定は business/common/config.php を利用します。
認証・共通関数は business/common/auth.php を利用します。

■削除
receipts.php は削除しました。
領収書機能は business/receipt_system/ で別途作成予定です。


[v19]
- 自社TEL/FAXは common/config.php の COMPANY_TEL / COMPANY_FAX を参照
- 請求先検索は customer_id の完全一致
- 日付・金額の空欄は検索条件に含めない
