<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Francesc D. Subirats <fsubirats@gmail.com>"
 * "Language-Team: CA"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_CA_INSTALL_INTRODUCTION', "Introducció");
define('_CA_INSTALL_AUTHENTICATION', "Identificació");
define('_CA_INSTALL_REQUIREMENTS', "Requeriments");
define('_CA_INSTALL_DATABASE', "Base de dades");
define('_CA_INSTALL_CREATEUSER', "Crea un usuari");
define('_CA_INSTALL_SETTINGS', "Configuració");
define('_CA_INSTALL_WRITECONFIG', "Desa la configuració");
define('_CA_INSTALL_FINISHED', "Finalitzat");
define('_CA_INSTALL_INTRO_WELCOME', "Benvingut a l'intal·lador de Jaws.");
define('_CA_INSTALL_INTRO_INSTALLER', "Aquest instal·lador el guiarà per a instal·lar Jaws, assegureu-vos que teniu disponible la informació següent");
define('_CA_INSTALL_INTRO_DATABASE', "Detalls de la base de dades - nom del host, usuari, contrasenya, nom de la base de dades.");
define('_CA_INSTALL_INTRO_FTP', "Una forma de pujar fitxers, probablement, FTP.");
define('_CA_INSTALL_INTRO_MAIL', "La informació sobre el servidor de correu (nom del host, usuari i contrasenya) si el teniu que utilitzar.");
define('_CA_INSTALL_INTRO_LOG', "Desa el registre dels processos (i errors) en un arxiu ({0})");
define('_CA_INSTALL_INTRO_LOG_ERROR', "Nota: Si voleu desar el registre de processos en un arxiu cal tenir permisos d'escriptura per al directori {0} i després actualitzar aquesta pàgina amb el navegador.");
define('_CA_INSTALL_AUTH_PATH_INFO', "Per assegurar que realment és el propietari d'aquest lloc web, creeu un arxiu i anomeneu-lo <strong>{0}</strong> en el directori install de Jaws (<strong>{1}</strong>)");
define('_CA_INSTALL_AUTH_UPLOAD', "Podeu pujar l'arxiu de la mateixa manera que ha pujat Jaws.");
define('_CA_INSTALL_AUTH_KEY_INFO', "L'arxiu ha de contenir el codi mostrat en el quadre de text i res més.");
define('_CA_INSTALL_AUTH_ENABLE_SECURITY', "Habilitar l'actualització segura (Impulsat per RSA)");
define('_CA_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "S'ha produït un error en la generació de la clau RSA, torneu a intenta-ho.");
define('_CA_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "S'ha produït un error en la generació de la clau RSA. No està disponible l'extensió matemàtica.");
define('_CA_INSTALL_AUTH_ERROR_KEY_FILE', "L'arxiu clau ({0}) no s'ha trobat, assegureu-vos d'haver-lo creat, i que el servidor té permisos de lectura.");
define('_CA_INSTALL_AUTH_ERROR_KEY_MATCH', "La clau trobada ({0}), no coincideix amb la de sota, comproveu que l'heu creat correctament.");
define('_CA_INSTALL_REQ_REQUIREMENT', "Requeriments");
define('_CA_INSTALL_REQ_OPTIONAL', "Opcional, però recomanat");
define('_CA_INSTALL_REQ_RECOMMENDED', "Recomanat");
define('_CA_INSTALL_REQ_DIRECTIVE', "Directiva");
define('_CA_INSTALL_REQ_ACTUAL', "Real");
define('_CA_INSTALL_REQ_RESULT', "Resultat");
define('_CA_INSTALL_REQ_PHP_VERSION', "Versió de PHP");
define('_CA_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_CA_INSTALL_REQ_DIRECTORY', "{0} directori");
define('_CA_INSTALL_REQ_EXTENSION', "{0} extensió");
define('_CA_INSTALL_REQ_FILE_UPLOAD', "Fitxers pujats");
define('_CA_INSTALL_REQ_SAFE_MODE', "Mode segur");
define('_CA_INSTALL_REQ_READABLE', "Llegible");
define('_CA_INSTALL_REQ_WRITABLE', "Escrivible");
define('_CA_INSTALL_REQ_OK', "D'acord");
define('_CA_INSTALL_REQ_BAD', "Incorrecte");
define('_CA_INSTALL_REQ_OFF', "Inactiu");
define('_CA_INSTALL_REQ_ON', "Actiu");
define('_CA_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "El directori {0} no és llegible o escrivible, arregleu aquests permisos.");
define('_CA_INSTALL_REQ_RESPONSE_PHP_VERSION', "La versió mínima de PHP per instal·lar Jaws és {0}, cal actualitzar la versió de PHP.");
define('_CA_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "Els directoris llistats a continuació com {0} no són llegibles o escrivibles, arregleu els permisos.");
define('_CA_INSTALL_REQ_RESPONSE_EXTENSION', "L'extensió {0} és necessària per utilitzar Jaws.");
define('_CA_INSTALL_DB_INFO', "Ara cal configurar la base de dades, la qual s'utilitzarà per inserir la informació que es mostrarà després.");
define('_CA_INSTALL_DB_NOTICE', "La base de dades necessita estar creada per a què aquest procès funcioni.");
define('_CA_INSTALL_DB_HOST', "Nom del host (localhost)");
define('_CA_INSTALL_DB_HOST_INFO', "Si no ho sabeu, el més segur és deixar-ho com a {0}.");
define('_CA_INSTALL_DB_DRIVER', "Controlador");
define('_CA_INSTALL_DB_USER', "Nom d'usuari");
define('_CA_INSTALL_DB_PASS', "Contrasenya");
define('_CA_INSTALL_DB_IS_ADMIN', "És l'administrador de la base de dades?");
define('_CA_INSTALL_DB_NAME', "Nom de la base de dades");
define('_CA_INSTALL_DB_PATH', "Camí de la base de dades");
define('_CA_INSTALL_DB_PATH_INFO', "Ompliu aquest camp només si voleu canviar el camí de la base de dades en SQLite, Interbase o Firebird driver.");
define('_CA_INSTALL_DB_PORT', "Port de la base de dades");
define('_CA_INSTALL_DB_PORT_INFO', "Només ompliu aquest camp si la base de dades s'està executant en un altre port al assignat per defecte.\nSi <strong>desconeix</strong> quin és el port real de la base de dades probablement serà el port per defecte. Li recomanem deixar el camp en blanc.");
define('_CA_INSTALL_DB_PREFIX', "Prefix de les taules");
define('_CA_INSTALL_DB_PREFIX_INFO', "Text que es posarà abans dels noms de les taules, de forma que podeu executar més d'un Jaws en la mateixa base de dades, p. ex. <strong>bloc_</strong>");
define('_CA_INSTALL_DB_RESPONSE_PATH', "El camí de la base de dades no existeix.");
define('_CA_INSTALL_DB_RESPONSE_PORT', "El port només pot contenir un valor numèric.");
define('_CA_INSTALL_DB_RESPONSE_INCOMPLETE', "Heu d'omplir tots els camps, excepte el port,  el camí de la base de dades i el prefixe de les taules.");
define('_CA_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Existeix un problema per a conectar amb la base de dades, comproveu els detalls de la base de dades i torneu a provar.");
define('_CA_INSTALL_DB_RESPONSE_GADGET_INSTALL', "S'ha produït un problema en instal·lar el gadget {0}");
define('_CA_INSTALL_DB_RESPONSE_SETTINGS', "S'ha produït un problema en configurar la base de dades.");
define('_CA_INSTALL_USER_INFO', "Ara podeu crear un usuari per al seu compte.");
define('_CA_INSTALL_USER_NOTICE', "Recordeu no escollir una contrasenya fàcil ja que si algú la descobreix tindrà accès per a controlar completament el lloc web.");
define('_CA_INSTALL_USER_USER', "Nom d'usuari");
define('_CA_INSTALL_USER_USER_INFO', "Aquest nom d'usuari es mostrarà als missatges o articles.");
define('_CA_INSTALL_USER_PASS', "Contrasenya");
define('_CA_INSTALL_USER_REPEAT', "Repeteix");
define('_CA_INSTALL_USER_REPEAT_INFO', "Repetiu la contrasenya per assegurar que no existeixen errors.");
define('_CA_INSTALL_USER_NAME', "Nom");
define('_CA_INSTALL_USER_NAME_INFO', "El seu nom real.");
define('_CA_INSTALL_USER_EMAIL', "Correu electrònic");
define('_CA_INSTALL_USER_RESPONSE_PASS_MISMATCH', "Els camps de contrasenya no coincideixen, torneu a escriure-la.");
define('_CA_INSTALL_USER_RESPONSE_INCOMPLETE', "Heu d'omplir els camps d'usuari i contrasenya.");
define('_CA_INSTALL_USER_RESPONSE_CREATE_FAILED', "S'ha produït un error al crear l'usuari.");
define('_CA_INSTALL_SETTINGS_INFO', "Ara podeu configurar els valores inicials per al lloc web. Podeu canviar qualsevol d'aquests paràmetres després des del Tauler de Control.");
define('_CA_INSTALL_SETTINGS_SITE_NAME', "Nom del lloc web");
define('_CA_INSTALL_SETTINGS_SITE_NAME_INFO', "Nom que es mostrarà al lloc web.");
define('_CA_INSTALL_SETTINGS_SLOGAN', "Descripció");
define('_CA_INSTALL_SETTINGS_SLOGAN_INFO', "Descripcio llarga.");
define('_CA_INSTALL_SETTINGS_DEFAULT_GADGET', "Gadget per defecte");
define('_CA_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "El gadget que es mostrarà quan algú visiti la pàgina inicial");
define('_CA_INSTALL_SETTINGS_SITE_LANGUAGE', "Idioma del lloc");
define('_CA_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "Idioma que es mostrarà al lloc web.");
define('_CA_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Cal escriure el nom de lloc web.");
define('_CA_INSTALL_CONFIG_INFO', "Cal desar l'arxiu de configuració.");
define('_CA_INSTALL_CONFIG_SOLUTION', "Això ho podeu fer de dues maneres");
define('_CA_INSTALL_CONFIG_SOLUTION_PERMISSION', "Doneu permís d'escriptura a <strong>{0}</strong>, i a continuació, pitgeu següent, l'instal·lador crearà el fitxer de configuració per si mateix.");
define('_CA_INSTALL_CONFIG_SOLUTION_UPLOAD', "O bé, podeu copiar i enganxar el contingut del següent quadre de text al fitxer <strong>{0}</strong> i desar-lo.");
define('_CA_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "S'ha produït un error desconegut en escriure el fitxer de configuració.");
define('_CA_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "Cal donar permís d'escriptura al directori <strong>config</string>, o crear-lo <strong>{0}</strong> manualment.");
define('_CA_INSTALL_FINISH_INFO', "Ha finalitzat amb èxit la configuració del lloc web!");
define('_CA_INSTALL_FINISH_CHOICES', "Ara podeu: <a href=\"{0}\">Veure el lloc web</a> o bé, <a href=\"{1}\">anar al tauler de control</a>.");
define('_CA_INSTALL_FINISH_MOVE_LOG', "Nota: Si teniu activada l'opció <i>desar el registre de processos</i>, li suggerim que l'esborreu.");
define('_CA_INSTALL_FINISH_THANKS', "Gràcies per utilitzar Jaws.");
