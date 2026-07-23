請求書管理システム v18

■主な変更点
- 領収書機能追加前の既存機能整理版です。
- DB接続情報を common/config.php に集約しました。
- DB接続処理を common/db.php に共通化しました。
- 認証・共通関数を common/auth.php に共通化しました。
- receipts.php は削除しました。
- receipt_system / material_system フォルダを追加しました。
- 請求書機能のファイル名を分かりやすい名称に整理しました。

■配置イメージ
APP/business/
├─ login.php
├─ logout.php
├─ menu.php
├─ common/
│  ├─ config.php
│  ├─ db.php
│  └─ auth.php
├─ invoice_system/
│  ├─ invoice_list.php
│  ├─ invoice_create.php
│  ├─ invoice_view.php
│  ├─ customer_list.php
│  ├─ customer_edit.php
│  ├─ header.php
│  ├─ style.css
│  └─ app.js
├─ receipt_system/
└─ material_system/

■URL
ログイン:
https://ireedui.jp/APP/business/login.php

請求書一覧:
https://ireedui.jp/APP/business/invoice_system/invoice_list.php

請求書作成:
https://ireedui.jp/APP/business/invoice_system/invoice_create.php

請求先一覧:
https://ireedui.jp/APP/business/invoice_system/customer_list.php

■注意
- 既存のDBテーブル構成は変更していません。
- 請求書の登録・検索・表示・印刷処理の仕様は変更していません。
- 領収書本体はこのZIPでは作成していません。メニューリンクのみ receipt_system/receipt_create.php にしています。


[v19]
- 自社情報を common/config.php に共通化
- SYSTEM_VERSION を common/config.php に共通化
- 請求書一覧の請求先検索をドロップダウン化
- 請求日・合計金額の初期値を空欄化
