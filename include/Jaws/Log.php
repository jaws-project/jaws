<?php
/**
 * Class to save entries in the log (screen, syslog, logdb, etc)
 *
 * @category   Log
 * @package    Core
 * @author     Ivan Chavero <imcsk8@gluch.org.mx>
 * @author     Jorge A Gallegos <kad@gulags.org>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
define('JAWS_LOG_EMERG',   1); /* system is unusable */
define('JAWS_LOG_ALERT',   2); /* action must be taken immediately */
define('JAWS_LOG_CRIT',    3); /* critical conditions */
define('JAWS_LOG_ERR',     4); /* error conditions */
define('JAWS_LOG_ERROR',   4); /* error conditions */
define('JAWS_LOG_WARNING', 5); /* warning conditions */
define('JAWS_LOG_NOTICE',  6); /* normal but significant condition */
define('JAWS_LOG_INFO',    7); /* informational */
define('JAWS_LOG_DEBUG',   8); /* debug-level messages */
define('Jaws_LogDefaultMethod', 'LogToWindow'); /* default log method */
define('Jaws_LogDefaultOption', '');            /* default log option */

class Jaws_Log
{
    /**
     * Log activated and level value 
     *  0   Emergency log level
     *  1   Alert log and utmost levels
     *  2   Critical log and utmost levels
     *  3   Error log and utmost levels
     *  4   Warning log and utmost levels
     *  5   Notice log and utmost levels
     *  6   Info log and utmost levels
     *  7   Debug log and utmost levels
     * @var mixed
     * @access  private
     */
    var $_LogActivated = false;

    /**
     * The equivalent string of log priorities
     *
     * @var     array
     * @access  private
     */
    var $_Log_Priority_Str = array(
        'LOG_EMERG',
        'LOG_ALERT',
        'LOG_CRIT',
        'LOG_ERROR',
        'LOG_WARNING',
        'LOG_NOTICE',
        'LOG_INFO',
        'LOG_DEBUG'
    );

    /**
     * The start time(microseconds)
     *
     * @var     double
     * @access  private
     */
    var $_StartTime = 0;

    /**
     * The logger
     *
     * @var     string
     * @access  private
     */
    var $_Method;

    /**
     * The logger options
     *
     * @var     array
     * @access  private
     */
    var $_Options;

    /**
     * The stack of messages
     *
     * @var    string
     * @access private
     * @see    GetMessageStack()
     */
    var $_MessageStack;

    /**
     * Information about the module
     *
     * @access  public
     */
    function Jaws_Log($activated = false, $logger = null)
    {
        $this->_MessageStack = '';
        $this->_LogActivated = $activated;
        if (!empty($logger)) {
            $this->_Method = $logger['method'];
            if (isset($logger['options'])) {
                $this->_Options = $logger['options'];
            }
        }

        if (empty($this->_Method)) {
            $this->_Method = Jaws_LogDefaultMethod;
        }

        if (empty($this->_Options)) {
            $this->_Options = Jaws_LogDefaultOption;
        }

    }

    /**
     * Return diffrence between current Unix timestamp with start time
     *
     * @access  public
     */
    function ExecTime()
    {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        return (double)($mtime[0] + $mtime[1] - $this->_StartTime);
    }

    /**
     * Return memory usage
     *
     * @access  public
     */
    function MemUsage()
    {
        $mem = 0;
        if (function_exists('memory_get_usage')) {
            $mem = round(memory_get_usage() / 1024);
        }
        return $mem;
    }

    /**
     * Put start string at beginning of log
     *
     * @access  public
     */
    function Start()
    {
        $this->_StartTime = $this->ExecTime();
        $this->Log(JAWS_LOG_INFO, '[Jaws Log Start]');
    }

    /**
     * Put end string at ending of log
     *
     * @access  public
     */
    function End()
    {
        $this->Log(JAWS_LOG_INFO, 'Memory Usage: ' . $this->MemUsage() . ' KB');
        $this->Log(JAWS_LOG_INFO, 'Page was generated in '. $this->ExecTime() . ' seconds');
        $this->Log(JAWS_LOG_INFO, '[Jaws Log End]');
        $this->LogStackToScreen();
    }

