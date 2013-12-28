<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: Tadashi Jokagi <elf2000@users.sourceforge.net>"
 * "Language-Team: JA"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_JA_UPGRADE_INTRODUCTION', "導入");
define('_JA_UPGRADE_AUTHENTICATION', "認証");
define('_JA_UPGRADE_REQUIREMENTS', "要求");
define('_JA_UPGRADE_DATABASE', "データベース");
define('_JA_UPGRADE_REPORT', "報告");
define('_JA_UPGRADE_VER_TO_VER', "{0} から {1}");
define('_JA_UPGRADE_SETTINGS', "設定");
define('_JA_UPGRADE_WRITECONFIG', "設定を保存する");
define('_JA_UPGRADE_FINISHED', "完了");
define('_JA_UPGRADE_INTRO_WELCOME', "Jaws アップグレーダーへようこそ。");
define('_JA_UPGRADE_INTRO_UPGRADER', "Using the upgrader you can upgrade and old installation to the current one. Just please make sure you have the following things available");
define('_JA_UPGRADE_INTRO_DATABASE', "データベースの詳細 - ホスト名、ユーザー名、パスワード、データベース名。");
define('_JA_UPGRADE_INTRO_FTP', "ファイルをアップロードする方法(おそらく FTP)。");
define('_JA_UPGRADE_INTRO_LOG', "ファイル ({0}) へアップグレード処理(とエラー)を記録する");
define('_JA_UPGRADE_INTRO_LOG_ERROR', "Note: If you want to log the upgrade process (and errors) of the installation to a file you first need to set write-access permissions to the ({0}) directory and then refresh this page with your browser");
define('_JA_UPGRADE_AUTH_PATH_INFO', "To make sure that you are really the owner of this site, please create a file called <strong>{0}</strong> in your Jaws upgrade directory (<strong>{1}</strong>).");
define('_JA_UPGRADE_AUTH_UPLOAD', "あなたの Jaws をアップロードした方法と同様にファイルをアップロードすることができます。");
define('_JA_UPGRADE_AUTH_KEY_INFO', "The file should contain the code shown in the box below, and nothing else.");
define('_JA_UPGRADE_AUTH_ENABLE_SECURITY', "セキュアなアップグレードを有効にする (Powered by RSA)");
define('_JA_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "RSA キー生成のエラーです。再度試してください。");
define('_JA_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "RSA キー生成のエラーです。いくつかの数学関数機能拡張が利用できません。");
define('_JA_UPGRADE_AUTH_ERROR_KEY_FILE', "鍵ファイル ({0}) が見つかりません。それを作成し、ウェブサーバーが読み込めるようにしてください。");
define('_JA_UPGRADE_AUTH_ERROR_KEY_MATCH', "鍵ファイル ({0}) が下記と一致しません。入力された鍵が正しい鍵か確認してください。");
define('_JA_UPGRADE_REQ_REQUIREMENT', "要求");
define('_JA_UPGRADE_REQ_OPTIONAL', "推奨オプション");
define('_JA_UPGRADE_REQ_RECOMMENDED', "推奨");
define('_JA_UPGRADE_REQ_DIRECTIVE', "ディレクティブ");
define('_JA_UPGRADE_REQ_ACTUAL', "実際");
define('_JA_UPGRADE_REQ_RESULT', "結果");
define('_JA_UPGRADE_REQ_PHP_VERSION', "PHP バージョン");
define('_JA_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_JA_UPGRADE_REQ_DIRECTORY', "{0} ディレクトリー");
define('_JA_UPGRADE_REQ_EXTENSION', "{0} 拡張");
define('_JA_UPGRADE_REQ_FILE_UPLOAD', "ファイルのアップロード");
define('_JA_UPGRADE_REQ_SAFE_MODE', "セーフモード");
define('_JA_UPGRADE_REQ_READABLE', "読み込み可能");
define('_JA_UPGRADE_REQ_WRITABLE', "書き込み可能");
define('_JA_UPGRADE_REQ_OK', "OK");
define('_JA_UPGRADE_REQ_BAD', "BAD");
define('_JA_UPGRADE_REQ_OFF', "オフ");
define('_JA_UPGRADE_REQ_ON', "オン");
define('_JA_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "ディレクトリー {0} は、読み込み、もしくは書き込みのどちらかができません。権限を修正してください。");
define('_JA_UPGRADE_REQ_RESPONSE_PHP_VERSION', "The minimum PHP version to upgrade Jaws is {0}, therefore you must upgrade your PHP version.");
define('_JA_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "The directories listed below as {0} are either not readable or writable, please fix their permissions.");
define('_JA_UPGRADE_REQ_RESPONSE_EXTENSION', "{0} 機能拡張は Jaws を使用するには必要です。");
define('_JA_UPGRADE_DB_INFO', "You now need to setup your database, which is used to get information of your current database and upgrade it.");
define('_JA_UPGRADE_DB_HOST', "ホスト名");
define('_JA_UPGRADE_DB_HOST_INFO', "If you don't know this, it's probably safe to leave it as {0}.");
define('_JA_UPGRADE_DB_DRIVER', "ドライバー");
define('_JA_UPGRADE_DB_USER', "ユーザー名");
define('_JA_UPGRADE_DB_PASS', "パスワード");
define('_JA_UPGRADE_DB_IS_ADMIN', "DB 管理者ですか?");
define('_JA_UPGRADE_DB_NAME', "データベース名");
define('_JA_UPGRADE_DB_PATH', "データベースのパス");
define('_JA_UPGRADE_DB_PATH_INFO', "Only fill this field out if you like change your database path in SQLite, Interbase and Firebird driver.");
define('_JA_UPGRADE_DB_PORT', "データベースのポート番号");
define('_JA_UPGRADE_DB_PORT_INFO', "Only fill this field out if your database is running on an another port then the default is.<br />If you have <strong>no idea</strong> what port the database is running then most likely it's running on the default port and thus we <strong>advice</strong> you to leave this field alone.");
define('_JA_UPGRADE_DB_PREFIX', "テーブル名の接頭語");
define('_JA_UPGRADE_DB_PREFIX_INFO', "Some text that will be placed in front of table names, so you can run more than one Jaws site from the same database, for example <strong>blog_</strong>");
define('_JA_UPGRADE_DB_RESPONSE_PATH', "データベースのパスが存在しません");
define('_JA_UPGRADE_DB_RESPONSE_PORT', "ポート番号は数値のみを使用できます");
define('_JA_UPGRADE_DB_RESPONSE_INCOMPLETE', "You must fill in all the fields apart from database path, table prefix and port.");
define('_JA_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "There was a problem connecting to the database, please check the details and try again.");
define('_JA_UPGRADE_REPORT_INFO', "現在の {0} とインストール済み Jaws のバージョンの比較中");
define('_JA_UPGRADE_REPORT_NOTICE', "Below you will find the versions that this Upgrade system can take care of. Maybe you are running a very old version, so we will take care of the rest.");
define('_JA_UPGRADE_REPORT_NEED', "アップグレードを必要とします");
define('_JA_UPGRADE_REPORT_NO_NEED', "アップグレードを必要としません");
define('_JA_UPGRADE_REPORT_NO_NEED_CURRENT', "Does not requires upgrade(is current)");
define('_JA_UPGRADE_REPORT_MESSAGE', "If the upgrade found that your installed Jaws version is old it will upgrade it, if not, it will end.");
define('_JA_UPGRADE_VER_INFO', "{0} から {1} へアップグレードするでしょう");
define('_JA_UPGRADE_VER_NOTES', "<strong>Note:</strong> Once you finish upgrading your Jaws version, other gadgets (like Blog, Phoo, etc) will require to be upgraded. You can do this task by logging in the Control Panel.");
define('_JA_UPGRADE_VER_RESPONSE_GADGET_FAILED', "here was a problem installing core gadget {0}");
define('_JA_UPGRADE_CONFIG_INFO', "設定ファイルを今すぐ保存する必要があります。");
define('_JA_UPGRADE_CONFIG_SOLUTION', "You can do this in two ways");
define('_JA_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Make <strong>{0}</strong> writable, and hit next, which will allow the installer to save the configuration itself.");
define('_JA_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Copy and paste the contents of the box below into a file and save it as <strong>{0}</strong>");
define('_JA_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "設定ファイルの書き込み中にエラーです。");
define('_JA_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "ディレクトリー「config」を書き込みできるようにするか、自分自身で設定ファイル「{0}」を作成するか、どちらかの作業が必要です。");
define('_JA_UPGRADE_FINISH_INFO', "ウェブサイトの設定を今終了しました!");
define('_JA_UPGRADE_FINISH_CHOICES', "あなたは今 <a href=\"{0}\">サイトの訪問</a> か、<a href=\"{1}\">コントロールパネルにログイン</a> の 2 つのうちどちらかを選択できます。");
define('_JA_UPGRADE_FINISH_MOVE_LOG', "Note: If you turned on the logging option at the first stage we suggest you to save it and move it / delete it");
define('_JA_UPGRADE_FINISH_THANKS', "Jaws を利用してくださいまして、ありがとうございます!");
