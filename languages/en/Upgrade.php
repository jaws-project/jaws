<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: Ali Fazelzadeh <afz@php.net>"
 * "Language-Team: EN"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

/* Upgrader common words */
define('_EN_UPGRADE_INTRODUCTION', "Introduction");
define('_EN_UPGRADE_AUTHENTICATION', "Authentication");
define('_EN_UPGRADE_REQUIREMENTS', "Requirements");
define('_EN_UPGRADE_DATABASE', "Database");
define('_EN_UPGRADE_REPORT', "Report");
define('_EN_UPGRADE_VER_TO_VER', "{0} to {1}{2}");
define('_EN_UPGRADE_SETTINGS', "Settings");
define('_EN_UPGRADE_WRITECONFIG', "Save Configuration");
define('_EN_UPGRADE_CLEANUP', "Cleanup");
define('_EN_UPGRADE_FINISHED', "Finished");

/* Introduction */
define('_EN_UPGRADE_INTRO_WELCOME', "Welcome to the Jaws upgrade wizard.");
define('_EN_UPGRADE_INTRO_UPGRADER', "Using this wizard you can upgrade an old installation of Jaws to the current one. Please make sure you have the following things available");
define('_EN_UPGRADE_INTRO_DATABASE', "Database details (i.e. hostname, username, password and database name)");
define('_EN_UPGRADE_INTRO_FTP', "A way of uploading files, probably FTP");
define('_EN_UPGRADE_INTRO_LOG', "Log the upgrade process (and errors) to a file ({0})");
define('_EN_UPGRADE_INTRO_LOG_ERROR', "Note: If you want to log the upgrade process (and errors) of the installation to a file you first need to set write-access permissions to the ({0}) directory and then refresh this page with your browser.");

