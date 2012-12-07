<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: Pawel Szczepanek <pauluz@pauluz.pl>"
 * "Language-Team: PL"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_PL_UPGRADE_INTRODUCTION', "Wstęp");
define('_PL_UPGRADE_AUTHENTICATION', "Autoryzacja");
define('_PL_UPGRADE_REQUIREMENTS', "Wymagania");
define('_PL_UPGRADE_DATABASE', "Baza Danych");
define('_PL_UPGRADE_REPORT', "Raport");
define('_PL_UPGRADE_VER_TO_VER', "{0} do {1}");
define('_PL_UPGRADE_SETTINGS', "Ustawienia");
define('_PL_UPGRADE_WRITECONFIG', "Zapisanie ustawień");
define('_PL_UPGRADE_FINISHED', "Zakończenie");
define('_PL_UPGRADE_INTRO_WELCOME', "Witamy w module uaktualniania Jaws.");
define('_PL_UPGRADE_INTRO_UPGRADER', "Używając tego modułu możesz uaktualnić swoją starą wersję Jaws do obecnie aktualnej. Sprawdź tylko czy posiadasz następujące dane");
define('_PL_UPGRADE_INTRO_DATABASE', "Szczegóły Twojej Bazy Danych - jej nazwę, nazwę hosta oraz nazwę użytkownika i hasło do Bazy SQL.");
define('_PL_UPGRADE_INTRO_FTP', "Możliwość zgrywania plików na serwer - czyli pewnie dostęp przez FTP.");
define('_PL_UPGRADE_INTRO_LOG', "zapisuj cały proces uaktualnienia (a także błędy) do pliku logowania ({0})");
define('_PL_UPGRADE_INTRO_LOG_ERROR', "Notka: Jeśli chcesz zapisać cały proces uaktualnienia (a także błędy) do pliku logowania MUSISZ najpierw ustawić prawa zapisu do katalogu ({0}) a następnie odświeżyć tą stronę w przeglądarce");
define('_PL_UPGRADE_AUTH_PATH_INFO', "Aby mieć pewność, że to Ty jesteś właścicielem tej strony WWW, proszę utwórz plik tekstowy <strong>{0}</strong> w katalogu upgrade gdzie zainstalowałeś Jaws (<strong>{1}</strong>).");
define('_PL_UPGRADE_AUTH_UPLOAD', "Możesz wgrać ten plik tak samo jak wgrałeś wszystkie inne pliki instalując Jaws.");
define('_PL_UPGRADE_AUTH_KEY_INFO', "Plik powinien zawierać poniższy kod i nic więcej (entery nie są ważne).");
define('_PL_UPGRADE_AUTH_ENABLE_SECURITY', "Włącz bezpieczną aktualizację (powered by RSA)");
define('_PL_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "Błąd podczas generowania klucza RSA. Proszę spróbuj ponownie.");
define('_PL_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "Błąd podczas generowania klucza RSA. Niedostępne żadne pasujące rozszerzenie.");
define('_PL_UPGRADE_AUTH_ERROR_KEY_FILE', "Twój plik z kluczem ({0}) nie został znaleziony; sprawdź czy został poprawnie utworzony i czy można go poprawnie odczytać.");
define('_PL_UPGRADE_AUTH_ERROR_KEY_MATCH', "Klucz w znalezionym pliku ({0}) nie pasuje do kodu podanego poniżej; sprawdź proszę czy poprawnie go skopiowałeś (entery nie są ważne).");
define('_PL_UPGRADE_REQ_REQUIREMENT', "Wymagane");
define('_PL_UPGRADE_REQ_OPTIONAL', "Opcjonalne ale zalecane");
define('_PL_UPGRADE_REQ_RECOMMENDED', "Zalecane");
define('_PL_UPGRADE_REQ_DIRECTIVE', "Parametr");
define('_PL_UPGRADE_REQ_ACTUAL', "Aktualnie");
define('_PL_UPGRADE_REQ_RESULT', "Wynik");
define('_PL_UPGRADE_REQ_PHP_VERSION', "Wersja PHP");
define('_PL_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_PL_UPGRADE_REQ_DIRECTORY', "katalog {0}");
define('_PL_UPGRADE_REQ_EXTENSION', "rozszerzenie {0}");
define('_PL_UPGRADE_REQ_FILE_UPLOAD', "Wgrywanie plików");
define('_PL_UPGRADE_REQ_SAFE_MODE', "Safe mode");
define('_PL_UPGRADE_REQ_READABLE', "Odczytywalny");
define('_PL_UPGRADE_REQ_WRITABLE', "Zapisywalny");
define('_PL_UPGRADE_REQ_OK', "OK");
define('_PL_UPGRADE_REQ_BAD', "ZŁE");
define('_PL_UPGRADE_REQ_OFF', "Off");
define('_PL_UPGRADE_REQ_ON', "On");
define('_PL_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "Katalog {0} NIE jest odczytywalny albo zapisywalny, proszę ustaw właściwe prawa dostępu.");
define('_PL_UPGRADE_REQ_RESPONSE_PHP_VERSION', "Minimalna wersja PHP dla Jaws to {0}, dlatego musisz upgrade'wać swoją instalację PHP.");
define('_PL_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "Wymienione katalogi NIE są odczytywalne albo zapisywalne {0}, proszę ustaw właściwe prawa dostępu.");
define('_PL_UPGRADE_REQ_RESPONSE_EXTENSION', "Rozszerzenie {0} jest niezbędne do działania Jaws.");
define('_PL_UPGRADE_DB_INFO', "Teraz należy skonfigurować Bazę Danych, która przechowuje aktualne informacje o Twojej stronie WWW - i uaktualnić ją.");
define('_PL_UPGRADE_DB_HOST', "Nazwa hosta");
define('_PL_UPGRADE_DB_HOST_INFO', "Jeśli nie znasz dokładnie swojej nazwy hosta dla Bazy SQL bezpiecznie jest pozostawić wartość domyślną ({0}).");
define('_PL_UPGRADE_DB_DRIVER', "Sterownik");
define('_PL_UPGRADE_DB_USER', "Użytkownik");
define('_PL_UPGRADE_DB_PASS', "Hasło");
define('_PL_UPGRADE_DB_IS_ADMIN', "Czy to Admin Bazy?");
define('_PL_UPGRADE_DB_NAME', "Nazwa Bazy");
define('_PL_UPGRADE_DB_PATH', "Ścieżka dla bazy danych");
define('_PL_UPGRADE_DB_PATH_INFO', "Wypełnij to pole tylko jeśli chcesz zmienić ścieżkę do bazy danych dla sterowników: SQLite, Interbase lub Firebird.");
define('_PL_UPGRADE_DB_PORT', "Port");
define('_PL_UPGRADE_DB_PORT_INFO', "Podaj tutaj wartość tylko jeśli Twoja Baza Danych działa na <strong>niestandardowym</strong> porcie.<br />Jeśli nie znasz portu Swojej Bazy Danych to pewnie Baza działa na porcie standardowym i <strong>najwłaściwsze</strong> będzie pozostawienie tego pola pustego.");
define('_PL_UPGRADE_DB_PREFIX', "Prefiks tabel");
define('_PL_UPGRADE_DB_PREFIX_INFO', "Krótki tekst umieszczany przed nazwą każdej tabeli, dzięki czemu możesz mieć kilka stron opartych na Jaws w tej samej Bazie Danych; przykład: <strong>blog_</strong>");
define('_PL_UPGRADE_DB_RESPONSE_PATH', "Ścieżka do bazy danych nie istnieje");
define('_PL_UPGRADE_DB_RESPONSE_PORT', "Numer portu musi posiadać wartość numeryczną");
define('_PL_UPGRADE_DB_RESPONSE_INCOMPLETE', "Musisz wypełnić wszystkie pola oprócz Portu i Prefiksu dla tabel.");
define('_PL_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "Wystąpił problem podczas połączenia do Bazy Danych, proszę sprawdź poprawność wpisanych wartości i spróbuj ponownie.");
define('_PL_UPGRADE_REPORT_INFO', "Porównanie wersji Twojej obecnej instalacji Jaws z aktualną {0}");
define('_PL_UPGRADE_REPORT_NOTICE', "Poniżej zobaczysz wersje Jaws jakimi ten moduł aktualizacji może się zająć. Możesz posiadać bardzo stare wersje Jaws - ten system przeprowadzi niezbędne uaktualnienia.");
define('_PL_UPGRADE_REPORT_NEED', "Wymaga uaktualnienia");
define('_PL_UPGRADE_REPORT_NO_NEED', "Nie wymaga uaktualnienia");
define('_PL_UPGRADE_REPORT_NO_NEED_CURRENT', "Nie wymaga uaktualnienia (wersja obecna)");
define('_PL_UPGRADE_REPORT_MESSAGE', "Jeśli moduł aktualizacji odkryje, że masz zainstalowaną starą wersję Jaws - aktualizuje ją, a jeśli nic nie znajdzie to zakończy swoje działanie.");
define('_PL_UPGRADE_VER_INFO', "Uaktualnienie z wersji {0} do wersji {1} ...");
define('_PL_UPGRADE_VER_NOTES', "<strong>Notka:</strong> Po skończonym procesie aktualizacji Twojej wersji Jaws, inne gadżety (takie jak np. Blog, Phoo, etc) wymagają osobnej aktualizacji! Możesz ją dokonać logując się do Panelu Kontrolnego.");
define('_PL_UPGRADE_VER_RESPONSE_GADGET_FAILED', "błąd podczas instalowania głównego gadżetu {0}");
define('_PL_UPGRADE_CONFIG_INFO', "Teraz należy zapisać Twój plik konfiguracyjny.");
define('_PL_UPGRADE_CONFIG_SOLUTION', "Możesz to zrobić na dwa sposoby");
define('_PL_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Ustaw katalog <strong>{0}</strong> na zapisywalny i naciśnij 'Następny', co pozwoli instalatorowi nagrać plik.");
define('_PL_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Skopiuj i wklej zawartość poniższego boksu do pliku i nagraj go jako <strong>{0}</strong>");
define('_PL_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "Wystąpił trudny do określenia błąd podczas zapisywania pliku konfiguracyjnego.");
define('_PL_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "Musisz ustawić katalog config tak aby był zapisywalny albo utworzyć plik {0} samemu.");
define('_PL_UPGRADE_FINISH_INFO', "Właśnie zakończyłeś proces aktualizacji i konfiguracji swojej strony WWW!");
define('_PL_UPGRADE_FINISH_CHOICES', "Masz teraz dwie możliwości: możesz albo <a href=\"{0}\">obejrzeć swoją stronę</a> albo <a href=\"{1}\">zalogować się do Panelu Kontrolnego</a>.");
define('_PL_UPGRADE_FINISH_MOVE_LOG', "Notka: Jeśli włączałeś na pierwszej stronie logowanie do pliku polecamy teraz sprawdzić ten plik a następnie <strong>przenieść go lub skasować</strong>!");
define('_PL_UPGRADE_FINISH_THANKS', "Dziękujemy za używanie Jaws!");
