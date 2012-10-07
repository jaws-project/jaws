<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: Jonathan Hernandez <ion@suavizado.com>"
 * "Language-Team: ES"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_ES_UPGRADE_INTRODUCTION', "Introducción");
define('_ES_UPGRADE_AUTHENTICATION', "Autenticación");
define('_ES_UPGRADE_REQUIREMENTS', "Requerimientos");
define('_ES_UPGRADE_DATABASE', "Base de datos");
define('_ES_UPGRADE_REPORT', "Reporte");
define('_ES_UPGRADE_VER_TO_VER', "{0} a {1}");
define('_ES_UPGRADE_SETTINGS', "Ajustes");
define('_ES_UPGRADE_WRITECONFIG', "Guardar configuración");
define('_ES_UPGRADE_FINISHED', "Finalizado");
define('_ES_UPGRADE_INTRO_WELCOME', "Bienvenidos al actualizador de Jaws");
define('_ES_UPGRADE_INTRO_UPGRADER', "Al usar el actualizador tu puedes actualizar tu vieja instalación a lo mas actual. Solo asegurate de que tienes las siguientes cosas disponibles.");
define('_ES_UPGRADE_INTRO_DATABASE', "Detalles de la base de datos - nombre del host, usuario, contraseña, nombre de la base de datos.");
define('_ES_UPGRADE_INTRO_FTP', "Una forma de subir archivos, probablemente FTP.");
define('_ES_UPGRADE_INTRO_LOG', "Guardar la bitacora del proceso (y errores) a un archivo ({0})");
define('_ES_UPGRADE_INTRO_LOG_ERROR', "Nota: Si quieres guardar la bitacora de la actualización a un archivo necesitas tener permisos de escritura para el directorio {0} y luego recargar esta página.");
define('_ES_UPGRADE_AUTH_PATH_INFO', "Para asegurar que realmente eres el dueño de este sitio, crea un archivo llamado <strong>{0}</strong> en el directorio upgrade de Jaws (<strong>{1}</strong>)");
define('_ES_UPGRADE_AUTH_UPLOAD', "Puedes subir el archivo de la misma manera que subiste Jaws.");
define('_ES_UPGRADE_AUTH_KEY_INFO', "El archivo debe contener el codigo mostrado en la caja de abajo y nada mas.");
define('_ES_UPGRADE_AUTH_ENABLE_SECURITY', "Habilitar actualización segura (RSA)");
define('_ES_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "Error en la generación de llave RSA, intenta de nuevo.");
define('_ES_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "Error en la generación de llave RSA. No existe extensión matemática");
define('_ES_UPGRADE_AUTH_ERROR_KEY_FILE', "El archivo de codigo ({0}) no se encuentra, asegurate de haberlo creado y que el servidor web lo pueda leer.");
define('_ES_UPGRADE_AUTH_ERROR_KEY_MATCH', "La llave encontrada ({0}) no coincide con la de abajo, asegurate de que este bien.");
define('_ES_UPGRADE_REQ_REQUIREMENT', "Requerimientos");
define('_ES_UPGRADE_REQ_OPTIONAL', "Opcional pero recomendado");
define('_ES_UPGRADE_REQ_RECOMMENDED', "Recomendado");
define('_ES_UPGRADE_REQ_DIRECTIVE', "Directiva");
define('_ES_UPGRADE_REQ_ACTUAL', "Actual");
define('_ES_UPGRADE_REQ_RESULT', "Resultado");
define('_ES_UPGRADE_REQ_PHP_VERSION', "Versión de PHP");
define('_ES_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_ES_UPGRADE_REQ_DIRECTORY', "Directorio {0}");
define('_ES_UPGRADE_REQ_EXTENSION', "Extensión {0}");
define('_ES_UPGRADE_REQ_FILE_UPLOAD', "Subir archivos");
define('_ES_UPGRADE_REQ_SAFE_MODE', "Modo seguro");
define('_ES_UPGRADE_REQ_READABLE', "Leíble");
define('_ES_UPGRADE_REQ_WRITABLE', "Escribible");
define('_ES_UPGRADE_REQ_OK', "BIEN");
define('_ES_UPGRADE_REQ_BAD', "MAL");
define('_ES_UPGRADE_REQ_OFF', "Apagado");
define('_ES_UPGRADE_REQ_ON', "Encendido");
define('_ES_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "El directorio {0} no es leíble o escribible, arregla estos permisos.");
define('_ES_UPGRADE_REQ_RESPONSE_PHP_VERSION', "La versión mínima de PHP para actualizar Jaws es {0}, necesitas actualizar tu versión de PHP.");
define('_ES_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "Los directorios listados a continuación como {0} no son leíbles o escribibles, arregla los permisos.");
define('_ES_UPGRADE_REQ_RESPONSE_EXTENSION', "La extensión {0} es necesaria para usar Jaws.");
define('_ES_UPGRADE_DB_INFO', "Ahora necesitas configurar la base de datos.");
define('_ES_UPGRADE_DB_HOST', "Nombre del host");
define('_ES_UPGRADE_DB_HOST_INFO', "Si no lo conoces, lo mas seguro es dejarlo como {0}");
define('_ES_UPGRADE_DB_DRIVER', "Manejador");
define('_ES_UPGRADE_DB_USER', "Usuario");
define('_ES_UPGRADE_DB_PASS', "Contraseña");
define('_ES_UPGRADE_DB_IS_ADMIN', "¿Es administrador de la base de datos?");
define('_ES_UPGRADE_DB_NAME', "Nombre de la base de datos");
define('_ES_UPGRADE_DB_PATH', "Ruta de la base de datos");
define('_ES_UPGRADE_DB_PATH_INFO', "Llena este campo solo si quieres cambiar la ruta de tu base de datos en SQLite, Interbase o Firebird");
define('_ES_UPGRADE_DB_PORT', "Puerto de la base de datos");
define('_ES_UPGRADE_DB_PORT_INFO', "Solo llena este campo su tu base de datos esta corriendo en otro puerto diferente al por default. Si no tienes idea en que puerto esta corriendo tu base de datos lo mas seguro es que lo este haciendo en el puerto default.");
define('_ES_UPGRADE_DB_PREFIX', "Prefijo de tablas");
define('_ES_UPGRADE_DB_PREFIX_INFO', "Texto que sera antepuesto en los nombres de las tablas, de manera que puedas correr mas de un Jaws en la misma base de datos, por ejemplo <strong>blog_</strong>");
define('_ES_UPGRADE_DB_RESPONSE_PATH', "No existe la ruta a la base de datos");
define('_ES_UPGRADE_DB_RESPONSE_PORT', "El puerto solo puede ser un valor numerico");
define('_ES_UPGRADE_DB_RESPONSE_INCOMPLETE', "Debes llenar todos los campos a excepción de el prefijo de tablas y el puerto.");
define('_ES_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "Ocurrio un problema al conectarse a la base de datos, checa los detalles e intenta de nuevo.");
define('_ES_UPGRADE_REPORT_INFO', "Comparando tu versión de Jaws con la actual {0}");
define('_ES_UPGRADE_REPORT_NOTICE', "A continuación encontraras las versiones que este actualizador se encargara de actualizar. Talvez estas corriendo una versión muy vieja, nosotros nos encargaremos del resto.");
define('_ES_UPGRADE_REPORT_NEED', "Requiere actualización");
define('_ES_UPGRADE_REPORT_NO_NEED', "No requiere actualización");
define('_ES_UPGRADE_REPORT_NO_NEED_CURRENT', "No requiere actualización (es la actual)");
define('_ES_UPGRADE_REPORT_MESSAGE', "Si el actualizador encuentra que tu versión de Jaws es vieja la actualizara, si no, terminara.");
define('_ES_UPGRADE_VER_INFO', "Actualizar de {0} a {1} hará");
define('_ES_UPGRADE_VER_NOTES', "<strong>Nota:</strong> Una vez finalizada la actualización, otros gadgets (como Blog, Phoo, etc) se requeriran actualizar. Puedes hacer esto desde el Panel de Control.");
define('_ES_UPGRADE_VER_RESPONSE_GADGET_FAILED', "Ocurrio un problema al instalar el gadget {0}");
define('_ES_UPGRADE_CONFIG_INFO', "Ahora necesitas guardar el archivo de configuración");
define('_ES_UPGRADE_CONFIG_SOLUTION', "Lo puedes hacer de 2 maneras");
define('_ES_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Has {0} escribible, y presiona siguiente, lo cual permitira al instalador guardar la configuración por si mismo.");
define('_ES_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Copia y pega el contenido que aparece abajo en un archivo y guardalo como {0}");
define('_ES_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "Ocurrio un error desconocido al escribir el archivo de configuración.");
define('_ES_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "Necesitas hacer el directorio <strong>config</strong> escribible o crear {0} a mano.");
define('_ES_UPGRADE_FINISH_INFO', "¡Has finalizado la actualización de tu sitio!");
define('_ES_UPGRADE_FINISH_CHOICES', "Ahora tienes 2 opciones, puedes entrar a <a href=\"{0}\">ver tu sitio</a> o <a href=\"{1}\">entrar al Panel de Control</a>.");
define('_ES_UPGRADE_FINISH_MOVE_LOG', "Nota: Si tienes encendido la opcion de guardar la bitacora sugerimos que la borres. ");
define('_ES_UPGRADE_FINISH_THANKS', "¡Gracias por usar Jaws!");
