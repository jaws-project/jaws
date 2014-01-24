<?php
/**
 * Declares the common jaws constants
 *
 * @category   Application
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2010-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */

// Jaws components type
define('JAWS_COMPONENT_OTHERS',  0);
define('JAWS_COMPONENT_GADGET',  1);
define('JAWS_COMPONENT_PLUGIN',  2);
define('JAWS_COMPONENT_THEMES',  3);
define('JAWS_COMPONENT_INSTALL', 4);
define('JAWS_COMPONENT_UPGRADE', 5);

// Version of the unpacked Jaws (not the one in registry)
define('JAWS_VERSION', '1.1.0');
define('JAWS_VERSION_CODENAME', 'Security, Ease of Use, Performance, Interoperability, Flexibility');

define('JAWS_EMERG',   1);  // System is unusable
define('JAWS_ALERT',   2);  // Immediate action required
define('JAWS_CRIT',    3);  // Critical conditions
define('JAWS_ERR',     4);  // Error conditions
define('JAWS_ERROR',   4);  // Error conditions
define('JAWS_WARNING', 5);  // Warning conditions
define('JAWS_NOTICE',  6);  // Normal but significant condition
define('JAWS_INFO',    7);  // Informational
define('JAWS_DEBUG',   8);  // debug-level messages

define('ACTION_MODE_NORMAL', 'normal');         // Normal action
define('ACTION_MODE_LAYOUT', 'layout');         // Layout action
define('ACTION_MODE_STANDALONE', 'standalone'); // Standalone action
