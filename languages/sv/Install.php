<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Thomas Lilliesköld <thomas.lillieskold@gmail.com>"
 * "Language-Team: SV"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_SV_INSTALL_INTRODUCTION', "Introducering");
define('_SV_INSTALL_AUTHENTICATION', "Autentisering");
define('_SV_INSTALL_REQUIREMENTS', "Systemkrav");
define('_SV_INSTALL_DATABASE', "Databas");
define('_SV_INSTALL_CREATEUSER', "Skapa en användare");
define('_SV_INSTALL_SETTINGS', "Inställlningar\nInställningar");
define('_SV_INSTALL_WRITECONFIG', "Spara konfiguration");
define('_SV_INSTALL_FINISHED', "Klar");
define('_SV_INSTALL_INTRO_WELCOME', "Välkommen till Jaws installerare.");
define('_SV_INSTALL_INTRO_INSTALLER', "Under installationsprogrammet guidas du igenom webbsajtens inställningar, se till att du har följande tillgängligt");
define('_SV_INSTALL_INTRO_DATABASE', "Databasdetaljer - värdnamn, användarnamn, lösenord och databasnamn.");
define('_SV_INSTALL_INTRO_FTP', "Ett sätt att ladda upp filer, förmodligen FTP");
define('_SV_INSTALL_INTRO_MAIL', "Din mailservers information (värdnamn, användarnamn, lösenord) om du använder en mailserver.");
define('_SV_INSTALL_INTRO_LOG', "Logga installationen (och ev fel) och spara till fil ({0})");
define('_SV_INSTALL_INTRO_LOG_ERROR', "OBS: Om du vill logga installationen (och ev. fel) till en fil måste du först sätta skrivrättigheter på mappen {0} och därefter ladda om sidan i webbläsaren.");
define('_SV_INSTALL_AUTH_PATH_INFO', "För att säkerställa att du verkligen är sajtens ägare ska en fil med namn <strong>{0}</strong> skapas i Jaws installationsmapp (<strong>{1}</strong>).");
define('_SV_INSTALL_AUTH_UPLOAD', "Du kan ladda upp filen på samma sätt som du laddade upp Jaws.");
define('_SV_INSTALL_AUTH_KEY_INFO', "Filen ska innehålla koden nedan och inget annat.");
define('_SV_INSTALL_AUTH_ENABLE_SECURITY', "Aktivera säker installation (RSA-understödd).");
define('_SV_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "Fel i skapandetav RSA-nyckeln, vänligen försök igen.");
define('_SV_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "Fel i skapandet av RSA-nyckeln.(No available math extension)");
define('_SV_INSTALL_AUTH_ERROR_KEY_FILE', "Din nyckelfil ({0}) hittades ej, vänligen säkerställ att den finns och att webbservern kan läsa den.");
define('_SV_INSTALL_AUTH_ERROR_KEY_MATCH', "Den hittade nyckeln ({0}) matchar inte den nedan, vänligen kontrollera att du fyllt i nyckeln korrekt.");
define('_SV_INSTALL_REQ_REQUIREMENT', "Systemkrav.");
define('_SV_INSTALL_REQ_OPTIONAL', "Valfritt men rekommenderat");
define('_SV_INSTALL_REQ_RECOMMENDED', "Rekommenderat");
define('_SV_INSTALL_REQ_DIRECTIVE', "Direktiv");
define('_SV_INSTALL_REQ_ACTUAL', "Egentlig");
define('_SV_INSTALL_REQ_RESULT', "Resultat");
define('_SV_INSTALL_REQ_PHP_VERSION', "Php-version");
define('_SV_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_SV_INSTALL_REQ_DIRECTORY', "mappen {0}");
define('_SV_INSTALL_REQ_EXTENSION', "tillägget {0}");
define('_SV_INSTALL_REQ_FILE_UPLOAD', "Uppladdningar");
define('_SV_INSTALL_REQ_SAFE_MODE', "Safe mode");
define('_SV_INSTALL_REQ_READABLE', "Läsbar");
define('_SV_INSTALL_REQ_WRITABLE', "Skrivbar");
define('_SV_INSTALL_REQ_OK', "Ok");
define('_SV_INSTALL_REQ_BAD', "BAD");
define('_SV_INSTALL_REQ_OFF', "Av");
define('_SV_INSTALL_REQ_ON', "På");
define('_SV_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "Mappen {0} är antingen ej läsbar eller skrivbar, vänligen ändra rättigheterna.");
define('_SV_INSTALL_REQ_RESPONSE_PHP_VERSION', "Minst Php-version {0} krävs för att kunna köra Jaws, därför måste du uppgradera din Php-version.");
define('_SV_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "Mapparna listade nedan som {0} är antingen ej läsbara eller skrivbara, vänligen ändra rättigheterna.");
define('_SV_INSTALL_REQ_RESPONSE_EXTENSION', "Tillägget {0} är nödvändigt för att kunna använda Jaws.");
define('_SV_INSTALL_DB_INFO', "Nu måste du ordna databasen, som ska användas för att lagra informationen för senare visning.");
define('_SV_INSTALL_DB_NOTICE', "Databasen du fyllt i detaljer för måste existera för att detta skall fungera.");
define('_SV_INSTALL_DB_HOST', "Värdnamn");
define('_SV_INSTALL_DB_HOST_INFO', "Om du inte vet detta är det bäst att låta det vara {0}.");
define('_SV_INSTALL_DB_DRIVER', "Version");
define('_SV_INSTALL_DB_USER', "Användarnamn");
define('_SV_INSTALL_DB_PASS', "Lösenord");
define('_SV_INSTALL_DB_IS_ADMIN', "Är användaren Admin för databasen?");
define('_SV_INSTALL_DB_NAME', "Databasnamn");
define('_SV_INSTALL_DB_PATH', "Databasens sökväg");
define('_SV_INSTALL_DB_PATH_INFO', "Fyll endast i detta fält om du vill ändra databasens sökväg i SQLite, Interbase eller Firebird.");
define('_SV_INSTALL_DB_PORT', "Databasport");
define('_SV_INSTALL_DB_PORT_INFO', "Fyll endast i detta om databasen körs på någon annan port den vanliga. Om du inte vet vilken port databasen körs på är det <strong>bäst</strong> att låta detta fält vara tomt.");
define('_SV_INSTALL_DB_PREFIX', "Tabellprefix");
define('_SV_INSTALL_DB_PREFIX_INFO', "Något som står före tabellnamnen så att fler än en Jawssajt kan köras från samma databas, t.ex <strong>blogg_</strong>");
define('_SV_INSTALL_DB_RESPONSE_PATH', "Angiven sökväg till databas finns inte");
define('_SV_INSTALL_DB_RESPONSE_PORT', "Porten kan bara ha ett numeriskt värde");
define('_SV_INSTALL_DB_RESPONSE_INCOMPLETE', "Alla fält utom port och tabellprefix måste fyllas i.");
define('_SV_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Kunde ej få kontakt med databasen, kontrollera alla detaljer och försök igen.");
define('_SV_INSTALL_DB_RESPONSE_GADGET_INSTALL', "problem med att installera kärnmodulen här {0}");
define('_SV_INSTALL_DB_RESPONSE_SETTINGS', "Ett problem uppstod med upprättandet av databasen.");
define('_SV_INSTALL_USER_INFO', "Nu kan du skapa ett användarkonto åt dig själv.");
define('_SV_INSTALL_USER_NOTICE', "Tänk på att välja ett svårgissat lösenord. Om någon knäcker det har den personen kontroll över hela sajten.");
define('_SV_INSTALL_USER_USER', "Användarnamn");
define('_SV_INSTALL_USER_USER_INFO', "Ditt inloggningsnamn, som kommer att visas vid dina inlägg.");
define('_SV_INSTALL_USER_PASS', "Lösenord");
define('_SV_INSTALL_USER_REPEAT', "Upprepa");
define('_SV_INSTALL_USER_REPEAT_INFO', "Upprepa lösenordet för att minimera risken för stavfel.");
define('_SV_INSTALL_USER_NAME', "Namn");
define('_SV_INSTALL_USER_NAME_INFO', "Ditt riktiga namn.");
define('_SV_INSTALL_USER_EMAIL', "Epost-adress");
define('_SV_INSTALL_USER_RESPONSE_PASS_MISMATCH', "Lösenorden matchar inte, försök igen.");
define('_SV_INSTALL_USER_RESPONSE_INCOMPLETE', "Du måste fylla i användarnamn, lösenord och upprepa.");
define('_SV_INSTALL_USER_RESPONSE_CREATE_FAILED', "Ett problem uppstod då användaren skulle skapas.");
define('_SV_INSTALL_SETTINGS_INFO', "Nu kan du ställa in förhandsvalen för sajten. Du kan ändra detta senare genom att logga in på kontrollpanelen och välja Inställningar");
define('_SV_INSTALL_SETTINGS_SITE_NAME', "Sajtnamn");
define('_SV_INSTALL_SETTINGS_SITE_NAME_INFO', "Namnet som visas på sajten");
define('_SV_INSTALL_SETTINGS_SLOGAN', "Beskrivning");
define('_SV_INSTALL_SETTINGS_SLOGAN_INFO', "En längre beskrivning av sajten.");
define('_SV_INSTALL_SETTINGS_DEFAULT_GADGET', "Förhandsvald modul");
define('_SV_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "Modulen som visas när någon besöker sajten.");
define('_SV_INSTALL_SETTINGS_SITE_LANGUAGE', "Sajtens språk");
define('_SV_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "Huvudspråket för hela sajten.");
define('_SV_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Du måste fylla i Sajtnamn.");
define('_SV_INSTALL_CONFIG_INFO', "Nu ska du spara konfigurationsfilen.");
define('_SV_INSTALL_CONFIG_SOLUTION', "Du kan göra detta på två sätt.");
define('_SV_INSTALL_CONFIG_SOLUTION_PERMISSION', "Gör mappen <strong>{0}</strong> skrivbar och klicka på nästa, så kan installationen själv skapa konfigurationsfilen.");
define('_SV_INSTALL_CONFIG_SOLUTION_UPLOAD', "Kopiera boxens innehåll till en fil som ska sparas som <strong>{0}</strong>");
define('_SV_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Ett fel uppstod när konfigurationsfilen skulle skapas.");
define('_SV_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "Du måste göra config-mappen skrivbar eller skapa {0} själv.");
define('_SV_INSTALL_FINISH_INFO', "Nu är du klar med att ställa in sajten!");
define('_SV_INSTALL_FINISH_CHOICES', "Nu har du två val. Antingen <a href=\"{0}\">kollar du in sajten</a> eller så loggar du in på <a href=\"{1}\">kontrollpanelen</a>.");
define('_SV_INSTALL_FINISH_MOVE_LOG', "OBS: Om du aktiverade loggning i början av installationen är det bäst att flytta/radera loggen.");
define('_SV_INSTALL_FINISH_THANKS', "Tack för att du använder Jaws!");
