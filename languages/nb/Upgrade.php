<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: Karl-Gustav Freding <gusse500@gusse.no>"
 * "Language-Team: NB"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_NB_UPGRADE_INTRODUCTION', "Introduksjon");
define('_NB_UPGRADE_AUTHENTICATION', "Godkjenning");
define('_NB_UPGRADE_REQUIREMENTS', "Krav");
define('_NB_UPGRADE_DATABASE', "Database");
define('_NB_UPGRADE_REPORT', "Rapport");
define('_NB_UPGRADE_VER_TO_VER', "{0} til {1}");
define('_NB_UPGRADE_SETTINGS', "Innstillinger");
define('_NB_UPGRADE_WRITECONFIG', "Lagre konfigurasjon");
define('_NB_UPGRADE_FINISHED', "Ferdig");
define('_NB_UPGRADE_INTRO_WELCOME', "Velkommen til Jaws oppgradering.");
define('_NB_UPGRADE_INTRO_UPGRADER', "Ved å bruke dette verktøyet kan du oppgradere en gammel installasjon til gjeldene utgivelse. Vær sikker på at du har følgende tilgjengelig.");
define('_NB_UPGRADE_INTRO_DATABASE', "Detaljer for databasen - vertsnavn, brukernavn, passord, databasenavn.");
define('_NB_UPGRADE_INTRO_FTP', "En måte å laste opp filer, sannsynligvis FTP.");
define('_NB_UPGRADE_INTRO_LOG', "Logg oppgraderingsprosessen (og feil) til en fil ({0})");
define('_NB_UPGRADE_INTRO_LOG_ERROR', "Merk: hvis du vil logge oppgraderingsprosessen (og feil) for installeringen til en fil, må det være skrivetilgang til mappen ({0}). Etterpå må du oppdatere nettleservinduet.");
define('_NB_UPGRADE_AUTH_PATH_INFO', "For å være sikker på at du er eieren av dette nettstedet må du lage en fil som du kaller <strong>{0}</strong> i din Jaws oppgraderingsmappe (<strong>{1}</strong>).");
define('_NB_UPGRADE_AUTH_UPLOAD', "Du kan laste opp filen på samme måte du lastet opp Jaws.");
define('_NB_UPGRADE_AUTH_KEY_INFO', "Filen må inneholde koden som vises i boksen under. Ingenting annet.");
define('_NB_UPGRADE_AUTH_ENABLE_SECURITY', "Slå på sikker oppgradering (Utgitt av RSA)");
define('_NB_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "Feil i nøkkelgeneringen til RSA. Prøv igjen.");
define('_NB_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "Feil i nøkkelgeneringen til RSA. Ingen tilgjengelige matteutvidelser.");
define('_NB_UPGRADE_AUTH_ERROR_KEY_FILE', "Nøkkelfilen ({0}) ble ikke funnet. Vær sikker på at den er laget og at nettserveren kan lese den.");
define('_NB_UPGRADE_AUTH_ERROR_KEY_MATCH', "Nøkkelen ({0}) er ikke lik den under. Sjekk at du skrev nøkkelen korrekt.");
define('_NB_UPGRADE_REQ_REQUIREMENT', "Krav");
define('_NB_UPGRADE_REQ_OPTIONAL', "Valgfritt men anbefalt");
define('_NB_UPGRADE_REQ_RECOMMENDED', "Anbefalt");
define('_NB_UPGRADE_REQ_DIRECTIVE', "Anvisning");
define('_NB_UPGRADE_REQ_ACTUAL', "Aktuell");
define('_NB_UPGRADE_REQ_RESULT', "Resultat");
define('_NB_UPGRADE_REQ_PHP_VERSION', "PHP versjon");
define('_NB_UPGRADE_REQ_GREATER_THAN', ">={0}");
define('_NB_UPGRADE_REQ_DIRECTORY', "Mappe {0}");
define('_NB_UPGRADE_REQ_EXTENSION', "Utvidelse {0}");
define('_NB_UPGRADE_REQ_FILE_UPLOAD', "Filopplasting");
define('_NB_UPGRADE_REQ_SAFE_MODE', "Sikker modus");
define('_NB_UPGRADE_REQ_READABLE', "Lesbar");
define('_NB_UPGRADE_REQ_WRITABLE', "Skrivbar");
define('_NB_UPGRADE_REQ_OK', "I orden");
define('_NB_UPGRADE_REQ_BAD', "Ikke i orden");
define('_NB_UPGRADE_REQ_OFF', "Av");
define('_NB_UPGRADE_REQ_ON', "På");
define('_NB_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "Mappen {0} er ikke lesbar eller skrivbar. Ordne rettighetene.");
define('_NB_UPGRADE_REQ_RESPONSE_PHP_VERSION', "Minimum PHP versjon for å oppgradere Jaws er {0}. Du må oppgradere din PHP versjon.");
define('_NB_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "Mappene listet under som {0} er ikke lesbare eller skrivbare. Ordne rettighetene.");
define('_NB_UPGRADE_REQ_RESPONSE_EXTENSION', "{0} utvidelsen er nødvendig for å bruke Jaws.");
define('_NB_UPGRADE_DB_INFO', "Du må nå sette opp databasen, dette brukes for å hente informasjon om gjeldende database. Deretter oppgraderes den.");
define('_NB_UPGRADE_DB_HOST', "Vertsnavn");
define('_NB_UPGRADE_DB_HOST_INFO', "Hvis du ikke vet dette er det sannsynligvis trygt å la den være {0}.");
define('_NB_UPGRADE_DB_DRIVER', "Driver");
define('_NB_UPGRADE_DB_USER', "Brukernavn");
define('_NB_UPGRADE_DB_PASS', "Passord");
define('_NB_UPGRADE_DB_IS_ADMIN', "Er databaseadministrator");
define('_NB_UPGRADE_DB_NAME', "Databasenavn");
define('_NB_UPGRADE_DB_PATH', "Databasesti");
define('_NB_UPGRADE_DB_PATH_INFO', "Fyll dette feltet ut bare hvis du vil forandre databasestien i SQLite, Interbase og Firebird.");
define('_NB_UPGRADE_DB_PORT', "Databaseport");
define('_NB_UPGRADE_DB_PORT_INFO', "Fyll bare ut dette feltet hvis databasen bruker en annen port enn standard.\n<strong>Hvis du ikke vet hvilken port databasen bruker</strong> er det sannsynligvis standardporten som blir brukt og vi må <strong>råde</strong> deg til å la dette feltet være urørt.");
define('_NB_UPGRADE_DB_PREFIX', "Forstavelse for tabellene.");
define('_NB_UPGRADE_DB_PREFIX_INFO', "Tekst som plasseres foran tabellnavnet så du kan bruke flere Jawsinstallasjoner i samme database. F.eks. <strong>blogg_</strong>");
define('_NB_UPGRADE_DB_RESPONSE_PATH', "Stien til databasen finnes ikke");
define('_NB_UPGRADE_DB_RESPONSE_PORT', "Portnummeret kan bare ha tallverdi");
define('_NB_UPGRADE_DB_RESPONSE_INCOMPLETE', "Du må fylle inn alle feltene utenom databasesti, forstavelse for tabell og portnummer.");
define('_NB_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "Det oppstod problem med å koble til databasen. Sjekk detaljene og prøv igjen.");
define('_NB_UPGRADE_REPORT_INFO', "Sammenligner din Jawsversjon med den gjeldene utgivelsen {0}");
define('_NB_UPGRADE_REPORT_NOTICE', "Under vil du finne versjonene som oppgraderingsverktøyet kan oppgradere. Hvis du bruker en veldig gammel versjon,  vil vi ta oss av resten. ");
define('_NB_UPGRADE_REPORT_NEED', "Krever oppgradering");
define('_NB_UPGRADE_REPORT_NO_NEED', "Krever ikke oppgradering");
define('_NB_UPGRADE_REPORT_NO_NEED_CURRENT', "Krever ikke oppgradering (er gjeldende versjon)");
define('_NB_UPGRADE_REPORT_MESSAGE', "Hvis oppgraderingsverktøyet finner en gammel Jawsversjon vil det oppgrade det, hvis ikke vil den slutte.");
define('_NB_UPGRADE_VER_INFO', "Oppgradering fra {0} til {1} vil");
define('_NB_UPGRADE_VER_NOTES', "<strong>Merk:</strong> Når du er ferdig med oppgraderingen vil andre moduler trenge en oppgradering (som Blogg osv). Dette kan gjøres med å logge inn i kontrollpanelet.");
define('_NB_UPGRADE_VER_RESPONSE_GADGET_FAILED', "Det oppstod problem med installeringen av kjernemodul {0}");
define('_NB_UPGRADE_CONFIG_INFO', "Du må nå lagre konfigurasjonsfilen.");
define('_NB_UPGRADE_CONFIG_SOLUTION', "Dette kan gjøres på to måter");
define('_NB_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Gjør <strong>{0}</strong> skrivbar og trykk neste, dette gjør at installeringen lagrer konfigurasjonen.");
define('_NB_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Kopier og lim inn innholdet i boksen under til en fil og lagre den som <strong>{0}</strong>");
define('_NB_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "Kunne ikke skrive konfigurasjonsfilen.");
define('_NB_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "Du må gjøre mappen config skrivbar eller lage filen {0}.");
define('_NB_UPGRADE_FINISH_INFO', "Du er nå ferdig med å sette opp nettstedet ditt!");
define('_NB_UPGRADE_FINISH_CHOICES', "Du har nå to valg. <a href=\"{0}\">Vise nettstedet ditt</a> eller <a href=\"{1}\">logge inn til kontrollpanelet.</a>");
define('_NB_UPGRADE_FINISH_MOVE_LOG', "Merk: Slo du på logging foreslår vi at du flytter/sletter den.");
define('_NB_UPGRADE_FINISH_THANKS', "Takk for at du bruker Jaws!");
