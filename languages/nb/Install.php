<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Karl-Gustav Freding <gusse500@gusse.no>"
 * "Language-Team: NB"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_NB_INSTALL_INTRODUCTION', "Introduksjon");
define('_NB_INSTALL_AUTHENTICATION', "Godkjenning");
define('_NB_INSTALL_REQUIREMENTS', "Krav");
define('_NB_INSTALL_DATABASE', "Database");
define('_NB_INSTALL_CREATEUSER', "Opprett en bruker");
define('_NB_INSTALL_SETTINGS', "Innstillinger");
define('_NB_INSTALL_WRITECONFIG', "Lagre konfigurasjon");
define('_NB_INSTALL_FINISHED', "Ferdig");
define('_NB_INSTALL_INTRO_WELCOME', "Velkommen til installeringen av Jaws");
define('_NB_INSTALL_INTRO_INSTALLER', "Ved å bruke installeringen vil du bli guidet gjennom oppsettet av ditt nettsted, vær sikker på at du har følgende tilgjengelig.");
define('_NB_INSTALL_INTRO_DATABASE', "Detaljer for databasen - vertsnavn, brukernavn, passord, databasenavn.");
define('_NB_INSTALL_INTRO_FTP', "En måte å laste opp filer, sannsynligvis FTP.");
define('_NB_INSTALL_INTRO_MAIL', "Informasjon for E-postserveren (vertsnavn, brukernavn, passord) hvis du bruker en mailserver.");
define('_NB_INSTALL_INTRO_LOG', "Logg prosessen (og feil) for installasjonen til en fil ({0})");
define('_NB_INSTALL_INTRO_LOG_ERROR', "Merk: Hvis du vil logge prosessen (og feil) for installasjonen til en fil trenger du å sette skrivetilgang  til mappen ({0}) og etterpå oppdatere denne siden med nettleseren.");
define('_NB_INSTALL_AUTH_PATH_INFO', "For å være sikker på at du er eieren av denne siden, lag en fil kalt <strong>{0}</strong> i din Jaws installasjonsmappe (<strong>{0}</strong>)");
define('_NB_INSTALL_AUTH_UPLOAD', "Du kan laste opp filen på samme måte spm du lastet opp Jaws.");
define('_NB_INSTALL_AUTH_KEY_INFO', "Filen må inneholde koden som vises i boksen under, ingenting annet.");
define('_NB_INSTALL_AUTH_ENABLE_SECURITY', "Aktiver sikker installasjon (Utgitt av RSA)");
define('_NB_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "Feil i generingen av RSA nøkkel, prøv igjen.");
define('_NB_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "Feil i generingen av RSA nøkkel. Ingen tilgjengelig matteutvidelse.");
define('_NB_INSTALL_AUTH_ERROR_KEY_FILE', "Din nøkkelfil ({0}) ble ikke funnet, vær sikker på at den er laget og at netttjeneren kan lese den.");
define('_NB_INSTALL_AUTH_ERROR_KEY_MATCH', "Nøkkelfilen ({0}) som ble funnet, stemmer ikke med koden under, sjekk at du skrev nøkkelen rett.");
define('_NB_INSTALL_REQ_REQUIREMENT', "Krav");
define('_NB_INSTALL_REQ_OPTIONAL', "Valgfritt men anbefalt");
define('_NB_INSTALL_REQ_RECOMMENDED', "Anbefalt");
define('_NB_INSTALL_REQ_DIRECTIVE', "Anvisning");
define('_NB_INSTALL_REQ_ACTUAL', "Aktuell");
define('_NB_INSTALL_REQ_RESULT', "Resultat");
define('_NB_INSTALL_REQ_PHP_VERSION', "PHP versjon");
define('_NB_INSTALL_REQ_GREATER_THAN', ">={0}");
define('_NB_INSTALL_REQ_DIRECTORY', "Mappe {0}");
define('_NB_INSTALL_REQ_EXTENSION', "Utvidelse {0}");
define('_NB_INSTALL_REQ_FILE_UPLOAD', "Filen lastes opp");
define('_NB_INSTALL_REQ_SAFE_MODE', "Sikker modus");
define('_NB_INSTALL_REQ_READABLE', "Lesbar");
define('_NB_INSTALL_REQ_WRITABLE', "Skrivbar");
define('_NB_INSTALL_REQ_OK', "I orden");
define('_NB_INSTALL_REQ_BAD', "Ikke i orden");
define('_NB_INSTALL_REQ_OFF', "Av");
define('_NB_INSTALL_REQ_ON', "På");
define('_NB_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "Mappen {0} er ikke lesbar eller skrivbar, ordne rettighetene.");
define('_NB_INSTALL_REQ_RESPONSE_PHP_VERSION', "Minimum PHP versjon for å installere Jaws er {0}, du må oppgradere din PHP versjon.");
define('_NB_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "Mappene listet nedenfor som {0} er ikke lesbare eller skrivbare, ordne rettighetene.");
define('_NB_INSTALL_REQ_RESPONSE_EXTENSION', "Utvidelsen {0} behøves for å bruke Jaws.");
define('_NB_INSTALL_DB_INFO', "Du må nå sette opp databasen, som brukes for å lagre informasjon som vises senere.");
define('_NB_INSTALL_DB_NOTICE', "Databasen som du gir detaljer om må være opprettet for at denne prosessen skal virke.");
define('_NB_INSTALL_DB_HOST', "Vertsnavn");
define('_NB_INSTALL_DB_HOST_INFO', "Hvis du ikke vet dette, er det sannsynligvis sikkert å la det være som {0}.");
define('_NB_INSTALL_DB_DRIVER', "Driver");
define('_NB_INSTALL_DB_USER', "Brukernavn");
define('_NB_INSTALL_DB_PASS', "Passord");
define('_NB_INSTALL_DB_IS_ADMIN', "Er DB administrator");
define('_NB_INSTALL_DB_NAME', "Navn på database");
define('_NB_INSTALL_DB_PATH', "Sti til database");
define('_NB_INSTALL_DB_PATH_INFO', "Bare forandre dette feltet hvis du vil forandre stien i SQLite, Interbase og Firebird.");
define('_NB_INSTALL_DB_PORT', "Port for databasen");
define('_NB_INSTALL_DB_PORT_INFO', "Fyll bare ut dette feltet hvis databasen bruker en annen port enn standard.\n<strong>Vet du ikke portnummeret</strong> er det sannsynlig at den bruker standardport og vi må <strong>råde</strong> deg til å la dette feltet være uforandret.");
define('_NB_INSTALL_DB_PREFIX', "Forstavelse for tabellene");
define('_NB_INSTALL_DB_PREFIX_INFO', "Dette vil plasseres foran tabellnavnet så du kan bruke flere Jawsinstallasjoner i samme database. F.eks. <strong>blogg_</strong>");
define('_NB_INSTALL_DB_RESPONSE_PATH', "Stien til databasen eksisterer ikke");
define('_NB_INSTALL_DB_RESPONSE_PORT', "Porten kan bare ha nummerverdi");
define('_NB_INSTALL_DB_RESPONSE_INCOMPLETE', "Du må fylle inn alle feltene utenom stien til databasen, forstavelse til tabellene og portnummeret.");
define('_NB_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Kunne ikke koble til databasen, sjekk detaljene og prøv igjen");
define('_NB_INSTALL_DB_RESPONSE_GADGET_INSTALL', "Kunne ikke installere kjernemodul {0}");
define('_NB_INSTALL_DB_RESPONSE_SETTINGS', "Kunne ikke sette opp databasen.");
define('_NB_INSTALL_USER_INFO', "Du kan nå opprette en brukerkonto til deg selv.");
define('_NB_INSTALL_USER_NOTICE', "Husk å velge et passord som ikke er lett å gjette siden hvem som helst med ditt passord har full kontroll over ditt nettsted.");
define('_NB_INSTALL_USER_USER', "Brukernavn");
define('_NB_INSTALL_USER_USER_INFO', "Ditt navn som vil vises i alle elementer du poster.");
define('_NB_INSTALL_USER_PASS', "Passord");
define('_NB_INSTALL_USER_REPEAT', "Gjenta");
define('_NB_INSTALL_USER_REPEAT_INFO', "Gjenta passorden for å være sikker på at det ikke blir feil.");
define('_NB_INSTALL_USER_NAME', "Navn");
define('_NB_INSTALL_USER_NAME_INFO', "Ditt riktige navn.");
define('_NB_INSTALL_USER_EMAIL', "E-postadresse");
define('_NB_INSTALL_USER_RESPONSE_PASS_MISMATCH', "Passordet og gjentakelsen er ikke lik, prøv igjen.");
define('_NB_INSTALL_USER_RESPONSE_INCOMPLETE', "Du må gjøre ferdig brukernavn, passord og gjentakelsen.");
define('_NB_INSTALL_USER_RESPONSE_CREATE_FAILED', "Kunne ikke opprette brukeren din.");
define('_NB_INSTALL_SETTINGS_INFO', "Du kan nå sette standard innstillinger for siden din. Du kan forandre dette senere ved å logge inn i kontrollpanelet og velge oppsett.");
define('_NB_INSTALL_SETTINGS_SITE_NAME', "Navn på siden");
define('_NB_INSTALL_SETTINGS_SITE_NAME_INFO', "Navn som skal vises for siden din.");
define('_NB_INSTALL_SETTINGS_SLOGAN', "Beskrivelse");
define('_NB_INSTALL_SETTINGS_SLOGAN_INFO', "En lengre beskrivelse av siden");
define('_NB_INSTALL_SETTINGS_DEFAULT_GADGET', "Standardmodul");
define('_NB_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "Modulen som skal vises når noen besøker siden.");
define('_NB_INSTALL_SETTINGS_SITE_LANGUAGE', "Språk for siden");
define('_NB_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "Hovedspråk for visning av siden din.");
define('_NB_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Du må fylle inn sidenavn.");
define('_NB_INSTALL_CONFIG_INFO', "Du må lagre konfigurasjonsfilen.");
define('_NB_INSTALL_CONFIG_SOLUTION', "Dette kan gjøres på to måter");
define('_NB_INSTALL_CONFIG_SOLUTION_PERMISSION', "Gjør <strong>{0}</strong> skrivbar og trykk neste. Installeringen lagrer konfigurasjonen.");
define('_NB_INSTALL_CONFIG_SOLUTION_UPLOAD', "Kopier og lim inn innholdet i boksen under til en fil og lagre den som <strong>{0}</strong>");
define('_NB_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Det var en ukjent feil med å skrive konfigurasjonsfilen.");
define('_NB_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "Du må enten gjøre mappen config skrivbar eller lage {0}.");
define('_NB_INSTALL_FINISH_INFO', "Du er nå ferdig med oppsettet av ditt nettsted!");
define('_NB_INSTALL_FINISH_CHOICES', "Du har nå to valg, du kan enten <a href=\"{0}\">se nettstedet ditt</a> eller <a href=\"{1}\">logge inn</a> til kontrollpanelet.");
define('_NB_INSTALL_FINISH_MOVE_LOG', "Merk: Hvis du slo på loggingen i første steg foreslår vi du lagrer den og flytter/sletter den");
define('_NB_INSTALL_FINISH_THANKS', "Takk for at du bruker Jaws!");
