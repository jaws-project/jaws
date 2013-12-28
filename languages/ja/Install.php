<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Tadashi Jokagi <elf2000@users.sourceforge.net>"
 * "Language-Team: JA"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_JA_INSTALL_INTRODUCTION', "導入");
define('_JA_INSTALL_AUTHENTICATION', "認証");
define('_JA_INSTALL_REQUIREMENTS', "要求");
define('_JA_INSTALL_DATABASE', "データベース");
define('_JA_INSTALL_CREATEUSER', "ユーザーの作成");
define('_JA_INSTALL_SETTINGS', "設定");
define('_JA_INSTALL_WRITECONFIG', "設定の保存");
define('_JA_INSTALL_FINISHED', "完了");
define('_JA_INSTALL_INTRO_WELCOME', "Jaws インストーラーへようこそ。");
define('_JA_INSTALL_INTRO_INSTALLER', "Using the installer you will be guided through setting up your website, please make sure you have the following things available");
define('_JA_INSTALL_INTRO_DATABASE', "データベースの詳細 - ホスト名、ユーザー名、パスワード、データベース名。");
define('_JA_INSTALL_INTRO_FTP', "ファイルをアップロードする方法(おそらく FTP)。");
define('_JA_INSTALL_INTRO_MAIL', "Your Mailserver informations (hostname, username, password) if you are using a mailserver.");
define('_JA_INSTALL_INTRO_LOG', "ファイル {0} にインストール処理(とエラー)を記録します");
define('_JA_INSTALL_INTRO_LOG_ERROR', "Note: If you want to log the process (and errors) of the installation to a file you first need to set write-access permissions to the ({0}) directory and then refresh this page with your browser");
define('_JA_INSTALL_AUTH_PATH_INFO', "To make sure that you are really the owner of this site, please create a file called <strong>{0}</strong> in your Jaws installation directory (<strong>{1}</strong>).");
define('_JA_INSTALL_AUTH_UPLOAD', "You can upload the file in the same way you uploaded your Jaws install.");
define('_JA_INSTALL_AUTH_KEY_INFO', "The file should contain the code shown in the box below, and nothing else.");
define('_JA_INSTALL_AUTH_ENABLE_SECURITY', "セキュアなインストールを有効にする (Powered by RSA)");
define('_JA_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "RSA キーの生成でエラーです。再度試してください。");
define('_JA_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "RSA キー生成のエラーです。いくつかの数学関数機能拡張が利用できません。");
define('_JA_INSTALL_AUTH_ERROR_KEY_FILE', "鍵ファイル ({0}) が見つかりません。それを作成し、ウェブサーバーが読み込めるようにしてください。");
define('_JA_INSTALL_AUTH_ERROR_KEY_MATCH', "鍵ファイル ({0}) が下記と一致しません。入力された鍵が正しい鍵か確認してください。");
define('_JA_INSTALL_REQ_REQUIREMENT', "要求");
define('_JA_INSTALL_REQ_OPTIONAL', "推奨オプション");
define('_JA_INSTALL_REQ_RECOMMENDED', "推奨");
define('_JA_INSTALL_REQ_DIRECTIVE', "ディレクティブ");
define('_JA_INSTALL_REQ_ACTUAL', "実際");
define('_JA_INSTALL_REQ_RESULT', "結果");
define('_JA_INSTALL_REQ_PHP_VERSION', "PHP バージョン");
define('_JA_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_JA_INSTALL_REQ_DIRECTORY', "{0} ディレクトリー");
define('_JA_INSTALL_REQ_EXTENSION', "{0} 機能拡張");
define('_JA_INSTALL_REQ_FILE_UPLOAD', "ファイルアップロード");
define('_JA_INSTALL_REQ_SAFE_MODE', "セーフモード");
define('_JA_INSTALL_REQ_READABLE', "読み込み可能");
define('_JA_INSTALL_REQ_WRITABLE', "書き込み可能");
define('_JA_INSTALL_REQ_OK', "OK");
define('_JA_INSTALL_REQ_BAD', "BAD");
define('_JA_INSTALL_REQ_OFF', "オフ");
define('_JA_INSTALL_REQ_ON', "オン");
define('_JA_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "ディレクトリー {0} は、読み込み、もしくは書き込みのどちらかができません。権限を修正してください。");
define('_JA_INSTALL_REQ_RESPONSE_PHP_VERSION', "The minimum PHP version to install Jaws is {0}, therefore you must upgrade your PHP version.");
define('_JA_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "The directories listed below as {0} are either not readable or writable, please fix their permissions.");
define('_JA_INSTALL_REQ_RESPONSE_EXTENSION', "{0} 機能拡張は Jaws を使用するには必要です。");
define('_JA_INSTALL_DB_INFO', "You now need to setup your database, which is used to store your information to be displayed later.");
define('_JA_INSTALL_DB_NOTICE', "The database that you provide details for must already be created for this process to work.");
define('_JA_INSTALL_DB_HOST', "ホスト名");
define('_JA_INSTALL_DB_HOST_INFO', "If you don't know this, it's probably safe to leave it as {0}.");
define('_JA_INSTALL_DB_DRIVER', "ドライバー");
define('_JA_INSTALL_DB_USER', "ユーザー名");
define('_JA_INSTALL_DB_PASS', "パスワード");
define('_JA_INSTALL_DB_IS_ADMIN', "データベース管理者ですか?");
define('_JA_INSTALL_DB_NAME', "データベース名");
define('_JA_INSTALL_DB_PATH', "データベースのパス");
define('_JA_INSTALL_DB_PATH_INFO', "Only fill this field out if you like change your database path in SQLite, Interbase and Firebird driver.");
define('_JA_INSTALL_DB_PORT', "データベースのポート番号");
define('_JA_INSTALL_DB_PORT_INFO', "Only fill this field out if your database is running on an another port then the default is.<br />If you have <strong>no idea</strong> what port the database is running then most likely it's running on the default port and thus we <strong>advice</strong> you to leave this field alone.");
define('_JA_INSTALL_DB_PREFIX', "テーブル接頭語");
define('_JA_INSTALL_DB_PREFIX_INFO', "Some text that will be placed in front of table names, so you can run more than one Jaws site from the same database, for example <strong>blog_</strong>");
define('_JA_INSTALL_DB_RESPONSE_PATH', "データベースのパスが存在しません");
define('_JA_INSTALL_DB_RESPONSE_PORT', "ポートは数値のみ指定できます");
define('_JA_INSTALL_DB_RESPONSE_INCOMPLETE', "You must fill in all the fields apart from database path, table prefix and port.");
define('_JA_INSTALL_DB_RESPONSE_CONNECT_FAILED', "There was a problem connecting to the database, please check the details and try again.");
define('_JA_INSTALL_DB_RESPONSE_GADGET_INSTALL', "here was a problem installing core gadget {0}");
define('_JA_INSTALL_DB_RESPONSE_SETTINGS', "There was a problem while setting up the database.");
define('_JA_INSTALL_USER_INFO', "今すぐ自分自身のユーザーアカウントを作成できます。");
define('_JA_INSTALL_USER_NOTICE', "Remember not to choose an easy to guess password since anyone who has your password has full control over your website.");
define('_JA_INSTALL_USER_USER', "ユーザー名");
define('_JA_INSTALL_USER_USER_INFO', "Your login name, which will be displayed by items you post.");
define('_JA_INSTALL_USER_PASS', "パスワード");
define('_JA_INSTALL_USER_REPEAT', "繰り返し");
define('_JA_INSTALL_USER_REPEAT_INFO', "Repeat your password to make sure there are no typos.");
define('_JA_INSTALL_USER_NAME', "名前");
define('_JA_INSTALL_USER_NAME_INFO', "本名です。");
define('_JA_INSTALL_USER_EMAIL', "電子メールアドレス");
define('_JA_INSTALL_USER_RESPONSE_PASS_MISMATCH', "The password and repeat boxes don't match, please try again.");
define('_JA_INSTALL_USER_RESPONSE_INCOMPLETE', "You must complete the username, password, and repeat boxes.");
define('_JA_INSTALL_USER_RESPONSE_CREATE_FAILED', "There was a problem while creating your user.");
define('_JA_INSTALL_SETTINGS_INFO', "You can now set the default settings for your site. You can change any of these later by logging into the Control Panel and selecting Settings.");
define('_JA_INSTALL_SETTINGS_SITE_NAME', "サイトの名前");
define('_JA_INSTALL_SETTINGS_SITE_NAME_INFO', "表示するサイトの名前です。");
define('_JA_INSTALL_SETTINGS_SLOGAN', "説明");
define('_JA_INSTALL_SETTINGS_SLOGAN_INFO', "サイトの長い説明です。");
define('_JA_INSTALL_SETTINGS_DEFAULT_GADGET', "標準のガジェット");
define('_JA_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "ホームページに訪問したときに表示するガオジェットです。");
define('_JA_INSTALL_SETTINGS_SITE_LANGUAGE', "サイトの言語");
define('_JA_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "サイトに表示する通常の言語です。");
define('_JA_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "サイト名を埋める必要があります。");
define('_JA_INSTALL_CONFIG_INFO', "今すぐ設定ファイルを保存する必要があります。");
define('_JA_INSTALL_CONFIG_SOLUTION', "You can do this in two ways");
define('_JA_INSTALL_CONFIG_SOLUTION_PERMISSION', "Make <strong>{0}</strong> writable, and hit next, which will allow the installer to save the configuration itself.");
define('_JA_INSTALL_CONFIG_SOLUTION_UPLOAD', "Copy and paste the contents of the box below into a file and save it as <strong>{0}</strong>");
define('_JA_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "設定ファイルの書き込み中にエラーです。");
define('_JA_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "ディレクトリー「config」を書き込みできるようにするか、自分自身で設定ファイル「{0}」を作成するか、どちらかの作業が必要です。");
define('_JA_INSTALL_FINISH_INFO', "ウェブサイトの設定を終了しました!");
define('_JA_INSTALL_FINISH_CHOICES', "あなたは今 <a href=\"{0}\">サイトの訪問</a> か、<a href=\"{1}\">コントロールパネルにログイン</a> の 2 つのうちどちらかを選択できます。");
define('_JA_INSTALL_FINISH_MOVE_LOG', "Note: If you turned on the logging option at the first stage we suggest you to save it and move it / delete it");
define('_JA_INSTALL_FINISH_THANKS', "Jaws を使ってくれてありがとうございます!");
