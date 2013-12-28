<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: Francesc D. Subirats <fsubirats@gmail.com>"
 * "Language-Team: CA"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_CA_UPGRADE_INTRODUCTION', "Introducció");
define('_CA_UPGRADE_AUTHENTICATION', "Identificació");
define('_CA_UPGRADE_REQUIREMENTS', "Requeriments");
define('_CA_UPGRADE_DATABASE', "Base de dades");
define('_CA_UPGRADE_REPORT', "Estudi");
define('_CA_UPGRADE_VER_TO_VER', "{0} a {1}");
define('_CA_UPGRADE_SETTINGS', "Configuració");
define('_CA_UPGRADE_WRITECONFIG', "Desa la configuració");
define('_CA_UPGRADE_FINISHED', "Finalitzat");
define('_CA_UPGRADE_INTRO_WELCOME', "Benvingut a l'actualitzador de Jaws");
define('_CA_UPGRADE_INTRO_UPGRADER', "En utilitzar l'actualitzador pot actualitzar la versió antiga per la més actual. Tan sols assegureu-vos que disposeu de les següents dades");
define('_CA_UPGRADE_INTRO_DATABASE', "Detalls de la base de dades - nom del host, usuari, contrasenya, nom de la base de dades.");
define('_CA_UPGRADE_INTRO_FTP', "Una forma de pujar fitxers, provablement, FTP.");
define('_CA_UPGRADE_INTRO_LOG', "Desa el registre dels processos (i errors) en un arxiu ({0})");
define('_CA_UPGRADE_INTRO_LOG_ERROR', "Nota: Si voleu desar el registre de l'actualització en un arxiu cal tenir permisos d'escriptura per al directori {0} i després recarregar aquesta pàgina amb el navegador.");
define('_CA_UPGRADE_AUTH_PATH_INFO', "Per assegurar que realment vostè és el propietari d'aquest lloc web, creeu un arxiu i anomeneu-lo <strong>{0}</strong> en el directori upgrade de Jaws (<strong>{1}</strong>)");
define('_CA_UPGRADE_AUTH_UPLOAD', "Podeu pujar l'arxiu de la mateixa manera que ha pujat Jaws.");
define('_CA_UPGRADE_AUTH_KEY_INFO', "L'arxiu ha de contindre el codi mostrat en el quadre de més avall i res més.");
define('_CA_UPGRADE_AUTH_ENABLE_SECURITY', "Habilitar actualització segura (Impulsat per RSA)");
define('_CA_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "S'ha produït un error en la generació de la clau RSA, si us plau, torneu a intenta-ho.");
define('_CA_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "S'ha produït un error en la generació de la clau RSA. No està disponible l'extensió matemàtica.");
define('_CA_UPGRADE_AUTH_ERROR_KEY_FILE', "L'arxiu clau ({0}) no s'ha trobat, assegureu-vos d'haver-lo creat, i que el servidor té permissos de lectura.");
define('_CA_UPGRADE_AUTH_ERROR_KEY_MATCH', "La clau trobada ({0}), no coincideix amb la de sota, comproveu que l'heu creat correctament.");
define('_CA_UPGRADE_REQ_REQUIREMENT', "Requeriments");
define('_CA_UPGRADE_REQ_OPTIONAL', "Opcional, però recomanat");
define('_CA_UPGRADE_REQ_RECOMMENDED', "Recomanat");
define('_CA_UPGRADE_REQ_DIRECTIVE', "Directiva");
define('_CA_UPGRADE_REQ_ACTUAL', "Actual");
define('_CA_UPGRADE_REQ_RESULT', "Resultat");
define('_CA_UPGRADE_REQ_PHP_VERSION', "Versió de PHP");
define('_CA_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_CA_UPGRADE_REQ_DIRECTORY', "{0} directori");
define('_CA_UPGRADE_REQ_EXTENSION', "{0} extensió");
define('_CA_UPGRADE_REQ_FILE_UPLOAD', "Fitxers pujats");
define('_CA_UPGRADE_REQ_SAFE_MODE', "Mode segur");
define('_CA_UPGRADE_REQ_READABLE', "Llegible");
define('_CA_UPGRADE_REQ_WRITABLE', "Escrivible");
define('_CA_UPGRADE_REQ_OK', "D'acord");
define('_CA_UPGRADE_REQ_BAD', "Incorrecte");
define('_CA_UPGRADE_REQ_OFF', "Inactiu");
define('_CA_UPGRADE_REQ_ON', "Actiu");
define('_CA_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "El directori {0} no és llegible o escrivible, arregleu aquests permissos.");
define('_CA_UPGRADE_REQ_RESPONSE_PHP_VERSION', "La versió mínima de PHP per actualitzar Jaws és {0}, cal actualitzar la versió de PHP.");
define('_CA_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "Els directoris llistats a continuació com {0} no són llegibles o escrivibles, arregla els permisos.");
define('_CA_UPGRADE_REQ_RESPONSE_EXTENSION', "L'extensió {0} és necessària per utilitzar Jaws.");
define('_CA_UPGRADE_DB_INFO', "Ara cal configurar la base de dades, utilitzant la informació per actualitzar la base de dades actual.");
define('_CA_UPGRADE_DB_HOST', "Nom del host");
define('_CA_UPGRADE_DB_HOST_INFO', "Si no ho sabeu, el més segur es deixar-ho com a {0}.");
define('_CA_UPGRADE_DB_DRIVER', "Controlador");
define('_CA_UPGRADE_DB_USER', "Nom d'usuari");
define('_CA_UPGRADE_DB_PASS', "Contrasenya");
define('_CA_UPGRADE_DB_IS_ADMIN', "És l'Administrador de la Base de dades?");
define('_CA_UPGRADE_DB_NAME', "Nom de la base de dades");
define('_CA_UPGRADE_DB_PATH', "Camí de la base de dades");
define('_CA_UPGRADE_DB_PATH_INFO', "Ompliu aquest camp només si voleu canviar el camí de la base de dades en SQLite, Interbase o Firebird");
define('_CA_UPGRADE_DB_PORT', "Port de la base de dades");
define('_CA_UPGRADE_DB_PORT_INFO', "Només ompliu aquest camp si la base de dades s'està executant en un altre port al assignat per defecte.\nSi <strong>desconeix</strong> quin és el port real de la base de dades probablement serà el port per defecte. Deixe-lo en blanc.");
define('_CA_UPGRADE_DB_PREFIX', "Prefix de les taules");
define('_CA_UPGRADE_DB_PREFIX_INFO', "Text que es posarà abanç dels noms de les taules, de forma que podeu executar més d'un Jaws en la mateixa base de dades, p. ex. <strong>bloc_</strong>");
define('_CA_UPGRADE_DB_RESPONSE_PATH', "El camí de la base de dades no existeix");
define('_CA_UPGRADE_DB_RESPONSE_PORT', "El port només pot contenir un valor numèric");
define('_CA_UPGRADE_DB_RESPONSE_INCOMPLETE', "Heu d'omplir tots els camps, excepte el port,  el camí de la base de dades i el prefixe de les taules.");
define('_CA_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "Existeix un problema per conectar amb la base de dades, comproveu els detalls de la base de dades i torneu a provar.");
define('_CA_UPGRADE_REPORT_INFO', "Comparant la versió de Jaws instal·lada amb l'actual {0}.");
define('_CA_UPGRADE_REPORT_NOTICE', "A continuació trobareu les versions que aquest actualitzador és capaç d'actualitzar. A lo millor esteu executant una versió molt antiga, nosaltres ens encarregarem de la resta.");
define('_CA_UPGRADE_REPORT_NEED', "Requereix actualització");
define('_CA_UPGRADE_REPORT_NO_NEED', "No requereix actualització");
define('_CA_UPGRADE_REPORT_NO_NEED_CURRENT', "No requereix actualització (és l'actual)");
define('_CA_UPGRADE_REPORT_MESSAGE', "Si l'actualitzador troba que la seva versió de Jaws es antiga l'actualitzarà, sino, finalitzarà.");
define('_CA_UPGRADE_VER_INFO', "Actualitzarà desde {0} a {1} ");
define('_CA_UPGRADE_VER_NOTES', "<strong>Nota:</strong> Una vegada finalitzada l'actualització principal, altres gadgets (com Bloc, Organitzador de fotos, etc) s'hauran d'actualitzar. Podeu fer-ho des del Tauler de Control.");
define('_CA_UPGRADE_VER_RESPONSE_GADGET_FAILED', "S'ha produït un problema en instal·lar el nucli del gadget {0}");
define('_CA_UPGRADE_CONFIG_INFO', "Cal desar l'arxiu de configuració.");
define('_CA_UPGRADE_CONFIG_SOLUTION', "Això ho podeu fer de dues maneres");
define('_CA_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Doneu permís d'escriptura a <strong>{0}</strong>, i a continuació, cliqueu següent, l'actualitzador crearà el fitxer de configuració per si mateix.");
define('_CA_UPGRADE_CONFIG_SOLUTION_UPLOAD', "O bé, podeu copiar i enganxar el contingut del següent quadre de text al fitxer <strong>{0}</strong> i desar-lo.");
define('_CA_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "S'ha produït un error desconegut en escriure el fitxer de configuració.");
define('_CA_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "Cal donar permís d'escriptura al directori <strong>config</string>, o crear-lo <strong>{0}</strong> manualment.");
define('_CA_UPGRADE_FINISH_INFO', "Ha finalitzat l'actualització del lloc web.");
define('_CA_UPGRADE_FINISH_CHOICES', "Ara podeu: <a href=\"{0}\">Veure el lloc web</a> o bé, <a href=\"{1}\">anar al tauler de control</a>.");
define('_CA_UPGRADE_FINISH_MOVE_LOG', "Si teniu activada l'opció <i>desar el registre de processos</i>, li suggerim que l'esborreu.");
define('_CA_UPGRADE_FINISH_THANKS', "Gràcies per utilitzar Jaws.");
