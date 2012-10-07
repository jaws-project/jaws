<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Jaromír Červenka - cervajz@cervajz.com"
 * "Language-Team: CS"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_CS_INSTALL_INTRODUCTION', "Úvod");
define('_CS_INSTALL_AUTHENTICATION', "Autentifikace");
define('_CS_INSTALL_REQUIREMENTS', "Požadavky");
define('_CS_INSTALL_DATABASE', "Databáze");
define('_CS_INSTALL_CREATEUSER', "Vytvořit uživatele");
define('_CS_INSTALL_SETTINGS', "Nastavení");
define('_CS_INSTALL_WRITECONFIG', "Uložit konfiguraci");
define('_CS_INSTALL_FINISHED', "Dokončeno");
define('_CS_INSTALL_INTRO_WELCOME', "Vítejte v instalaci systému Jaws.");
define('_CS_INSTALL_INTRO_DATABASE', "Detaily k databázi - hostname, uživatelské jméno, heslo, název databáze.");
define('_CS_INSTALL_INTRO_MAIL', "Informace o vašem poštovním serveru (hostname, uživatelské jméno, heslo). Pouze pokud používáte poštovní server.");
define('_CS_INSTALL_INTRO_LOG', "Zaznamenat činnosti (a chyby) instalace do souboru ({0})");
define('_CS_INSTALL_AUTH_PATH_INFO', "Abychom si byli jistí, že jste opravdu vlastník webu, vytvořte prosím soubor nazvaný {0} ve složce instalace Jaws ({1})");
define('_CS_INSTALL_AUTH_UPLOAD', "Můžete nahrát soubor stejnou cestou jako jste nahráli instalaci Jaws.");
define('_CS_INSTALL_AUTH_KEY_INFO', "Soubor by měl obsahovat pouze kód zobrazený níže v boxu. Nic víc.");
define('_CS_INSTALL_AUTH_ENABLE_SECURITY', "Zapnout zabezpečenou instalaci (na bázi RSA)");
define('_CS_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "Chyba při generování RSA klíče. Prosím zkuste to znova.");
define('_CS_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "Chyba při generování RSA klíče. Není dostpné žádné matematické rozšíření.");
define('_CS_INSTALL_REQ_REQUIREMENT', "Požadováno");
define('_CS_INSTALL_REQ_OPTIONAL', "Volitelné ale doporučené");
define('_CS_INSTALL_REQ_RECOMMENDED', "Doporučené");
define('_CS_INSTALL_REQ_DIRECTIVE', "Direktiva");
define('_CS_INSTALL_REQ_ACTUAL', "Aktuální");
define('_CS_INSTALL_REQ_RESULT', "Výsledek");
define('_CS_INSTALL_REQ_PHP_VERSION', "Verze PHP");
define('_CS_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_CS_INSTALL_REQ_DIRECTORY', "{0} složka");
define('_CS_INSTALL_REQ_EXTENSION', "{0} rozšíření");
define('_CS_INSTALL_REQ_SAFE_MODE', "Safe mode");
define('_CS_INSTALL_REQ_READABLE', "Čitelný");
define('_CS_INSTALL_REQ_WRITABLE', "Zapisovatelný");
define('_CS_INSTALL_REQ_OK', "OK");
define('_CS_INSTALL_REQ_OFF', "Vypnuto");
define('_CS_INSTALL_REQ_ON', "Zapnuto");
define('_CS_INSTALL_REQ_RESPONSE_EXTENSION', "Rozšíření {0} je nezbytné pro používání Jaws.");
define('_CS_INSTALL_DB_INFO', "Nyní je třeba nastavit parametry databáze, která je použita pro uložení vašich informací.");
define('_CS_INSTALL_DB_HOST', "Hostname");
define('_CS_INSTALL_DB_HOST_INFO', "Pokud nevíte co toto znamená, raději to nechte tak jak to je {0}.");
define('_CS_INSTALL_DB_DRIVER', "Ovladač");
define('_CS_INSTALL_DB_USER', "Uživ. jméno");
define('_CS_INSTALL_DB_PASS', "Heslo");
define('_CS_INSTALL_DB_IS_ADMIN', "Je administrátor databáze?");
define('_CS_INSTALL_DB_NAME', "Název databáze");
define('_CS_INSTALL_DB_PATH', "Cesta k databázi");
define('_CS_INSTALL_DB_PORT', "Port databáze");
define('_CS_INSTALL_DB_PREFIX', "Prefix tabulek");
define('_CS_INSTALL_DB_RESPONSE_PATH', "Cesta k databázi neexistuje");
define('_CS_INSTALL_DB_RESPONSE_PORT', "Port může být pouze číselná hodnota");
define('_CS_INSTALL_DB_RESPONSE_SETTINGS', "Vyskytl se problém při nastavování databáze.");
define('_CS_INSTALL_USER_USER', "Uživatelské jméno");
define('_CS_INSTALL_USER_PASS', "Heslo");
define('_CS_INSTALL_USER_REPEAT', "Zopakovat");
define('_CS_INSTALL_USER_REPEAT_INFO', "Zopakujte vaše heslo pro vyloučení chyb.");
define('_CS_INSTALL_USER_NAME', "Jméno");
define('_CS_INSTALL_USER_NAME_INFO', "Vaše skutečné jméno");
define('_CS_INSTALL_USER_EMAIL', "E-mailová adresa");
define('_CS_INSTALL_USER_RESPONSE_INCOMPLETE', "Je třeba vyplnit uživatelské jméno, heslo (dvakrát).");
define('_CS_INSTALL_USER_RESPONSE_CREATE_FAILED', "Vyskytl se problém při vytváření uživatele.");
define('_CS_INSTALL_SETTINGS_SITE_NAME', "Název webu");
define('_CS_INSTALL_SETTINGS_SLOGAN', "Popis");
define('_CS_INSTALL_SETTINGS_SLOGAN_INFO', "Delší popis webu.");
define('_CS_INSTALL_SETTINGS_DEFAULT_GADGET', "První (defaultní) gadget");
define('_CS_INSTALL_SETTINGS_SITE_LANGUAGE', "Jazyk webu");
define('_CS_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "Hlavní jazyk vašeho webu,");
define('_CS_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Je potřeba vyplnit název webu.");
define('_CS_INSTALL_CONFIG_INFO', "Nyní je potřeba uložit konfigurační soubor.");
define('_CS_INSTALL_CONFIG_SOLUTION', "Můžete to udělat dvěma způsoby");
define('_CS_INSTALL_CONFIG_SOLUTION_UPLOAD', "Zkopírujte a vložte obsah boxu do souboru a uložte jej jako {0}");
define('_CS_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Vyskytla se neznámá chyba při zápisu konfiguračního souboru.");
define('_CS_INSTALL_FINISH_INFO', "Právě jste dokončil nastavení Vašeho webu!");
define('_CS_INSTALL_FINISH_CHOICES', "Nyní máte dvě možnosti, můžete buď <a href=\"{0}\">zobrazit web</a> nebo <a href=\"{1}\">se přihlásit do ovládacího panelu</a>.");
define('_CS_INSTALL_FINISH_THANKS', "Díky, že používáte Jaws!");