    /**
     * This is the only function that is called from an instance
     * it recieves the facility and identifies it on the registry
     * then takes the method and the options and execute it.
     * if the facility does not exist we use the LogToScreen method
     * and show a unknown facilty message
     *
     * @access  public
     * @param   string  $priority  The severity level of log
     * @param   string  $msg       Message to log
     * @param   integer $backtrace Log trace back level
     */
    function Log($priority, $msg, $backtrace = 0)
    {
        if ($this->_LogActivated === false || $priority > (int)$this->_LogActivated) {
            return;
        }

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $backtrace + 1);
        } elseif (version_compare(PHP_VERSION, '5.3.6', '>=')) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } elseif (version_compare(PHP_VERSION, '5.2.5', '>=')) {
            $trace = debug_backtrace(false);
        } else {
            $trace = debug_backtrace();
        }

        $file = $trace[$backtrace]['file'];
        $line = $trace[$backtrace]['line'];

        $method = $this->_Method;
        $msg = "[$file,$line]: ". trim($msg);
        $this->$method($priority, $msg, $this->_Options);
    }

    /**
     * This function prints a variable in a human readable form to the log method specified
     *
     * @access public
     * @param  $mixed mixed Object to display
     */
    function VarDump($mixed = null)
    {
        ob_start();
        var_dump($mixed);
        $content = ob_get_contents();
        ob_end_clean();
        $method = $this->_Method;
        $this->$method(JAWS_LOG_DEBUG, "\n" . trim($content), $this->_Options);
    }

    /**
     * Logs the message to a file especified on the dest parameter
     *
     * @access  public
     * @param   string  $priority  How to log
     * @param   string  $msg       Message to log
     * @param   string  $opts      Options(log file naem, ...)
     */
    function LogToFile($priority, $msg, $opts)
    {
        if (isset($opts['file'])) {
            $logfile = $opts['file'];
        } else {
            trigger_error("You need to set at least the filename for Jaws_Log::LogToFile", E_USER_ERROR);
        }

        $fh = fopen($logfile, 'a+');
        fwrite($fh, $this->SetLogStr($priority, $msg) . "\n");
        fclose($fh);
    }

    /**
     * Logs the message to syslog
     *
     * @access  public
     * @param   string  $priority  How to log
     * @param   string  $msg       Message to log
     * @param   string  $opt       Some options
     */
    function LogToSyslog($priority, $msg, $opt)
    {
        @define_syslog_variables();
        $indent = 'Jaws_Log';
        if (isset($opt['indent'])) {
            $indent = $opt['indent'];
        }
        openlog($indent, LOG_PID | LOG_PERROR, LOG_LOCAL0);
        syslog((int)$priority, $msg);
        closelog();
    }

    /**
     * dump the messages into the FireBug extension
     *
     * @access  public
     * @param   string  $priority   How to log
     * @param   string  $msg        Message to log
     * @param   string  $opt        Some options
     */
    function LogToFirebug($priority, $msg, $opt)
    {
        switch($priority) {
            case JAWS_LOG_EMERG:
            case JAWS_LOG_ALERT:
            case JAWS_LOG_CRIT:
            case JAWS_LOG_ERROR:
                $console_method = 'error';
                break;
            case JAWS_LOG_NOTICE:
            case JAWS_LOG_INFO:
                $console_method = 'info';
                break;
            case JAWS_LOG_WARNING:
                $console_method = 'warn';
                break;
            case JAWS_LOG_DEBUG:
                $console_method = 'debug';
                break;
        }

        $now = date('Y-m-d H:i:s').', '.$this->ExecTime();
        $msg = str_replace("\r\n", "\n", $msg);
        $msg = str_replace("\n", "\\n\\\n", $msg);
        $msg = str_replace('"', '\\"', $msg);

        $this->_MessageStack = $this->_MessageStack . "\n" . 'console.' .
                               $console_method . '("[' . $now . ']\n' . $msg . '");';
    }

    /**
     * prints the message to the apache error log file
     *
     * @access  public
     * @param   string  $priority  How to log
     * @param   string  $msg       Message to log
     * @param   string  $opt       Some options
     */
    function LogToApache($priority, $msg, $opt)
    {
        switch ($priority){
            case JAWS_LOG_ERROR:
            case JAWS_LOG_WARNING:
                $error_level = E_USER_WARNING;
                break;
            default:
                $error_level = E_USER_NOTICE;
                break;
        }
        trigger_error($this->SetLogStr($priority, $msg), $error_level);
    }


    /**
     * put the message into a message stack
     * originally it was an array but i think that a
     * flat variable should do
     *
     * @access  public
     * @param   string  $priority  How to log
     * @param   string  $msg       Message to log
     * @param   string  $opt       Some options
     */
    function LogToWindow($priority, $msg, $opt)
    {
        $this->_MessageStack = $this->_MessageStack . "\n" . $this->SetLogStr($priority, $msg);
    }

    /**
     * Get the message stack
     * whe should use it like this:
     * $this->Log(JAWS_LOG_DEBUG,$this->GetMessageStack);
     *
     * @access  public
     * @return  string the Stack of messages
     */
    function GetMessageStack()
    {
        return $this->_MessageStack;
    }

    /**
     * Formats the message to be printed.
     * appends the date and the priority to the message
     *
     * @access  private
     * @param   string  $priority  How to log
     * @param   string  $msg       Message to log
     * @return  string  The message already prepared to be logged(parsed)
     */
    function SetLogStr($priority, $msg)
    {
        $time = date('Y-m-d H:i:s');
        $exec = substr($this->ExecTime(), 0, 10);
        $tmem = str_pad($this->MemUsage().'KB', 8, '-', STR_PAD_LEFT);
        $type = str_pad($this->_Log_Priority_Str[$priority - 1], 11, '-');
        return "[$type]:[$time, $exec, $tmem]:". $msg;
    }

    /**
     * Parse the stack and give it a nice format
     *
     * @access  private
     * @return  string   a HTML with the log
     */
    function StackToWindow()
    {
        print "<script type='text/javascript'>\n";
        print "JawsLogWin = window.open('', 'JawsLogWin', 'toolbar=no,scrollbars,width=600,height=400');\n";
        print "JawsLogWin.document.writeln('<html>');\n";
        print "JawsLogWin.document.writeln('<head>');\n";
        print "JawsLogWin.document.writeln('<title>Jaws Log Window</title>');\n";
        print "JawsLogWin.document.writeln('</head>');\n";
        print "JawsLogWin.document.writeln('<body>');\n";
        $l = preg_split('/\n/', $this->_MessageStack);
        foreach ($l as $line) {
            print "JawsLogWin.document.writeln('".addslashes($line)."<br/>');\n";
        }
        print "JawsLogWin.document.writeln('</body>');\n";
        print "JawsLogWin.document.writeln('</html>');\n";
        print "</script>\n";
    }

    /**
     * Gives the stack in Firebug's favor format
     *
     * @access  private
     * @return  string  JavaScript stuff
     */
    function StackToFirebug()
    {
        print '<script type="text/javascript">';
        print "\nif (('console' in window) || ('firebug' in console)) {\n";
        $l = preg_split('/\n/', $this->_MessageStack);
        foreach ($l as $line) {
            print $line."\n";
        }
        print "\n}\n";
        print "</script>";
    }

    /**
     * prints the message stack to the screen
     *
     * @access  private
     * @return  string  The stack of messages
     */
    function LogStackToScreen()
    {
        if (!empty($this->_MessageStack)) {
            switch($this->_Method) {
                case 'LogToWindow':
                    $this->StackToWindow();
                    break;
                case 'LogToFirebug':
                    $this->StackToFirebug();
                    break;
            }
        }
    }

}

/**
 * Convenience function to log.
 *
 * Passes it's arguments to Jaws_Log::Log to do the actual log.
 *
 * @access public
 * @param   string  $priority  The severity level of log
 * @param   string  $msg       Message to log
 * @param   integer $backtrace Log trace back level
 */
function _log($priority, $msg, $backtrace = 0)
{
    if (isset($GLOBALS['log'])) {
        $GLOBALS['log']->Log($priority, $msg, $backtrace + 1);
    }
}

/**
 * Convenience function to VarDump.
 *
 * Passes it's arguments to Jaws_Log::VarDump to do the actual dump.
 *
 * @access public
 * @param  $mixed mixed Object to display
 */
function _log_var_dump($mixed = null)
{
    if (isset($GLOBALS['log'])) {
        $GLOBALS['log']->VarDump($mixed);
    }
}
