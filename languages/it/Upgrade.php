<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: Alessio Calafiore <lex@iokoi.com>"
 * "Language-Team: IT"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_IT_UPGRADE_INTRODUCTION', "Introduzione");
define('_IT_UPGRADE_AUTHENTICATION', "Autenticazione");
define('_IT_UPGRADE_REQUIREMENTS', "Requisiti");
define('_IT_UPGRADE_DATABASE', "Database");
define('_IT_UPGRADE_REPORT', "Rapporto");
define('_IT_UPGRADE_VER_TO_VER', "Da {0} a {1}");
define('_IT_UPGRADE_SETTINGS', "Impostazioni");
define('_IT_UPGRADE_WRITECONFIG', "Salva Configurazione");
define('_IT_UPGRADE_FINISHED', "Fine");
define('_IT_UPGRADE_INTRO_WELCOME', "Benvenuto nell'aggiornamento di Jaws.");
define('_IT_UPGRADE_INTRO_UPGRADER', "Utilizzando questa procedura potrai aggiornare una vecchia installazione ad una nuova. Assicurati di avere le seguenti informazioni");
define('_IT_UPGRADE_INTRO_DATABASE', "Dettagli del Database - hostname, username, password, nome del database.");
define('_IT_UPGRADE_INTRO_FTP', "Un sistema per caricare i file, come ad esempio l'FTP.");
define('_IT_UPGRADE_INTRO_LOG', "Tieni traccia del processo di aggiornamento (e degli errori) in un file ({0})");
define('_IT_UPGRADE_INTRO_LOG_ERROR', "Nota: Se vuoi tenere traccia del processo di installazione (e degli errori) in un file, dovrai assicurarti che la directory ({0}) abbia i permessi in scrittura. Dopo averla impostata aggiorna questa pagina.");
define('_IT_UPGRADE_AUTH_PATH_INFO', "Per verificare che tu sia il proprietario del sito, crea un file chiamato <strong>{0}</strong> nella tua directory di installazione di Jaws (<strong>{1}</strong>).");
define('_IT_UPGRADE_AUTH_UPLOAD', "Puoi caricare il file nello stesso modo in cui hai caricato l'installazione di Jaws.");
define('_IT_UPGRADE_AUTH_KEY_INFO', "Il file deve contenere SOLO il codice mostrato nel riquadro sottostante.");
define('_IT_UPGRADE_AUTH_ENABLE_SECURITY', "Abilita l'installazione sicura (Powered by RSA)");
define('_IT_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "Errore nella generazione della chiave RSA. Prova di nuovo.");
define('_IT_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "Errore nella generazione della chiave RSA. Non sono disponibili estensioni per i calcoli matematici.");
define('_IT_UPGRADE_AUTH_ERROR_KEY_FILE', "Il tuo file ({0}) non e' stato trovato, assicurati di averlo creato e che il server web sia in grado di leggerlo.");
define('_IT_UPGRADE_AUTH_ERROR_KEY_MATCH', "La chiave trovata ({0}), non corrisponde con quella riportata di seguito, controlla di averla inserita correttamente.");
define('_IT_UPGRADE_REQ_REQUIREMENT', "Requisiti");
define('_IT_UPGRADE_REQ_OPTIONAL', "Opzionale, ma raccomandato");
define('_IT_UPGRADE_REQ_RECOMMENDED', "Raccomandato");
define('_IT_UPGRADE_REQ_DIRECTIVE', "Nome");
define('_IT_UPGRADE_REQ_ACTUAL', "Atuale");
define('_IT_UPGRADE_REQ_RESULT', "Risultato");
define('_IT_UPGRADE_REQ_PHP_VERSION', "Versione PHP");
define('_IT_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_IT_UPGRADE_REQ_DIRECTORY', "{0} directory");
define('_IT_UPGRADE_REQ_EXTENSION', "{0} estensione");
define('_IT_UPGRADE_REQ_FILE_UPLOAD', "Caricamento files");
define('_IT_UPGRADE_REQ_SAFE_MODE', "Modalità sicura");
define('_IT_UPGRADE_REQ_READABLE', "Leggibile");
define('_IT_UPGRADE_REQ_WRITABLE', "Scrivibile");
define('_IT_UPGRADE_REQ_OK', "OK");
define('_IT_UPGRADE_REQ_BAD', "NO");
define('_IT_UPGRADE_REQ_OFF', "Off");
define('_IT_UPGRADE_REQ_ON', "On");
define('_IT_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "La directory {0} non ha i permessi in lettura e/o scrittura, controllali.");
define('_IT_UPGRADE_REQ_RESPONSE_PHP_VERSION', "La versione minima di PHP per installare Jaws e' {0}. E' necessario aggiornare la tua versione di PHP.");
define('_IT_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "Le directory elencate di seguito {0} non hanno i permessi necessari in lettura e/o scrittura, controllale.");
define('_IT_UPGRADE_REQ_RESPONSE_EXTENSION', "L'estensione {0} e' necessaria per utilizzare Jaws.");
define('_IT_UPGRADE_DB_INFO', "Adesso devi impostare il tuo database corrente per poterlo aggiornare.");
define('_IT_UPGRADE_DB_HOST', "Hostname");
define('_IT_UPGRADE_DB_HOST_INFO', "Se non conosci questa informazione, e' meglio lasciarla come {0}.");
define('_IT_UPGRADE_DB_DRIVER', "Driver");
define('_IT_UPGRADE_DB_USER', "Username");
define('_IT_UPGRADE_DB_PASS', "Password");
define('_IT_UPGRADE_DB_IS_ADMIN', "E' Amministratore del DB?");
define('_IT_UPGRADE_DB_NAME', "Nome del DataBase");
define('_IT_UPGRADE_DB_PATH', "Percorso del Database");
define('_IT_UPGRADE_DB_PATH_INFO', "Compila questo campo solo se vuoi cambiare il tuo percorso del database in SQLite, Interbase e Firebird driver.");
define('_IT_UPGRADE_DB_PORT', "Porta del DataBase");
define('_IT_UPGRADE_DB_PORT_INFO', "Compila questo campo se il tuo database opera su una porta diversa da quella predefinita.<br />Se <strong>non hai idea</strong> di quale porta e' utilizzata dal database e' molto probabile che sia operativo sulla porta predefinita, quindi ti <strong>raccomandiamo</strong> di lasciare in bianco questo campo.");
define('_IT_UPGRADE_DB_PREFIX', "Prefisso della tabella");
define('_IT_UPGRADE_DB_PREFIX_INFO', "Testo che verra' messo davanti al nome delle tabelle, in modo che potrai far girare piu' siti Jaws sul solito database. Per esempio <strong>blog_</strong>");
define('_IT_UPGRADE_DB_RESPONSE_PATH', "Il percorso del database non esiste");
define('_IT_UPGRADE_DB_RESPONSE_PORT', "La porta puo' essere solo un valore numerico");
define('_IT_UPGRADE_DB_RESPONSE_INCOMPLETE', "Devi compilare tutti i campi, fatta eccezione della Porta del DataBase e del Prefisso della tabella.");
define('_IT_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "Si e' verificato un errore nella connessione al database, controlla i dati inseriti e prova di nuovo.");
define('_IT_UPGRADE_REPORT_INFO', "Comparazione della tua versione di Jaws installata con la corrente {0}");
define('_IT_UPGRADE_REPORT_NOTICE', "Di seguito troverai le versioni che questo processo di aggiornamento può gestire. Forse stai utilizzando una versione molto vecchia, il processo di aggiornamento si prender� cura di tutto.");
define('_IT_UPGRADE_REPORT_NEED', "Richiede aggiornamento");
define('_IT_UPGRADE_REPORT_NO_NEED', "Non richiede aggiornamento");
define('_IT_UPGRADE_REPORT_NO_NEED_CURRENT', "Non richiede aggiornamento (è la versione corrente)");
define('_IT_UPGRADE_REPORT_MESSAGE', "Se l'aggiornamento verificherà che la tua versione di Jaws è vecchia, questa sarà aggiornata. Altrimenti il processo terminerà.");
define('_IT_UPGRADE_VER_INFO', "Aggiornamento dalla {0} alla {1}");
define('_IT_UPGRADE_VER_NOTES', "<strong>Nota:</strong> Una volta finito l'aggiornamento della tua versione di Jaws, altri gadgets (come Blog, Phoo, etc) richiederenno un aggiornamento. Puoi compiere questa azione dal Pannello di Controllo.");
define('_IT_UPGRADE_VER_RESPONSE_GADGET_FAILED', "si è verificato un problema nell'instalazione del core gadget {0}");
define('_IT_UPGRADE_CONFIG_INFO', "Adesso devi salvare il tuo file di configurazione.");
define('_IT_UPGRADE_CONFIG_SOLUTION', "Puoi fare questo in due modi");
define('_IT_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Imposta <strong>{0}</strong> con i permessi di scrittura e premi Prossimo. Questo permettera' al processo di installazione di salvare il file di configurazione autonomamente.");
define('_IT_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Copia ed incolla il contenuto del riquadro sottostante nel file e salvalo come <strong>{0}</strong>");
define('_IT_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "Si e' verificato un errore sconosciuto durante la scrittura del file di configurazione.");
define('_IT_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "Devi impostare i permessi di scrittura alla directory di configurazione oppure creare {0} a mano.");
define('_IT_UPGRADE_FINISH_INFO', "Hai impostato il tuo sito web!");
define('_IT_UPGRADE_FINISH_CHOICES', "Adesso hai due scelte, puoi <a href=\"{0}\">vedere il tuo sito</a> o <a href=\"{1}\">accedere al Pannello di Controllo</a>.");
define('_IT_UPGRADE_FINISH_MOVE_LOG', "Nota: Se al primo passo hai scelto l'opzione per tenere traccia dell'installazione, ti consigliamo di salvare il file di log e spostarlo/rimuoverlo");
define('_IT_UPGRADE_FINISH_THANKS', "Grazie per utilizzare Jaws!");
