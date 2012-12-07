<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Jonathan Hernandez <ion@suavizado.com>"
 * "Language-Team: ES"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_ES_INSTALL_INTRODUCTION', "Introducción");
define('_ES_INSTALL_AUTHENTICATION', "Autenticación");
define('_ES_INSTALL_REQUIREMENTS', "Requerimientos");
define('_ES_INSTALL_DATABASE', "Base de datos");
define('_ES_INSTALL_CREATEUSER', "Crear un usuario");
define('_ES_INSTALL_SETTINGS', "Ajustes");
define('_ES_INSTALL_WRITECONFIG', "Guardar configuración");
define('_ES_INSTALL_FINISHED', "Finalizado");
define('_ES_INSTALL_INTRO_WELCOME', "Bienvenido al instalador de Jaws");
define('_ES_INSTALL_INTRO_INSTALLER', "Este instalador te guia para instalar Jaws en tu sitio, por favor asegurate de tener las siguientes cosas disponibles");
define('_ES_INSTALL_INTRO_DATABASE', "Detalles de la base de datos - hostname, usuario, contraseña, nombre de la base de datos");
define('_ES_INSTALL_INTRO_FTP', "Una forma de subir archivos, probablemente FTP");
define('_ES_INSTALL_INTRO_MAIL', "La información de tu servidor de correo en caso de que estes usando alguno.");
define('_ES_INSTALL_INTRO_LOG', "Guarda la bitacora del proceso y errores de la instalación en un archivo ({0})");
define('_ES_INSTALL_INTRO_LOG_ERROR', "Nota: Si quieres guardar la bitacora del proceso de instalación a un archivo necesitas tener permisos de escritura en el directorio {0} y luego recargar esta página en tu navegador");
define('_ES_INSTALL_AUTH_PATH_INFO', "Para asegurar que tu eres realmente el dueño de este sitio, porfavor crea un archivo llamado {0} en el directorio de instalación {1}");
define('_ES_INSTALL_AUTH_UPLOAD', "Puedes subir el archivo de la misma manera en que subiste la instalación de Jaws");
define('_ES_INSTALL_AUTH_KEY_INFO', "El archivo debe contener el código mostrado en el cuadro de abajo, y nada mas.");
define('_ES_INSTALL_AUTH_ENABLE_SECURITY', "Habilitar instalación segura (RSA)");
define('_ES_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "Error en la generación de llave RSA, intentalo de nuevo");
define('_ES_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "Error en la generación de llave RSA, intentalo de nuevo");
define('_ES_INSTALL_AUTH_ERROR_KEY_FILE', "Tu archivo con la llave {0} no se encontro, asegurate de que lo creaste y que el servidor web lo puede leer");
define('_ES_INSTALL_AUTH_ERROR_KEY_MATCH', "La llave {0} no coincide con la de abajo, asegurate de que pusiste la llave correcta");
define('_ES_INSTALL_REQ_REQUIREMENT', "Requerimientos");
define('_ES_INSTALL_REQ_OPTIONAL', "Opcional pero recomendado");
define('_ES_INSTALL_REQ_RECOMMENDED', "Recomendado");
define('_ES_INSTALL_REQ_DIRECTIVE', "Directiva");
define('_ES_INSTALL_REQ_ACTUAL', "Actual");
define('_ES_INSTALL_REQ_RESULT', "Resultado");
define('_ES_INSTALL_REQ_PHP_VERSION', "Versión de PHP");
define('_ES_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_ES_INSTALL_REQ_DIRECTORY', "directorio {0} ");
define('_ES_INSTALL_REQ_EXTENSION', "extensión {0}");
define('_ES_INSTALL_REQ_FILE_UPLOAD', "Subida de archivos");
define('_ES_INSTALL_REQ_SAFE_MODE', "Modo seguro");
define('_ES_INSTALL_REQ_READABLE', "Leíble");
define('_ES_INSTALL_REQ_WRITABLE', "Escribible");
define('_ES_INSTALL_REQ_OK', "BUENO");
define('_ES_INSTALL_REQ_BAD', "MALO");
define('_ES_INSTALL_REQ_OFF', "Apagado");
define('_ES_INSTALL_REQ_ON', "Encendido");
define('_ES_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "El directorio {0} no es leíble o escribible, arregla los permisos.");
define('_ES_INSTALL_REQ_RESPONSE_PHP_VERSION', "La versión mínima para instalar Jaws es {0}, necesitas actualizar tu versión de PHP.");
define('_ES_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "Los directorios listados abajo como {0} no son leíbles o escribibles, arregla los permisos.");
define('_ES_INSTALL_REQ_RESPONSE_EXTENSION', "La extensión {0} es necesaria para usar Jaws.");
define('_ES_INSTALL_DB_INFO', "Ahora necesitas configurar tu base de datos, la cual es usada para almacenar la informacion que sera mostrada despues.");
define('_ES_INSTALL_DB_NOTICE', "La base de datos necesita estar creada para que este proceso funcione");
define('_ES_INSTALL_DB_HOST', "Nombre del host");
define('_ES_INSTALL_DB_HOST_INFO', "Si no conoces esto lo mas seguro es dejarlo como {0}.");
define('_ES_INSTALL_DB_DRIVER', "Manejador");
define('_ES_INSTALL_DB_USER', "Usuario");
define('_ES_INSTALL_DB_PASS', "Contraseña");
define('_ES_INSTALL_DB_IS_ADMIN', "¿Es administrador de la base de datos?");
define('_ES_INSTALL_DB_NAME', "Nombre de la base de datos");
define('_ES_INSTALL_DB_PATH', "Ruta de la base de datos");
define('_ES_INSTALL_DB_PATH_INFO', "Llena este campo solo si necesitas cambiar la ruta de tu base de datos en SQLite, Interbase o Firebird");
define('_ES_INSTALL_DB_PORT', "Puerto de la base de datos");
define('_ES_INSTALL_DB_PORT_INFO', "Solo llena este campo su tu base de datos esta corriendo en otro puerto diferente al por default. Si no tienes idea en que puerto esta corriendo tu base de datos lo mas seguro es que lo este haciendo en el puerto default.");
define('_ES_INSTALL_DB_PREFIX', "Prefijo de tablas");
define('_ES_INSTALL_DB_PREFIX_INFO', "Texto que sera antepuesto en los nombres de las tablas, de manera que puedas correr mas de un Jaws en la misma base de datos, por ejemplo <strong>blog</strong>");
define('_ES_INSTALL_DB_RESPONSE_PATH', "No existe la ruta de la base de datos");
define('_ES_INSTALL_DB_RESPONSE_PORT', "El puerto solo puede ser un valor numerico");
define('_ES_INSTALL_DB_RESPONSE_INCOMPLETE', "Necesitas llenar todos los campos a excepción de el prefijo de tablas y el puerto");
define('_ES_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Ocurrio un problema al conectarse a la base de datos, checa los detalles e intenta de nuevo.");
define('_ES_INSTALL_DB_RESPONSE_GADGET_INSTALL', "Ocurrio un problema al instalar el gadget {0}");
define('_ES_INSTALL_DB_RESPONSE_SETTINGS', "Ocurrio un problema al configurar la base de datos");
define('_ES_INSTALL_USER_INFO', "Ahora puedes crear una cuenta para ti.");
define('_ES_INSTALL_USER_NOTICE', "Recuerda no escojer una contraseña fácil dado que alguien que conozca tu contraseña tendrá acceso a controlar tu sitio completamente.");
define('_ES_INSTALL_USER_USER', "Usuario");
define('_ES_INSTALL_USER_USER_INFO', "El nombre de usuario");
define('_ES_INSTALL_USER_PASS', "Contraseña");
define('_ES_INSTALL_USER_REPEAT', "Repetir");
define('_ES_INSTALL_USER_REPEAT_INFO', "Repite la contraseña para asegurar que no existen errores");
define('_ES_INSTALL_USER_NAME', "Nombre");
define('_ES_INSTALL_USER_NAME_INFO', "Tu nombre real");
define('_ES_INSTALL_USER_EMAIL', "E-Mail");
define('_ES_INSTALL_USER_RESPONSE_PASS_MISMATCH', "Las contraseñas no coinciden, intenta de nuevo");
define('_ES_INSTALL_USER_RESPONSE_INCOMPLETE', "Debes llenar los campos de usuario y contraseñas");
define('_ES_INSTALL_USER_RESPONSE_CREATE_FAILED', "Ocurrio un problema al crear tu usuario.");
define('_ES_INSTALL_SETTINGS_INFO', "Ahora puedes configurar los valores iniciales para tu sitio. Tu puedes cambiar cualquiera de estos despues desde el Panel de Control.");
define('_ES_INSTALL_SETTINGS_SITE_NAME', "Nombre del sitio");
define('_ES_INSTALL_SETTINGS_SITE_NAME_INFO', "El nombre que se muestra para tu sitio");
define('_ES_INSTALL_SETTINGS_SLOGAN', "Descripción");
define('_ES_INSTALL_SETTINGS_SLOGAN_INFO', "Una descripción larga de tu sitio");
define('_ES_INSTALL_SETTINGS_DEFAULT_GADGET', "Gadget inicial");
define('_ES_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "El gadget que se mostrara cuando alguien visita tu página de inicio.");
define('_ES_INSTALL_SETTINGS_SITE_LANGUAGE', "Lenguaje");
define('_ES_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "El lenguaje en el que esta tu sitio.");
define('_ES_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Necesitas llenar el nombre del sitio");
define('_ES_INSTALL_CONFIG_INFO', "Ahora necesitas guardar tu archivo de configuración");
define('_ES_INSTALL_CONFIG_SOLUTION', "Puedes hacerlo de 2 maneras");
define('_ES_INSTALL_CONFIG_SOLUTION_PERMISSION', "Has {0} escribible, y presiona siguiente, lo cual permitira al instalador guardar la configuración por si mismo.");
define('_ES_INSTALL_CONFIG_SOLUTION_UPLOAD', "Copia y pega el contenido que aparece abajo en un archivo y guardalo como {0}");
define('_ES_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Ocurrio un error desconocido al escribir el archivo de configuración.");
define('_ES_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "Necesitas hacer el directorio <strong>config</strong> escribible o crear {0} a mano.");
define('_ES_INSTALL_FINISH_INFO', "¡Has finalizado la configuración de tu sitio!");
define('_ES_INSTALL_FINISH_CHOICES', "Ahora tienes 2 opciones, puedes entrar a <a href=\"{0}\">ver tu sitio</a> o <a href=\"{1}\">entrar al Panel de Control</a>.");
define('_ES_INSTALL_FINISH_MOVE_LOG', "Nota: Si tienes encendido la opcion de guardar la bitacora sugerimos que la borres.");
define('_ES_INSTALL_FINISH_THANKS', "¡Gracias por usar Jaws!");
