<?php
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
 * @copyright  2005-2014 Jaws Development Group
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
     * Constructor
     *
     * @access  public
     * @param   string  $message    Error message
     * @param   string  $code       Error code
     * @param   int     $level      The severity level of the error.
     * @param   int     $backtrace  Log trace back level
     * @return  void
     */
    function Jaws_Error($message, $code = 0, $level = JAWS_ERROR_ERROR, $backtrace = 0)
    {
        $this->_Message = $message;
        $this->_Code    = $code;
        $this->_Level   = $level;
        if ($backtrace >= 0) {
            $backtrace++;
            $GLOBALS['log']->Log($level, '[' . $code . ']: ' . $message, $backtrace);
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
        if ($backtrace >= 0) {
            $backtrace++;
        }
        $objError = new Jaws_Error($message, $code, $level, $backtrace);
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
        // Set Headers
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        if ($backtrace >= 0) {
            $backtrace++;
            $GLOBALS['log']->Log(JAWS_ERROR_FATAL, $message, $backtrace);
        }
        //Get content
        $content = file_get_contents(JAWS_PATH . 'gadgets/ControlPanel/Templates/FatalError.html');
        $content = str_replace('{{message}}', $message, $content);
        jaws()->http_response_code($http_response_code);
        terminate($content, $http_response_code, '', false);
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