/* Authentication */
define('_EN_UPGRADE_AUTH_PATH_INFO', "To make sure that you are really the owner of this site, please create a file called <strong>{0}</strong> in your Jaws upgrade directory (<strong>{1}</strong>).");
define('_EN_UPGRADE_AUTH_UPLOAD', "You can upload the file in the same way you uploaded your Jaws.");
define('_EN_UPGRADE_AUTH_KEY_INFO', "The file should contain the code shown in the box below, and nothing else.");
define('_EN_UPGRADE_AUTH_ENABLE_SECURITY', "Enable secure upgrade (Powered by RSA)");
define('_EN_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "Error in RSA key generation. please try again.");
define('_EN_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "Error in RSA key generation. No available any math extension.");
define('_EN_UPGRADE_AUTH_ERROR_KEY_FILE', "Your key file ({0}) was not found, please make sure you created it, and the web server is able to read it.");
define('_EN_UPGRADE_AUTH_ERROR_KEY_MATCH', "The key found ({0}), doesn't match the one below, please check that you entered the key correctly.");

/* Requirements */
define('_EN_UPGRADE_REQ_REQUIREMENT', "Requirement");
define('_EN_UPGRADE_REQ_OPTIONAL', "Optional but recommended");
define('_EN_UPGRADE_REQ_RECOMMENDED', "Recommended");
define('_EN_UPGRADE_REQ_DIRECTIVE', "Directive");
define('_EN_UPGRADE_REQ_ACTUAL', "Actual");
define('_EN_UPGRADE_REQ_RESULT', "Result");
define('_EN_UPGRADE_REQ_PHP_VERSION', "PHP version");
define('_EN_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_EN_UPGRADE_REQ_DIRECTORY', "{0} directory");
define('_EN_UPGRADE_REQ_EXTENSION', "{0} extension");
define('_EN_UPGRADE_REQ_FILE_UPLOAD', "File Uploads");
define('_EN_UPGRADE_REQ_SAFE_MODE', "Safe mode");
define('_EN_UPGRADE_REQ_READABLE', "Readable");
define('_EN_UPGRADE_REQ_WRITABLE', "Writable");
define('_EN_UPGRADE_REQ_OK', "OK");
define('_EN_UPGRADE_REQ_BAD', "BAD");
define('_EN_UPGRADE_REQ_OFF', "Off");
define('_EN_UPGRADE_REQ_ON', "On");
define('_EN_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "The directory {0} are either not readable or writable, please fix the permissions.");
define('_EN_UPGRADE_REQ_RESPONSE_PHP_VERSION', "The minimum PHP version to upgrade Jaws is {0}, therefore you must upgrade your PHP version.");
define('_EN_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "The directories listed below as {0} are either not readable or writable, please fix their permissions.");
define('_EN_UPGRADE_REQ_RESPONSE_EXTENSION', "The {0} extension is necessary for using Jaws.");

/* Database */
define('_EN_UPGRADE_DB_INFO', "You now need to setup your database, which is used to get information of your current database and upgrade it.");
define('_EN_UPGRADE_DB_HOST', "Hostname");
define('_EN_UPGRADE_DB_HOST_INFO', "If you don't know this, it's probably safe to leave it as {0}.");//localhost
define('_EN_UPGRADE_DB_DRIVER', "Driver");
define('_EN_UPGRADE_DB_USER', "Username");
define('_EN_UPGRADE_DB_PASS', "Password");
define('_EN_UPGRADE_DB_IS_ADMIN', "Is DB Admin?");
define('_EN_UPGRADE_DB_NAME', "Database Name");
define('_EN_UPGRADE_DB_PATH', "Database Path");
define('_EN_UPGRADE_DB_PATH_INFO', "Only fill this field out if you like change your database path in SQLite, Interbase and Firebird driver.");
define('_EN_UPGRADE_DB_PORT', "Database Port");
define('_EN_UPGRADE_DB_PORT_INFO', "Only fill this field out if your database is running on an another port then the default is.<br />If you have <strong>no idea</strong> what port the database is running then most likely it's running on the default port and thus we <strong>advice</strong> you to leave this field alone.");
define('_EN_UPGRADE_DB_PREFIX', "Tables Prefix");
define('_EN_UPGRADE_DB_PREFIX_INFO', "Some text that will be placed in front of table names, so you can run more than one Jaws site from the same database, for example <strong>blog_</strong>");
define('_EN_UPGRADE_DB_RESPONSE_PATH', "The database path not exist");
define('_EN_UPGRADE_DB_RESPONSE_PORT', "The port can only be a numeric value");
define('_EN_UPGRADE_DB_RESPONSE_INCOMPLETE', "You must fill in all the fields apart from database path, table prefix and port.");
define('_EN_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "There was a problem connecting to the database, please check the details and try again.");

/* Report */
define('_EN_UPGRADE_REPORT_INFO', "Comparing your installed Jaws version with current {0}");
define('_EN_UPGRADE_REPORT_NOTICE', "Below you will find the versions that this Upgrade system can take care of. Maybe you are running a very old version, so we will take care of the rest.");
define('_EN_UPGRADE_REPORT_NEED', "Requires upgrade");
define('_EN_UPGRADE_REPORT_NO_NEED', "Does not requires upgrade");
define('_EN_UPGRADE_REPORT_NO_NEED_CURRENT', "Does not requires upgrade(is current)");
define('_EN_UPGRADE_REPORT_MESSAGE', "If the upgrade found that your installed Jaws version is old it will upgrade it, if not, it will end.");
define('_EN_UPGRADE_REPORT_NOT_SUPPORTED', "This upgrader can't upgrade versions lower than 0.8.18");
define('_EN_UPGRADE_VER_TO_VER_STEP1', " - First Step");
define('_EN_UPGRADE_VER_TO_VER_STEP2', " - Second Step");
define('_EN_UPGRADE_VER_TO_VER_STEP3', " - Thired Step");

/* Version */
define('_EN_UPGRADE_VER_INFO', "Upgrading from {0} to {1} will");
define('_EN_UPGRADE_VER_NOTES', "<strong>Note:</strong>&nbsp;Once you finish upgrading your Jaws version, other gadgets (like Blog, Phoo, etc) will require to be upgraded. You can do this task by logging in the Control Panel.");
define('_EN_UPGRADE_VER_RESPONSE_GADGET_FAILED', "here was a problem installing core gadget {0}");

/* WriteConfig */
define('_EN_UPGRADE_CONFIG_INFO', "You now need to save your configuration file.");
define('_EN_UPGRADE_CONFIG_SOLUTION', "You can do this in two ways");
define('_EN_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Make <strong>{0}</strong> writable, and hit next, which will allow the installer to save the configuration itself.");
define('_EN_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Copy and paste the contents of the box below into a file and save it as <strong>{0}</strong>");
define('_EN_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "There was an unknown error writing the configuration file.");
define('_EN_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "You need to either make the config directory writable, or create {0} by hand.");

/* Cleanup */
define('_EN_UPGRADE_CLEANUP_INFO', "Jaws gadgets and plugins in 0.9.x is based on different file/directory structure, so you must cleanup outdated files/directories listed below:");
define('_EN_UPGRADE_CLEANUP_ERROR_PERMISSION', "Error occurred while deleting files/directories");
define('_EN_UPGRADE_CLEANUP_NOT_REQUIRED', "There is no outdated file or directory");

/* Finished */
define('_EN_UPGRADE_FINISH_INFO', "You have now finished setting up your website!");
define('_EN_UPGRADE_FINISH_CHOICES', "You now have two choices, you can either <a href=\"{0}\">view your site</a> or <a href=\"{1}\">login to the control panel</a>.");
define('_EN_UPGRADE_FINISH_MOVE_LOG', "Note: If you turned on the logging option at the first stage we suggest you to save it and move it / delete it");
define('_EN_UPGRADE_FINISH_THANKS', "Thank you for using Jaws!");
