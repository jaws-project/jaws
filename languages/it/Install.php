<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Alessio Calafiore <lex@iokoi.com>"
 * "Language-Team: IT"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_IT_INSTALL_INTRODUCTION', "Introduzione");
define('_IT_INSTALL_AUTHENTICATION', "Autenticazione");
define('_IT_INSTALL_REQUIREMENTS', "Requisiti");
define('_IT_INSTALL_DATABASE', "Database");
define('_IT_INSTALL_CREATEUSER', "Crea un Utente");
define('_IT_INSTALL_SETTINGS', "Impostazioni");
define('_IT_INSTALL_WRITECONFIG', "Salva Configurazione");
define('_IT_INSTALL_FINISHED', "Fine");
define('_IT_INSTALL_INTRO_WELCOME', "Benvenuto nell'installazione di Jaws.");
define('_IT_INSTALL_INTRO_INSTALLER', "Utilizzando questa procedura sarai guidato nella configurazione del tuo sito web. Assicurati di avere a disposizione le seguenti informazioni");
define('_IT_INSTALL_INTRO_DATABASE', "Dettagli del Database - hostname, username, password, nome del database.");
define('_IT_INSTALL_INTRO_FTP', "Un sistema per caricare i file, come un servizio FTP.");
define('_IT_INSTALL_INTRO_MAIL', "Le informazioni del tuo server Mail (hostname, username, password) se stai usando un server mail.");
define('_IT_INSTALL_INTRO_LOG', "Tieni traccia del processo di installazione (e degli errori) nel file ({0})");
define('_IT_INSTALL_INTRO_LOG_ERROR', "Nota: Se vuoi tenere traccia del processo di installazione (e degli errori) in un file, dovrai assicurarti che la directory ({0}) abbia i permessi in scrittura. Dopo averla impostata aggiorna questa pagina.");
define('_IT_INSTALL_AUTH_PATH_INFO', "Per verificare che tu sia il proprietario del sito, crea un file chiamato <strong>{0}</strong> nella tua directory di installazione di Jaws (<strong>{1}</strong>).");
define('_IT_INSTALL_AUTH_UPLOAD', "Puoi caricare il file nello stesso modo in cui hai caricato l'installazione di Jaws.");
define('_IT_INSTALL_AUTH_KEY_INFO', "Il file deve contenere SOLO il codice mostrato nel riquadro sottostante.");
define('_IT_INSTALL_AUTH_ENABLE_SECURITY', "Abilita l'installazione sicura (Powered by RSA)");
define('_IT_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "Errore nella generazione della chiave RSA. Prova di nuovo.");
define('_IT_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "Errore nella generazione della chiave RSA. Non sono disponibili estensioni per i calcoli matematici.");
define('_IT_INSTALL_AUTH_ERROR_KEY_FILE', "Il tuo file ({0}) non e' stato trovato, assicurati di averlo creato e che il server web sia in grado di leggerlo.");
define('_IT_INSTALL_AUTH_ERROR_KEY_MATCH', "La chiave trovata ({0}), non corrisponde con quella riportata di seguito, controlla di averla inserita correttamente.");
define('_IT_INSTALL_REQ_REQUIREMENT', "Requisiti");
define('_IT_INSTALL_REQ_OPTIONAL', "Opzionale, ma raccomandato");
define('_IT_INSTALL_REQ_RECOMMENDED', "Raccomandato");
define('_IT_INSTALL_REQ_DIRECTIVE', "Nome");
define('_IT_INSTALL_REQ_ACTUAL', "Attuale");
define('_IT_INSTALL_REQ_RESULT', "Risultato");
define('_IT_INSTALL_REQ_PHP_VERSION', "Versione PHP");
define('_IT_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_IT_INSTALL_REQ_DIRECTORY', "{0} directory");
define('_IT_INSTALL_REQ_EXTENSION', "{0} estensione");
define('_IT_INSTALL_REQ_FILE_UPLOAD', "Caricamento files");
define('_IT_INSTALL_REQ_SAFE_MODE', "Modalita' sicura");
define('_IT_INSTALL_REQ_READABLE', "Leggibile");
define('_IT_INSTALL_REQ_WRITABLE', "Scrivibile");
define('_IT_INSTALL_REQ_OK', "OK");
define('_IT_INSTALL_REQ_BAD', "NO");
define('_IT_INSTALL_REQ_OFF', "Off");
define('_IT_INSTALL_REQ_ON', "On");
define('_IT_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "La directory {0} non ha i permessi in lettura e/o scrittura, controllali.");
define('_IT_INSTALL_REQ_RESPONSE_PHP_VERSION', "La versione minima di PHP per installare Jaws e' {0}. E' necessario aggiornare la tua versione di PHP.");
define('_IT_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "Le directory elencate di seguito {0} non hanno i permessi necessari in lettura e/o scrittura.");
define('_IT_INSTALL_REQ_RESPONSE_EXTENSION', "L'estensione {0} e' necessaria per utilizzare Jaws.");
define('_IT_INSTALL_DB_INFO', "Adesso devi impostare il tuo database, necessario per immagazzinare le informazioni del tuo sito.");
define('_IT_INSTALL_DB_NOTICE', "Il database del quale inserirai le informazioni deve essere gia' esistente.");
define('_IT_INSTALL_DB_HOST', "Hostname");
define('_IT_INSTALL_DB_HOST_INFO', "Se non conosci questa informazione, e' meglio lasciarla come {0}.");
define('_IT_INSTALL_DB_DRIVER', "Driver");
define('_IT_INSTALL_DB_USER', "Username");
define('_IT_INSTALL_DB_PASS', "Password");
define('_IT_INSTALL_DB_IS_ADMIN', "E' Amministratore del DB?");
define('_IT_INSTALL_DB_NAME', "Nome del DataBase");
define('_IT_INSTALL_DB_PATH', "Percorso del Database");
define('_IT_INSTALL_DB_PATH_INFO', "Compila questo campo solo se vuoi cambiare il tuo percorso del database in SQLite, Interbase e Firebird driver.");
define('_IT_INSTALL_DB_PORT', "Porta del DataBase");
define('_IT_INSTALL_DB_PORT_INFO', "Compila questo campo se il tuo database opera su una porta diversa da quella predefinita.<br />Se <strong>non hai idea</strong> di quale porta e' utilizzata dal database e' molto probabile che sia operativo sulla porta predefinita, quindi ti <strong>raccomandiamo</strong> di lasciare in bianco questo campo.");
define('_IT_INSTALL_DB_PREFIX', "Prefisso della tabella");
define('_IT_INSTALL_DB_PREFIX_INFO', "Testo che verra' messo davanti al nome delle tabelle, in modo che potrai far girare piu' siti Jaws sul solito database. Per esempio <strong>blog_</strong>");
define('_IT_INSTALL_DB_RESPONSE_PATH', "Il percorso del database non esiste");
define('_IT_INSTALL_DB_RESPONSE_PORT', "La porta puo' essere solo un valore numerico");
define('_IT_INSTALL_DB_RESPONSE_INCOMPLETE', "Devi compilare tutti i campi, fatta eccezione della Porta del DataBase e del Prefisso della tabella.");
define('_IT_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Si e' verificato un errore nella connessione al database, controlla i dati inseriti e prova di nuovo.");
define('_IT_INSTALL_DB_RESPONSE_GADGET_INSTALL', "Si e' verificato un problema nell'installazione del core gadget {0}");
define('_IT_INSTALL_DB_RESPONSE_SETTINGS', "Si e' verificato un problema nell'impostazione del database.");
define('_IT_INSTALL_USER_INFO', "Adesso puoi crare un account per il tuo uso personale.");
define('_IT_INSTALL_USER_NOTICE', "Ricorda di non scegliere una password facile da indovinare, in quanto qualsiasi persona con la tua password ha il controllo totale del sito.");
define('_IT_INSTALL_USER_USER', "Nome Utente");
define('_IT_INSTALL_USER_USER_INFO', "Il tuo nome di accesso, sarà mostrato nei tuoi post.");
define('_IT_INSTALL_USER_PASS', "Password");
define('_IT_INSTALL_USER_REPEAT', "Ripeti");
define('_IT_INSTALL_USER_REPEAT_INFO', "Ripeti la tua password, per essere sicuro che sia corretta.");
define('_IT_INSTALL_USER_NAME', "Nome");
define('_IT_INSTALL_USER_NAME_INFO', "Il tuo vero nome.");
define('_IT_INSTALL_USER_EMAIL', "Indirizzo e-mail");
define('_IT_INSTALL_USER_RESPONSE_PASS_MISMATCH', "I campi Password e Ripeti non corrispondono, prova di nuovo.");
define('_IT_INSTALL_USER_RESPONSE_INCOMPLETE', "Devi compilare i campi Nome Utente, Password, e Ripeti.");
define('_IT_INSTALL_USER_RESPONSE_CREATE_FAILED', "Si e' verificato un errore nella creazione del tuo utente.");
define('_IT_INSTALL_SETTINGS_INFO', "Adesso puoi impostare il tuo sito. Puoi cambiare tutto in un secondo momento, accedendo al Pannello di Controllo e selezionando le Impostazioni.");
define('_IT_INSTALL_SETTINGS_SITE_NAME', "Nome del Sito");
define('_IT_INSTALL_SETTINGS_SITE_NAME_INFO', "Nome da mostrare per il tuo sito.");
define('_IT_INSTALL_SETTINGS_SLOGAN', "Descrizione");
define('_IT_INSTALL_SETTINGS_SLOGAN_INFO', "Una descrizione del tuo sito.");
define('_IT_INSTALL_SETTINGS_DEFAULT_GADGET', "Gadget Predefinito");
define('_IT_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "Il gadget da mostrare quando qualcuno visita la pagina principale.");
define('_IT_INSTALL_SETTINGS_SITE_LANGUAGE', "Lingua del Sito");
define('_IT_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "La lingua principale nella quale mostrare il sito.");
define('_IT_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Devi compilare il campo Nome del Sito.");
define('_IT_INSTALL_CONFIG_INFO', "Adesso devi salvare il tuo file di configurazione.");
define('_IT_INSTALL_CONFIG_SOLUTION', "Puoi farlo in due modi");
define('_IT_INSTALL_CONFIG_SOLUTION_PERMISSION', "Imposta <strong>{0}</strong> con i permessi di scrittura e premi Prossimo. Questo permettera' al processo di installazione di salvare il file di configurazione autonomamente.");
define('_IT_INSTALL_CONFIG_SOLUTION_UPLOAD', "Copia ed incolla il contenuto del riquadro sottostante nel file e salvalo come <strong>{0}</strong>");
define('_IT_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Si e' verificato un errore sconosciuto durante la scrittura del file di configurazione.");
define('_IT_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "Devi impostare i permessi di scrittura alla directory di configurazione oppure creare {0} a mano.");
define('_IT_INSTALL_FINISH_INFO', "Hai impostato il tuo sito web!");
define('_IT_INSTALL_FINISH_CHOICES', "Adesso hai due scelte, puoi <a href=\"{0}\">vedere il tuo sito</a> o <a href=\"{1}\">accedere al Pannello di Controllo</a>.");
define('_IT_INSTALL_FINISH_MOVE_LOG', "Nota: Se al primo passo hai scelto l'opzione per tenere traccia dell'installazione, ti consigliamo di salvare il file di log e spostarlo/rimuoverlo");
define('_IT_INSTALL_FINISH_THANKS', "Grazie per utilizzare Jaws!");
