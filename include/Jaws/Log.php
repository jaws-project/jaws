<?php
/**
 * Class to save entries in the log (screen, syslog, logdb, etc)
 *
 * @category   Log
 * @package    Core
 * @author     Ivan Chavero <imcsk8@gluch.org.mx>
 * @author     Jorge A Gallegos <kad@gulags.org>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Log
{
    const level = array(
        'emergency' => JAWS_EMERGENCY,
        'alert'     => JAWS_ALERT,
        'critical'  => JAWS_CRITICAL,
        'error'     => JAWS_ERROR,
        'warning'   => JAWS_WARNING,
        'notice'    => JAWS_NOTICE,
        'info'      => JAWS_INFO,
        'debug'     => JAWS_DEBUG,
    );

    /**
     * Log activated and level value
     *  0   Disabled
     *  1   Emergency log level
     *  2   Alert log and utmost levels
     *  3   Critical log and utmost levels
     *  4   Error log and utmost levels
     *  5   Warning log and utmost levels
     *  6   Notice log and utmost levels
     *  7   Info log and utmost levels
     *  8   Debug log and utmost levels
     *
     * @var mixed
     * @access  private
     */
    var $_LogActivated = 0;

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
     * @var     float
     * @access  private
     */
    var $_StartTime = 0;

    /**
     * The stack of messages
     *
     * @var     string
     * @access  private
     * @see     GetMessageStack()
     */
    var $_MessageStack;

    /**
     * Information about the module
     *
     * @access  public
     * @param   int     $activated  Logging priority level
     * @param   string  $logger     Logger method
     * @return  void
     */
    function __construct($activated = 0, $logger = null)
    {
        $this->_MessageStack = '';
        $this->_LogActivated = (int)$activated;

        if (!defined('LOGGER_METHOD')) {
            define('LOGGER_METHOD', 'LogToWindow');
        }
    }

    /**
     * Return diffrence between current Unix timestamp with start time
     *
     * @access  public
     * @return  float   Execution time
     */
    function ExecTime()
    {
        return microtime(true) - $this->_StartTime;
    }

    /**
     * Return memory usage
     *
     * @access  public
     * @return  float   Memory usage
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
     * @return  void
     */
    function Start()
    {
        $this->_StartTime = microtime(true);
        $this->Log(JAWS_INFO, '[Jaws Log Start]');
    }

    /**
     * Put end string at ending of log
     *
     * @access  public
     * @return  void
     */
    function End()
    {
        $this->Log(JAWS_INFO, 'Memory Usage: ' . $this->MemUsage() . ' KB');
        $this->Log(JAWS_INFO, 'Page was generated in '. $this->ExecTime() . ' seconds');
        $this->Log(JAWS_INFO, '[Jaws Log End]');
        $this->LogStackToScreen();
    }

    /**
     * This is the only function that is called from an instance
     * it receives the facility and identifies it on the registry
     * then takes the method and the options and execute it.
     * if the facility does not exist we use the LogToScreen method
     * and show a unknown facility message
     *
     * @access  public
     * @param   string  $priority   The severity level of log
     * @param   string  $msg        Message to log
     * @param   mixed   $backtrace  Trace array or back trace level
     */
    function Log($priority, $msg, $backtrace = 0)
    {
        if ($priority > $this->_LogActivated) {
            return;
        }

        if (is_array($backtrace)) {
            $file = $backtrace['file'];
            $line = $backtrace['line'];
        } else {
            // PHP >= 5.4.0
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $backtrace + 1);
            $file = @$trace[$backtrace]['file'];
            $line = @$trace[$backtrace]['line'];
        }

        $logLevel = array_search($priority, self::level);
        $method = LOGGER_METHOD;
        $this->$method($logLevel, $file, $line, trim($msg));
    }

    /**
     * This function prints a variable in a human readable form to the log method specified
     *
     * @access  public
     * @param   $mixed mixed Object to display
     * @return  void
     */
    function VarDump($mixed = null)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        } else { //PHP >= 5.3.6
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        if (@$trace[1]['function'] == '_log_var_dump') {
            $file = @$trace[1]['file'];
            $line = @$trace[1]['line'];
        } else {
            $file = @$trace[2]['file'];
            $line = @$trace[2]['line'];
        }

        ob_start();
        call_user_func_array('var_dump', func_get_args());
        $content = ob_get_contents();
        ob_end_clean();
        $method = LOGGER_METHOD;
        $this->$method('debug', $file, $line, trim($content));
    }

    /**
     * Logs the message to a file specified on the dest parameter
     *
     * @access  public
     * @param   string  $logLevel   Log level string
     * @param   string  $file       Filename
     * @param   string  $line       File line number
     * @param   string  $msg        Message to log
     * @return  void
     */
    function LogToFile($logLevel, $file, $line, $msg)
    {
        if (!defined('LOGGER_METHOD_FILE_PATH')) {
            define('LOGGER_METHOD_FILE_PATH', ROOT_DATA_PATH . 'logs/');
        }

        $logfile = LOGGER_METHOD_FILE_PATH . ".jaws.$logLevel";

        // log file rotation
        if (defined('LOGGER_METHOD_FILE_SIZE') && @filesize($logfile) >= LOGGER_METHOD_FILE_SIZE) {
            Jaws_FileManagement_File::rename($logfile, $logfile. '.'. time());
        }

        if (false !== $fh = @fopen($logfile, 'a+')) {
            fwrite($fh, $this->getLogString($logLevel, $file, $line, $msg) . "\n");
            fclose($fh);
        }
    }

    /**
     * Logs the message to syslog
     *
     * @access  public
     * @param   string  $logLevel   Log level string
     * @param   string  $file       Filename
     * @param   string  $line       File line number
     * @param   string  $msg        Message to log
     */
    function LogToSyslog($logLevel, $file, $line, $msg)
    {
        if (!defined('LOGGER_METHOD_SYSLOG_INDENT')) {
            define('LOGGER_METHOD_SYSLOG_INDENT', 'Jaws_Log');
        }

        openlog(LOGGER_METHOD_SYSLOG_INDENT, LOG_PID | LOG_PERROR, LOG_LOCAL0);
        syslog(self::level[$logLevel], $msg);
        closelog();
    }

    /**
     * dump the messages into the FireBug extension
     *
     * @access  public
     * @param   string  $logLevel   Log level string
     * @param   string  $file       Filename
     * @param   string  $line       File line number
     * @param   string  $msg        Message to log
     * @return  void
     */
    function LogToFirebug($logLevel, $file, $line, $msg)
    {
        switch($logLevel) {
            case 'emergency':
            case 'alert':
            case 'critical':
            case 'error':
                $console_method = 'error';
                break;

            case 'notice':
            case 'info':
                $console_method = 'info';
                break;

            case 'warning':
                $console_method = 'warn';
                break;

            case 'debug':
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
     * @param   string  $logLevel   Log level string
     * @param   string  $file       Filename
     * @param   string  $line       File line number
     * @param   string  $msg        Message to log
     * @return  void
     */
    function LogToApache($logLevel, $file, $line, $msg)
    {
        switch ($priority){
            case 'error':
            case 'warning':
                $error_level = E_USER_WARNING;
                break;

            default:
                $error_level = E_USER_NOTICE;
                break;
        }
        trigger_error($this->getLogString($logLevel, $file, $line, $msg), $error_level);
    }


    /**
     * put the message into a message stack
     * originally it was an array but i think that a
     * flat variable should do
     *
     * @access  public
     * @param   string  $logLevel   Log level string
     * @param   string  $file       Filename
     * @param   string  $line       File line number
     * @param   string  $msg        Message to log
     * @return  void
     */
    function LogToWindow($logLevel, $file, $line, $msg)
    {
        $this->_MessageStack = $this->_MessageStack . "\n" . $this->getLogString($logLevel, $file, $line, $msg);
    }

    /**
     * Get the message stack
     * whe should use it like this:
     * $this->Log(JAWS_DEBUG,$this->GetMessageStack);
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
     * @param   string  $logLevel   Log level string
     * @param   string  $file       Filename
     * @param   string  $line       File line number
     * @param   string  $msg        Message to log
     * @return  string  The message already prepared to be logged(parsed)
     */
    private function getLogString($logLevel, $file, $line, $msg)
    {
        $time = date('Y-m-d H:i:s');
        $exec = substr($this->ExecTime(), 0, 10);
        $tmem = $this->MemUsage().'KB';
        return '['. strtoupper($logLevel).']'.
            '['. JAWS_SCRIPT.",{$_SERVER['REQUEST_METHOD']}]".
            "[$time, $exec, $tmem][$file,$line]:\n".$msg;
    }

    /**
     * Parse the stack and give it a nice format
     *
     * @access  private
     * @return  void
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
     * @return  void
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
            switch(LOGGER_METHOD) {
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
 * @access  public
 * @param   string  $priority  The severity level of log
 * @param   string  $msg       Message to log
 * @param   int     $backtrace Log trace back level
 * @return  void
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
 * @access  public
 * @param   $mixed mixed Object to display
 * @return  void
 */
function _log_var_dump($mixed = null)
{
    if (isset($GLOBALS['log'])) {
        call_user_func_array(array($GLOBALS['log'], 'VarDump'), func_get_args());
    }
}
