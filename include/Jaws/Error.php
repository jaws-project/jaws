<?php
define('JAWS_ERROR_DEBUG',   8); //LOG_DEBUG   = 8
define('JAWS_ERROR_INFO',    7); //LOG_INFO    = 7
define('JAWS_ERROR_NOTICE',  6); //LOG_NOTICE  = 6
define('JAWS_ERROR_WARNING', 5); //LOG_WARNING = 5
define('JAWS_ERROR_ERROR',   4); //LOG_ERR     = 4
define('JAWS_ERROR_FATAL',   3); //LOG_CRIT    = 3

/**
 * Manage Jaws Errors
 *
 * @category   Error
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Error
{
    /**
     * Error message
     *
     * @access  private
     * @var     string
     * @see     GetMessage()
     */
    private $_Message;

    /**
     * Error code
     *
     * @access  private
     * @var     string
     * @see     GetCode()
     */
    private $_Code;

    /**
     * The severity of the error.
     *
     * @access  private
     * @var     string
     * @see     GetLevel()
     */
    private $_Level;

    /**
     * Log trace back level
     *
     * @access  private
     * @var     array
     */
    private $_Backtrace = array();

    /**
     * Constructor
     *
     * @access  public
     * @param   string  $message    Error message
     * @param   string  $code       Error code
     * @param   int     $level      The severity level of the error.
     * @param   int     $backtrace  Log trace back level
     * @return  void
     */
    function __construct($message, $code = 0, $level = JAWS_ERROR_ERROR, $backtrace = 0)
    {
        $this->_Message = $message;
        $this->_Code    = $code;
        $this->_Level   = $level;

        // PHP >= 5.4.0
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $backtrace + 1);
        $this->_Backtrace = @$trace[$backtrace];
    }

    /**
     * Destructor
     *
     * @access  public
     * @return  void
     */
    function __destruct() {
        if (isset($GLOBALS['log'])) {
            $GLOBALS['log']->Log($this->_Level, '[' . $this->_Code . ']: ' . $this->_Message, $this->_Backtrace);
        }
    }

    /**
     * Creates the Jaws_Error instance
     *
     * @access  public
     * @param   string  $message   Error message
     * @param   string  $code      Error code
     * @param   int     $level     The severity level of the error.
     * @param   int     $backtrace Log trace back level
     * @return  object  Jaws_Error object
     */
    static function &raiseError($message, $code = 0, $level = JAWS_ERROR_ERROR, $backtrace = 0)
    {
        $objError = new Jaws_Error($message, $code, $level, $backtrace + 1);
        return $objError;
    }

    /**
     * Sets the Error message
     *
     * @access  public
     * @param   string  $message    Error message
     * @return  void
     */
    function SetMessage($message)
    {
        $this->_Message = $message;
    }

    /**
     * Returns the Error message
     *
     * @access  public
     * @return  string  Error message
     */
    function GetMessage()
    {
        return $this->_Message;
    }

    /**
     * Returns the Error code
     *
     * @access  public
     * @return  string  Error code
     */
    function GetCode()
    {
        return $this->_Code;
    }

    /**
     * Returns the error level.
     *
     * @access  public
     * @return  int     The severity level.
     */
    function GetLevel()
    {
        return $this->_Level;
    }

    /**
     * Sets the error level
     *
     * @access  public
     * @param   int     $level  Error level
     * @return  void
     */
    function SetLevel($level = JAWS_ERROR_ERROR)
    {
        $this->_Level = $level;
    }

    /**
     * Validates if an input is a error or not
     *
     * @access  public
     * @param   mixed   $input  Input to validate(can be boolean, object, numeric, etc)
     * @return  bool    True if input is a Jaws_Error, false if not.
     */
    static function IsError(&$input)
    {
        return(bool)(is_object($input) &&(strtolower(get_class($input)) == 'jaws_error'));
    }

    /**
     * Prints a Fatal Error
     *
     * @access  public
     * @param   string  $message            Error message
     * @param   int     $backtrace          Log trace back level
     * @param   int     $http_response_code HTTP response code
     * @return  void
     */
    static function Fatal($message, $backtrace = 0, $http_response_code = 500)
    {
        if ($backtrace >= 0) {
            $backtrace++;
            $GLOBALS['log']->Log(JAWS_ERROR_FATAL, $message, $backtrace);
        }
        //Get content
        $content = file_get_contents(ROOT_JAWS_PATH . 'gadgets/ControlPanel/Templates/FatalError.html');
        $content = str_replace('{{message}}', $message, $content);
        terminate($content, $http_response_code);
    }

    /**
     * Overloading __get magic method
     *
     * @access  private
     * @param   string  $property   Property name
     * @return  object  Jaws_Error object
     */
    function __get($property)
    {
        return $this;
    }

    /**
     * Overloading magic method
     *
     * @access  private
     * @param   string  $method  Method name
     * @param   string  $params  Method parameters
     * @return  mixed   Jaws_Error object
     */
    function __call($method, $params)
    {
        return $this;
    }

}