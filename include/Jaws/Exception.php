<?php
/**
 * Jaws Exception
 *
 * @category    Exception
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Exception extends ErrorException
{
    /**
     * Destructor
     *
     * @access  public
     * @return  void
     */
    function __destruct() {
        if (isset($GLOBALS['log'])) {
            $GLOBALS['log']->Log($this->severity, '[' . $this->code . ']: ' . $this->message);
        }
    }

    /**
     * Sets the Exception message
     *
     * @access  public
     * @param   string  $message    Error message
     * @return  void
     */
    function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Sets the Exception code
     *
     * @access  public
     * @param   int     $code   Exception code
     * @return  void
     */
    function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Sets exception severity
     *
     * @access  public
     * @param   int     $severity  Exception severity
     * @return  void
     */
    function setSeverity($severity = E_ERROR)
    {
        $this->severity = $severity;
    }

}