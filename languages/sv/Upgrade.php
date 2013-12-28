<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: Thomas Lilliesköld <thomas.lillieskold@gmail.com>"
 * "Language-Team: SV"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_SV_UPGRADE_INTRODUCTION', "Introducering");
define('_SV_UPGRADE_AUTHENTICATION', "Autentisering");
define('_SV_UPGRADE_REQUIREMENTS', "Systemkrav");
define('_SV_UPGRADE_DATABASE', "Databas");
define('_SV_UPGRADE_REPORT', "Rapportera");
define('_SV_UPGRADE_VER_TO_VER', "{0} till {1}");
define('_SV_UPGRADE_SETTINGS', "Inställningar");
define('_SV_UPGRADE_WRITECONFIG', "Spara konfiguration");
define('_SV_UPGRADE_FINISHED', "Klar");
define('_SV_UPGRADE_INTRO_WELCOME', "Välkommen till Jaws uppgraderingscript");
define('_SV_UPGRADE_INTRO_UPGRADER', "Med uppgraderingscriptet kan du uppgradera en gammal version till den nya. Se bara till att ha följande klart");
define('_SV_UPGRADE_INTRO_DATABASE', "Databasdetaljer - värdnamn, användarnamn, lösenord och databasnamn.");
define('_SV_UPGRADE_INTRO_FTP', "Ett sätt att ladda upp filer, förmodligen FTP");
define('_SV_UPGRADE_INTRO_LOG', "Logga uppgraderingen (och ev fel) och spara till fil ({0})");
define('_SV_UPGRADE_INTRO_LOG_ERROR', "OBS: Om du vill logga uppgraderingen(och ev. fel) till en fil måste du först sätta skrivrättigheter på mappen {0} och därefter ladda om sidan i webbläsaren.");
define('_SV_UPGRADE_AUTH_PATH_INFO', "För att säkerställa att du verkligen är sajtens ägare ska en fil med namn <strong>{0}</strong> skapas i Jaws installationsmapp (<strong>{1}</strong>).");
define('_SV_UPGRADE_AUTH_UPLOAD', "Du kan ladda upp filen på samma sätt som du laddade upp Jaws.");
define('_SV_UPGRADE_AUTH_KEY_INFO', "Filen ska innehålla koden nedan och inget annat.");
define('_SV_UPGRADE_AUTH_ENABLE_SECURITY', "Aktivera säker installation (RSA-understödd).");
define('_SV_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "Fel i skapandetav RSA-nyckeln, vänligen försök igen.");
define('_SV_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "Fel i skapandet av RSA-nyckeln.(No available math extension)");
define('_SV_UPGRADE_AUTH_ERROR_KEY_FILE', "Din nyckelfil ({0}) hittades ej, vänligen säkerställ att den finns och att webbservern kan läsa den.");
define('_SV_UPGRADE_AUTH_ERROR_KEY_MATCH', "Den hittade nyckeln ({0}) matchar inte den nedan, vänligen kontrollera att du fyllt i nyckeln korrekt.");
define('_SV_UPGRADE_REQ_REQUIREMENT', "Systemkrav");
define('_SV_UPGRADE_REQ_OPTIONAL', "Valfritt men rekommenderas");
define('_SV_UPGRADE_REQ_RECOMMENDED', "Rekommenderas");
define('_SV_UPGRADE_REQ_DIRECTIVE', "Direktiv");
define('_SV_UPGRADE_REQ_ACTUAL', "Egentlig");
define('_SV_UPGRADE_REQ_RESULT', "Resultat");
define('_SV_UPGRADE_REQ_PHP_VERSION', "PHP-version");
define('_SV_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_SV_UPGRADE_REQ_DIRECTORY', "mappen {0}");
define('_SV_UPGRADE_REQ_EXTENSION', "tillägget {0}");
define('_SV_UPGRADE_REQ_FILE_UPLOAD', "Uppladdningar");
define('_SV_UPGRADE_REQ_SAFE_MODE', "Safe mode");
define('_SV_UPGRADE_REQ_READABLE', "Läsbar");
define('_SV_UPGRADE_REQ_WRITABLE', "Skrivbar");
define('_SV_UPGRADE_REQ_OK', "Ok");
define('_SV_UPGRADE_REQ_BAD', "BAD");
define('_SV_UPGRADE_REQ_OFF', "Av");
define('_SV_UPGRADE_REQ_ON', "På");
define('_SV_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "Mappen {0} är antingen ej läsbar eller skrivbar, vänligen ändra rättigheterna.");
define('_SV_UPGRADE_REQ_RESPONSE_PHP_VERSION', "Minst Php-version {0} krävs för att kunna köra Jaws, därför måste du uppgradera din Php-version.");
define('_SV_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "Mapparna listade nedan som {0} är antingen ej läsbara eller skrivbara, vänligen ändra rättigheterna.");
define('_SV_UPGRADE_REQ_RESPONSE_EXTENSION', "Tillägget {0} är nödvändigt för att kunna använda Jaws.");
define('_SV_UPGRADE_DB_INFO', "Nu måste du ordna databasen, som ska användas för att lagra informationen för senare visning.");
define('_SV_UPGRADE_DB_HOST', "Värdnamn");
define('_SV_UPGRADE_DB_HOST_INFO', "Om du inte vet detta är det bäst att låta det vara {0}.");
define('_SV_UPGRADE_DB_DRIVER', "Version");
define('_SV_UPGRADE_DB_USER', "Användarnamn");
define('_SV_UPGRADE_DB_PASS', "Lösenord");
define('_SV_UPGRADE_DB_IS_ADMIN', "Är användaren Admin för databasen?");
define('_SV_UPGRADE_DB_NAME', "Databasnamn");
define('_SV_UPGRADE_DB_PATH', "Sökväg till databas");
define('_SV_UPGRADE_DB_PATH_INFO', "Fyll endast i detta fält om du vill ändra sökväg till databasen i SQLite, Interbase eller Firebird.");
define('_SV_UPGRADE_DB_PORT', "Databasport");
define('_SV_UPGRADE_DB_PORT_INFO', "Fyll endast i detta om databasen körs på någon annan port den vanliga. Om du inte vet vilken port databasen körs på är det <strong>bäst</strong> att låta detta fält vara tomt.");
define('_SV_UPGRADE_DB_PREFIX', "Tabellprefix");
define('_SV_UPGRADE_DB_PREFIX_INFO', "Något som står före tabellnamnen så att fler än en Jawssajt kan köras från samma databas, t.ex <strong>blogg_</strong>");
define('_SV_UPGRADE_DB_RESPONSE_PATH', "Angiven sökväg till databas finns inte");
define('_SV_UPGRADE_DB_RESPONSE_PORT', "Porten kan bara ha ett numeriskt värde");
define('_SV_UPGRADE_DB_RESPONSE_INCOMPLETE', "Alla fält utom port och tabellprefix måste fyllas i.");
define('_SV_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "Kunde ej få kontakt med databasen, kontrollera alla detaljer och försök igen.");
define('_SV_UPGRADE_REPORT_INFO', "Jämför installerad Jaws-version med nuvarande {0}");
define('_SV_UPGRADE_REPORT_NOTICE', "Nedan ser du vilka versioner som uppgraderingssystemet kan fixa. Du kör kanske en väldigt gammal version, så vi tar hand om resten.");
define('_SV_UPGRADE_REPORT_NEED', "Måste uppgraderas");
define('_SV_UPGRADE_REPORT_NO_NEED', "Behöver ej uppgraderas");
define('_SV_UPGRADE_REPORT_NO_NEED_CURRENT', "Behöver ej uppgraderas (är nuvarande)");
define('_SV_UPGRADE_REPORT_MESSAGE', "Om uppgraderingen upptäcker att din installation av Jaws är gammal kommer den uppdateras, om inte så stängs uppgraderingen av.");
define('_SV_UPGRADE_VER_INFO', "Uppgraderar från {0} till {1}");
define('_SV_UPGRADE_VER_NOTES', "<strong>OBS:</strong> När uppgraderingen är klar kan olika moduler (som Blogg, Phoo, etc) behöva uppgraderas. Detta kan du göra i kontrollpanelen.");
define('_SV_UPGRADE_VER_RESPONSE_GADGET_FAILED', "ett problem med att installera kärnmodulen uppstod här: {0}");
define('_SV_UPGRADE_CONFIG_INFO', "Nu ska du spara konfigurationsfilen.");
define('_SV_UPGRADE_CONFIG_SOLUTION', "Du kan göra detta på två sätt");
define('_SV_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Gör mappen <strong>{0}</strong> skrivbar och klicka på nästa, så kan installationen själv skapa konfigurationsfilen.");
define('_SV_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Kopiera boxens innehåll till en fil som ska sparas som <strong>{0}</strong>");
define('_SV_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "Ett fel uppstod när konfigurationsfilen skulle skapas.");
define('_SV_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "Du måste göra config-mappen skrivbar eller skapa {0} själv.");
define('_SV_UPGRADE_FINISH_INFO', "Nu är du klar med att ställa in sajten!");
define('_SV_UPGRADE_FINISH_CHOICES', "Nu har du två val. Antingen <a href=\"{0}\">kollar du in sajten</a> eller så loggar du in på <a href=\"{1}\">kontrollpanelen</a>.");
define('_SV_UPGRADE_FINISH_MOVE_LOG', "OBS: Om du aktiverade loggning i början av installationen är det bäst att flytta/radera loggen.");
define('_SV_UPGRADE_FINISH_THANKS', "Tack för att du använder Jaws!");
