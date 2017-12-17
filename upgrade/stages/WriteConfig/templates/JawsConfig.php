<!-- BEGIN JawsConfig --><?php
/**
 * JawsConfig.php - Configuration variables
 *
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 */
// Path where Jaws is installed
define('JAWS_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
<!-- BEGIN jaws_data -->
define('JAWS_DATA', '{{jaws_data}}');
<!-- END jaws_data -->
<!-- BEGIN jaws_base_data -->
define('JAWS_BASE_DATA', '{{jaws_base_data}}');
<!-- END jaws_base_data -->
<!-- BEGIN jaws_themes -->
define('JAWS_THEMES', '{{jaws_themes}}');
<!-- END jaws_themes -->
<!-- BEGIN jaws_base_themes -->
define('JAWS_BASE_THEMES', '{{jaws_base_themes}}');
<!-- END jaws_base_themes -->
<!-- BEGIN jaws_cache -->
define('JAWS_CACHE', '{{jaws_cache}}');
<!-- END jaws_cache -->

// temporary definition
define('JAWS_REGISTRY_JSON_ENCODED', 1);

$db = array(); //DONT RENAME/DELETE THIS VARIABLE!!
/**
 * DB Configuration
 *
 * In this section you configure some params of your DB connection, such as
 * username, password, name, host and driver.
 * The prefix is optional, just make sure it has an empty value
 */
$db['driver']   = '{{db_driver}}';
$db['host']     = '{{db_host}}';
$db['port']     = '{{db_port}}';
$db['user']     = '{{db_user}}';
$db['password'] = '{{db_pass}}';
$db['isdba']    = '{{db_isdba}}';
$db['path']     = '{{db_path}}';
$db['name']     = '{{db_name}}';
$db['prefix']   = '{{db_prefix}}';

/**
 * Logs
 *
 * If you want to enable logging Jaws, maybe to track the errors, or to debug a good
 * idea is to configure/enable it.
 *
 * Debug: false Disable log
 *        0     Disabled
 *        1     Emergency log level
 *        2     Alert log and utmost levels
 *        3     Critical log and utmost levels
 *        4     Error log and utmost levels
 *        5     Warning log and utmost levels
 *        6     Notice log and utmost levels
 *        7     Info log and utmost levels
 *        8     Debug log and utmost levels
 */
define('LOG_ACTIVATED', {{log_level}});

/**
 * Log Method
 *
 * How do you want to print/save the log?. Currently we just support:
 *
 *    LogToFile: Logs the message to a specified file.
 *     Options:
 *      file (required): File where you want to save data, IMPORTANT. PHP needs write-access to that file
 *      maxlines (optional): How many lines will contain the file. Default = 500
 *      rotatelimit (optional): How many rotated files will be created (i.e. jaws.log.1, jaws.log.2 etc). Default = 1
 *     Example:
 *        $GLOBALS['logger']['method'] = 'LogToFile';
 *        $GLOBALS['logger']['options'] = array();
 *        $GLOBALS['logger']['options']['file'] = "/tmp/jaws.log";
 *        $GLOBALS['logger']['options']['size'] = 2097152;
 *
 *    LogToSyslog: Logs the message to the syslog, you can find the log of this blog just by looking to the tag you
 *    define
 *      Options:
 *       indent: String ident is added to each message. Default: "Jaws_Log"
 *      Example:
 *        $GLOBALS['logger']['method'] = 'LogToSyslog';
 *        $GLOBALS['logger']['options'] = array();
 *        $GLOBALS['logger']['options']['indent'] = 'Jaws_Log';
 *
 *    LogToWindow: All log messages are printed to new window
 *       Example:
 *        $GLOBALS['logger']['method'] = 'LogToWindow';
 *
 *    LogToApache': Prints the message to the apache error log file
 *       Example:
 *        $GLOBALS['logger']['method'] = 'LogToApache';
 *
 *    LogToFirebug: Prints the messages into the Firebugs console (The firebug extensions is required)
 *       Example:
 *        $GLOBALS['logger']['method'] = 'LogToFirebug';
 */

$GLOBALS['logger']['method'] = 'LogToFile';
$GLOBALS['logger']['options'] = array();
$GLOBALS['logger']['options']['file'] = JAWS_DATA . 'logs/.jaws.log';
$GLOBALS['logger']['options']['size'] = 2097152;
<!-- END JawsConfig -->
