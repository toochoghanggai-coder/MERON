<?php
// 共通設定。DB接続情報やシステム共通値をここに集約します。
define('DB_HOST', 'localhost');
define('DB_NAME', 'xs007464_iredui');
define('DB_USER', 'xs007464_admin');
define('DB_PASS', 'Ireedui20160515');
define('DB_CHARSET', 'utf8mb4');

// システム共通バージョン。
// この値を変更すると、Edge/Chromeに残った古いCSS/JSキャッシュを一括で回避できます。
define('SYSTEM_VERSION', '19.0.0');
// 既存ソースとの互換性維持用。asset_url() は引き続き APP_VERSION を参照します。
define('APP_VERSION', SYSTEM_VERSION);

// 自社情報。帳票などで使用する値はここだけを修正します。
define('COMPANY_NAME', '株式会社 IREEDUI');
define('COMPANY_POSTAL_CODE', '369-0105');
define('COMPANY_ADDRESS', '埼玉県熊谷市箕輪373-3');
define('COMPANY_TEL', '0493-81-6707');
define('COMPANY_FAX', '0493-81-6708');
define('COMPANY_REGISTRATION_NUMBER', 'T4030001095529');
?>
