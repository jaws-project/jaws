<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: C.Tuemer <info@exceptionz.net>"
 * "Language-Team: DE"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_DE_INSTALL_INTRODUCTION', "Einleitung");
define('_DE_INSTALL_AUTHENTICATION', "Authentifizierung");
define('_DE_INSTALL_REQUIREMENTS', "Voraussetzungen");
define('_DE_INSTALL_DATABASE', "Datenbank");
define('_DE_INSTALL_CREATEUSER', "Benutzer erstellen");
define('_DE_INSTALL_SETTINGS', "Einstellungen");
define('_DE_INSTALL_WRITECONFIG', "Konfiguration speichern");
define('_DE_INSTALL_FINISHED', "Abgeschlossen");
define('_DE_INSTALL_INTRO_WELCOME', "Herzlich Willkommen zum Jawsinstaller.");
define('_DE_INSTALL_INTRO_INSTALLER', "Während des Installationsvorgangs werden Sie durch die einzelnen Schritte geführt. Bitte vergewissern Sie sich, dass Folgendes verfügbar ist");
define('_DE_INSTALL_INTRO_DATABASE', "Datenbankdetails - Hostname, Benutzername, Passwort, Datenbankname.");
define('_DE_INSTALL_INTRO_FTP', "Eine Möglichkeit, Dateien hochzuladen, voraussichtlich FTP.");
define('_DE_INSTALL_INTRO_MAIL', "Ihre Mailserverinformationen (Hostname, Benutzername, Passwort), wenn Sie einen Mailserver verwenden.");
define('_DE_INSTALL_INTRO_LOG', "Den Installationsprozess (und Fehler) in die Datei ({0}) speichern ");
define('_DE_INSTALL_INTRO_LOG_ERROR', "Achtung: Wenn Sie den Installationsvorgang (und die Fehler) loggen möchten, sorgen Sie dafür, dass auf das Verzeichnis ({0}) schreibend zugegriffen werden kann und laden diese Seite neu.");
define('_DE_INSTALL_AUTH_PATH_INFO', "Um sich als Inhaber dieser Seite zu identifizieren, erstellen Sie eine Datei mit dem Namen {0} in ihrem Jawsinstallationsverzeichnis ({1}).");
define('_DE_INSTALL_AUTH_UPLOAD', "Sie können die Datei genauso hochladen, wie Sie die Jawsdateien hochgeladen haben.");
define('_DE_INSTALL_AUTH_KEY_INFO', "Die Datei sollte ausschließlich folgenden Code beinhalten.");
define('_DE_INSTALL_AUTH_ENABLE_SECURITY', "Sichere Installation aktivieren (Powered by RSA)");
define('_DE_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "Fehler bei der RSA Key-Generierung. Bitte versuchen Sie es noch einmal.");
define('_DE_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "Fehler bei der RSA Key-Generierung. Keine verfügbare Math-Erweiterung.");
define('_DE_INSTALL_AUTH_ERROR_KEY_FILE', "Ihre Schlüsseldatei ({0}) wurde nicht gefunden, bitte vergewissern Sie sich, dass Sie sie erstellt haben und die Rechte richtig gesetzt sind. ");
define('_DE_INSTALL_AUTH_ERROR_KEY_MATCH', "Die Schlüsseldatei ({0}) stimmt mit dem Code, der unten dargestellt wird, nicht überein. Bitte überprüfen Sie die Datei.");
define('_DE_INSTALL_REQ_REQUIREMENT', "Voraussetzung");
define('_DE_INSTALL_REQ_OPTIONAL', "Empfohlen aber nicht vorausgesetzt");
define('_DE_INSTALL_REQ_RECOMMENDED', "Empfohlen");
define('_DE_INSTALL_REQ_DIRECTIVE', "Befehl");
define('_DE_INSTALL_REQ_ACTUAL', "Tatsächlich");
define('_DE_INSTALL_REQ_RESULT', "Ergebnis");
define('_DE_INSTALL_REQ_PHP_VERSION', "PHP-Version");
define('_DE_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_DE_INSTALL_REQ_DIRECTORY', "Verzeichnis {0}");
define('_DE_INSTALL_REQ_EXTENSION', "Erweiterung {0}");
define('_DE_INSTALL_REQ_FILE_UPLOAD', "Datei-Uploads");
define('_DE_INSTALL_REQ_SAFE_MODE', "Safemode");
define('_DE_INSTALL_REQ_READABLE', "Lesezugriff");
define('_DE_INSTALL_REQ_WRITABLE', "Schreibzugriff");
define('_DE_INSTALL_REQ_OK', "OK");
define('_DE_INSTALL_REQ_BAD', "Fehler");
define('_DE_INSTALL_REQ_OFF', "Aus");
define('_DE_INSTALL_REQ_ON', "An");
define('_DE_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "Auf das Verzeichnis {0} kann entweder nicht lesend, oder nicht schreibend zugegriffen werden. Bitte überprüfen Sie die Rechte.");
define('_DE_INSTALL_REQ_RESPONSE_PHP_VERSION', "Die minimale PHP-Version für die Jawsinstallation ist {0}, daher müssen Sie Ihre PHP-Version aktualisieren. ");
define('_DE_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "Auf die Verzeichnisse, die unten dargestellt sind, kann entweder nicht lesend, oder nicht schreibend zugegriffen werden. Bitte überprüfen Sie die Rechte.");
define('_DE_INSTALL_REQ_RESPONSE_EXTENSION', "Sie benötigen die Erweiterung {0}, um Jaws zu verwenden. ");
define('_DE_INSTALL_DB_INFO', "Nun müssen Sie Ihre Datenbank konfigurieren.");
define('_DE_INSTALL_DB_NOTICE', "Die Datenbank, die Sie verwenden wollen, muss schon existieren.");
define('_DE_INSTALL_DB_HOST', "Hostname");
define('_DE_INSTALL_DB_HOST_INFO', "Wenn Sie nicht wissen, was Sie tun, dann ist es wahrscheinlich besser, wenn Sie es als {0} lassen.");
define('_DE_INSTALL_DB_DRIVER', "Treiber");
define('_DE_INSTALL_DB_USER', "Benutzername");
define('_DE_INSTALL_DB_PASS', "Passwort");
define('_DE_INSTALL_DB_IS_ADMIN', "Ist DB-Admin?");
define('_DE_INSTALL_DB_NAME', "Datenbankname");
define('_DE_INSTALL_DB_PATH', "Datenbankpfad");
define('_DE_INSTALL_DB_PATH_INFO', "Füllen Sie dieses Feld nur dann aus, wenn Sie Ihre SQLite,Interbase oder Firebird benutzen wollen.");
define('_DE_INSTALL_DB_PORT', "Datenbankport");
define('_DE_INSTALL_DB_PORT_INFO', "Füllen Sie dieses Feld nur dann aus, wenn Ihre Datenbank an einem anderen Port als Standardport lauscht. \n\nWenn Sie nicht wissen, an welchem Port die Datenbank lauscht, dann ist es wahrscheinlich besser, dass Sie dieses Feld leer lassen.");
define('_DE_INSTALL_DB_PREFIX', "Tabellenpräfix");
define('_DE_INSTALL_DB_PREFIX_INFO', "Tabellenpräfix für Ihre Jawssite, damit Sie für verschiedene Sites die selbe Datenbank verwenden können; beispielsweise blog_");
define('_DE_INSTALL_DB_RESPONSE_PATH', "Der Datenbankpfad existiert nicht");
define('_DE_INSTALL_DB_RESPONSE_PORT', "Der Port kann nur einen numerischen Wert haben");
define('_DE_INSTALL_DB_RESPONSE_INCOMPLETE', "Sie müssen alle Felder außer Datanbankpfad, Tabellenpräfix und Port ausfüllen.");
define('_DE_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Fehler bei der Verbindung zu der Datenbank, bitte überprüfen Sie Ihre Konfiguration und versuchen es erneut.");
define('_DE_INSTALL_DB_RESPONSE_GADGET_INSTALL', "Fehler beim Installieren des Coregadgets {0}");
define('_DE_INSTALL_DB_RESPONSE_SETTINGS', "Es trat ein Fehler bei der Einrichtung der Datenbank auf.");
define('_DE_INSTALL_USER_INFO', "Sie können nun einen Benutzeraccount für sich selbst anlegen.");
define('_DE_INSTALL_USER_NOTICE', "Bitte vergewissern Sie sich, dass Sie kein einfaches Passwort auswählen, da man mit diesem Passwort die volle Kontrolle über die Website erlangen kann.");
define('_DE_INSTALL_USER_USER', "Benutzername");
define('_DE_INSTALL_USER_USER_INFO', "Ihr Benutzername, dieser wird bei Einträgen, die Sie erstellen, angezeigt.");
define('_DE_INSTALL_USER_PASS', "Passwort");
define('_DE_INSTALL_USER_REPEAT', "Noch einmal eingeben");
define('_DE_INSTALL_USER_REPEAT_INFO', "Bitte geben Sie Ihr Passwort noch einmal ein.");
define('_DE_INSTALL_USER_NAME', "Name");
define('_DE_INSTALL_USER_NAME_INFO', "Realer Name");
define('_DE_INSTALL_USER_EMAIL', "E-Mail-Adresse");
define('_DE_INSTALL_USER_RESPONSE_PASS_MISMATCH', "Die Passwörter stimmen nicht überein, bitte versuchen Sie es noch einmal.");
define('_DE_INSTALL_USER_RESPONSE_INCOMPLETE', "Es müssen alle Felder ausgefüllt werden.");
define('_DE_INSTALL_USER_RESPONSE_CREATE_FAILED', "Fehler beim Erstellen des Accounts.");
define('_DE_INSTALL_SETTINGS_INFO', "Nun können Sie die Standardoptionen für Ihre Site einstellen. Diese können Sie jederzeit in Ihrem Administratorpanel verändern.");
define('_DE_INSTALL_SETTINGS_SITE_NAME', "Sitename");
define('_DE_INSTALL_SETTINGS_SITE_NAME_INFO', "Der Name, der auf der Seite dargestellt wird.");
define('_DE_INSTALL_SETTINGS_SLOGAN', "Siteslogan");
define('_DE_INSTALL_SETTINGS_SLOGAN_INFO', "Eine längere Beschreibung der Seite.");
define('_DE_INSTALL_SETTINGS_DEFAULT_GADGET', "Standardgadget");
define('_DE_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "Standardgadget zum Anzeigen auf der Hauptseite.");
define('_DE_INSTALL_SETTINGS_SITE_LANGUAGE', "Sitesprache");
define('_DE_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "Die voreingestellte Sprache.");
define('_DE_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Sie müssen einen Namen eingeben.");
define('_DE_INSTALL_CONFIG_INFO', "Sie müssen nun Ihre Konfigurationsdatei speichern.");
define('_DE_INSTALL_CONFIG_SOLUTION', "Dies kann folgendermaßen realisiert werden");
define('_DE_INSTALL_CONFIG_SOLUTION_PERMISSION', "Sorgen Sie dafür, dass auf {0} lesend zugegriffen werden kann und klicken auf weiter. ");
define('_DE_INSTALL_CONFIG_SOLUTION_UPLOAD', "Kopieren Sie den Code und fügen ihn in einer neuen Datei hinzu und speichern diese als {0}");
define('_DE_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Es trat ein unbekannter Fehler beim Schreiben der Konfigurationsdatei auf.");
define('_DE_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "Entweder muss auf das Configverzeichnis schreibend zugegriffen, oder die Datei {0} manuell erstellt werden.");
define('_DE_INSTALL_FINISH_INFO', "Sie haben die Installation erfolgreich abgeschlossen!");
define('_DE_INSTALL_FINISH_CHOICES', "Sie haben nun zwei Möglichkeiten: <a href=\"{0}\">Site anzeigen</a> oder <a href=\"{0}\">In das Administratorpanel einloggen </a>");
define('_DE_INSTALL_FINISH_MOVE_LOG', "Achtung: Wenn Sie die Loggingoption ausgewählt haben, raten wir Ihnen, die Datei herunterzuladen und dann zu verschieben oder zu löschen.");
define('_DE_INSTALL_FINISH_THANKS', "Vielen Dank, dass Sie sich für Jaws entschieden haben!");
