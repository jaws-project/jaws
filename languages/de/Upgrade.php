<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: C.Tuemer <info@exceptionz.net>"
 * "Language-Team: DE"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_DE_UPGRADE_INTRODUCTION', "Einführung");
define('_DE_UPGRADE_AUTHENTICATION', "Authentifizierung");
define('_DE_UPGRADE_REQUIREMENTS', "Voraussetzungen");
define('_DE_UPGRADE_DATABASE', "Datenbank");
define('_DE_UPGRADE_REPORT', "Bericht");
define('_DE_UPGRADE_VER_TO_VER', "von {0} auf  {1}");
define('_DE_UPGRADE_SETTINGS', "Einstellungen");
define('_DE_UPGRADE_WRITECONFIG', "Konfiguration speichern");
define('_DE_UPGRADE_FINISHED', "Beendet");
define('_DE_UPGRADE_INTRO_WELCOME', "Herzlich Willkommen zum Upgradetool von Jaws");
define('_DE_UPGRADE_INTRO_UPGRADER', "Mit Hilfe dieses Tools können Sie eine alte Installation von Jaws aktualisieren. Bitte vergewissern Sie sich, dass Folgendes verfügbar ist");
define('_DE_UPGRADE_INTRO_DATABASE', "Datenbankdetails, Hostname, Benutzername, Passwort,  Datenbankname");
define('_DE_UPGRADE_INTRO_FTP', "Eine Möglichkeit, Dateien hochzuladen, voraussichtlich FTP.");
define('_DE_UPGRADE_INTRO_LOG', "Den Upgradeprozess (und Fehler) in die Datei ({0}) speichern ");
define('_DE_UPGRADE_INTRO_LOG_ERROR', "Achtung: Wenn Sie den Upgradeprozess (und die Fehler) loggen möchten, sorgen Sie dafür, dass auf das Verzeichnis ({0}) schreibend zugegriffen werden kann und laden diese Seite neu");
define('_DE_UPGRADE_AUTH_PATH_INFO', "Um sich als Inhaber dieser Seite zu identifizieren, erstellen Sie eine Datei mit dem Namen {0} in ihrem Jawsinstallationsverzeichnis ({1}).");
define('_DE_UPGRADE_AUTH_UPLOAD', "Sie können die Datei genauso hochladen, wie Sie die Jawsdateien hochgeladen haben.");
define('_DE_UPGRADE_AUTH_KEY_INFO', "Die Datei sollte ausschließlich folgenden Code beinhalten.");
define('_DE_UPGRADE_AUTH_ENABLE_SECURITY', "Sichere Installation aktivieren (Powered by RSA)");
define('_DE_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "Fehler bei der RSA Key-Generierung. Bitte versuchen Sie es noch einmal.");
define('_DE_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "Fehler bei der RSA Key-Generierung. Keine verfügbare Math-Erweiterung.");
define('_DE_UPGRADE_AUTH_ERROR_KEY_FILE', "Ihre Schlüsseldatei ({0}) wurde nicht gefunden, bitte vergewissern Sie sich, dass Sie sie erstellt haben und die Rechte richtig gesetzt sind. ");
define('_DE_UPGRADE_AUTH_ERROR_KEY_MATCH', "Die Schlüsseldatei ({0}) stimmt mit dem Code, der unten dargestellt wird, nicht überein. Bitte überprüfen Sie die Datei.");
define('_DE_UPGRADE_REQ_REQUIREMENT', "Voraussetzung");
define('_DE_UPGRADE_REQ_OPTIONAL', "Empfohlen aber nicht vorausgesetzt");
define('_DE_UPGRADE_REQ_RECOMMENDED', "Empfohlen");
define('_DE_UPGRADE_REQ_DIRECTIVE', "Befehl");
define('_DE_UPGRADE_REQ_ACTUAL', "Tatsächlich");
define('_DE_UPGRADE_REQ_RESULT', "Ergebnis");
define('_DE_UPGRADE_REQ_PHP_VERSION', "PHP-Version");
define('_DE_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_DE_UPGRADE_REQ_DIRECTORY', "Verzeichnis {0}");
define('_DE_UPGRADE_REQ_EXTENSION', "Erweiterung {0}");
define('_DE_UPGRADE_REQ_FILE_UPLOAD', "Datei-Uploads");
define('_DE_UPGRADE_REQ_SAFE_MODE', "Safemode");
define('_DE_UPGRADE_REQ_READABLE', "Lesezugriff");
define('_DE_UPGRADE_REQ_WRITABLE', "Schreibzugriff");
define('_DE_UPGRADE_REQ_OK', "OK");
define('_DE_UPGRADE_REQ_BAD', "BAD");
define('_DE_UPGRADE_REQ_OFF', "Aus");
define('_DE_UPGRADE_REQ_ON', "An");
define('_DE_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "Auf das Verzeichnis {0} kann entweder nicht lesend, oder nicht schreibend zugegriffen werden. Bitte überprüfen Sie die Rechte.");
define('_DE_UPGRADE_REQ_RESPONSE_PHP_VERSION', "Die minimale PHP-Version für die Jawsinstallation ist {0}, daher müssen Sie Ihre PHP-Version aktualisieren. ");
define('_DE_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "Auf die Verzeichnisse, die unten dargestellt sind, kann entweder nicht lesend, oder nicht schreibend zugegriffen werden. Bitte überprüfen Sie die Rechte.");
define('_DE_UPGRADE_REQ_RESPONSE_EXTENSION', "Sie benötigen die Erweiterung {0}, um Jaws zu verwenden. ");
define('_DE_UPGRADE_DB_INFO', "Nun müssen Sie Ihre Datenbank konfigurieren.");
define('_DE_UPGRADE_DB_HOST', "Hostname");
define('_DE_UPGRADE_DB_HOST_INFO', "Wenn Sie nicht wissen, was Sie tun, dann ist es wahrscheinlich besser, wenn Sie es als {0} lassen.");
define('_DE_UPGRADE_DB_DRIVER', "Treiber");
define('_DE_UPGRADE_DB_USER', "Benutzername");
define('_DE_UPGRADE_DB_PASS', "Passwort");
define('_DE_UPGRADE_DB_IS_ADMIN', "Ist DB-Admin?");
define('_DE_UPGRADE_DB_NAME', "Datenbankname");
define('_DE_UPGRADE_DB_PATH', "Datenbankpfad");
define('_DE_UPGRADE_DB_PATH_INFO', "Füllen Sie dieses Feld nur dann aus, wenn Sie Ihre SQLite,Interbase oder Firebird benutzen wollen.");
define('_DE_UPGRADE_DB_PORT', "Datenbankport");
define('_DE_UPGRADE_DB_PORT_INFO', "Füllen Sie dieses Feld nur dann aus, wenn Ihre Datenbank an einem anderen Port als Standardport lauscht. \n\nWenn Sie nicht wissen, an welchem Port die Datenbank lauscht, dann ist es wahrscheinlich besser, dass Sie dieses Feld leer lassen.");
define('_DE_UPGRADE_DB_PREFIX', "Tabellenpräfix");
define('_DE_UPGRADE_DB_PREFIX_INFO', "Tabellenpräfix für Ihre Jawssite, damit Sie für verschiedene Sites die selbe Datenbank verwenden können; beispielsweise blog_");
define('_DE_UPGRADE_DB_RESPONSE_PATH', "Der Datenbankpfad existiert nicht");
define('_DE_UPGRADE_DB_RESPONSE_PORT', "Der Port kann nur einen numerischen Wert haben");
define('_DE_UPGRADE_DB_RESPONSE_INCOMPLETE', "Sie müssen alle Felder außer Datanbankpfad, Tabellenpräfix und Port ausfüllen.");
define('_DE_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "Fehler bei der Verbindung zu der Datenbank, bitte überprüfen Sie Ihre Konfiguration und versuchen es erneut.");
define('_DE_UPGRADE_REPORT_INFO', "Vergleiche Ihre Jawsversion mit der aktuellen {0}");
define('_DE_UPGRADE_REPORT_NOTICE', "Unten finden Sie alle Versionen, mit denen dieses Upgradesystem arbeiten kann. Vielleicht haben Sie eine sehr alte Jawsversion, so dass wir den Rest übernehmen.");
define('_DE_UPGRADE_REPORT_NEED', "Upgrade nötig");
define('_DE_UPGRADE_REPORT_NO_NEED', "Upgrade nicht nötig");
define('_DE_UPGRADE_REPORT_NO_NEED_CURRENT', "Upgrade nicht nötig (ist aktuell)");
define('_DE_UPGRADE_REPORT_MESSAGE', "Wenn das Upgradesystem Ihre Jawsinstallation als veraltet identifiziert, wird es mit dem Upgrade fortfahren, ansonsten wird es den Vorgang sofort abschließen.");
define('_DE_UPGRADE_VER_INFO', "Upgrade von {0} auf {1}");
define('_DE_UPGRADE_VER_NOTES', "Achtung: Wenn Sie mit dem Upgrade Ihrer Jawsversion fertig sind, müssen andere Gadgets wie (Blog, Phoo, etc.) aktualisiert werden. Dies können Sie in Ihrem Administratorpanel durchführen.");
define('_DE_UPGRADE_VER_RESPONSE_GADGET_FAILED', "Fehler beim Installieren des Coregadgets {0}");
define('_DE_UPGRADE_CONFIG_INFO', "Sie müssen nun Ihre Konfigurationsdatei speichern.");
define('_DE_UPGRADE_CONFIG_SOLUTION', "Dies kann folgendermaßen realisiert werden");
define('_DE_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Sorgen Sie dafür, dass auf {0} lesend zugegriffen werden kann und klicken auf weiter. ");
define('_DE_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Kopieren Sie den Code und fügen ihn in einer neuen Datei hinzu und speichern diese als {0}");
define('_DE_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "Es trat ein unbekannter Fehler beim Schreiben der Konfigurationsdatei auf.");
define('_DE_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "Entweder muss auf das Configverzeichnis schreibend zugegriffen, oder die Datei {0} manuell erstellt werden.");
define('_DE_UPGRADE_FINISH_INFO', "Sie haben den Upgradevorgang erfolgreich abgeschlossen!");
define('_DE_UPGRADE_FINISH_CHOICES', "Sie haben nun zwei Möglichkeiten: <a href=\"{0}\">Site anzeigen</a> oder <a href=\"{0}\">In das Administratorpanel einloggen </a>");
define('_DE_UPGRADE_FINISH_MOVE_LOG', "Achtung: Wenn Sie die Loggingoption ausgewählt haben, raten wir Ihnen, die Datei herunterzuladen und dann zu verschieben oder zu löschen.");
define('_DE_UPGRADE_FINISH_THANKS', "Vielen Dank, dass Sie sich für Jaws entschieden haben!");
