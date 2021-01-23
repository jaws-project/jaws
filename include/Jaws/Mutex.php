<?php
/**
 * Jaws Mutex class
 *
 * @category    Mutex
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Mutex
{
    /**
     * Mutexs handles array
     * @var     array   $mutex
     * @access  protected
     */
    protected $mutexs = array();

    /**
     * file token
     * @var     int     $lkey   Lock identifier
     * @access  private
     */
    protected $lkey;

    /**
     * Constructor
     *
     * @access  protected
     * @param   int     $lkey   Lock identifier
     * @return  void
     */
    protected function __construct($lkey)
    {
        $this->lkey = $lkey;
    }

    /**
     * Creates the Jaws_Lock instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @param   int     $lkey   Lock identifier
     * @return  object  Jaws_Lock type object
     */
    static function getInstance($lkey)
    {
        static $objMutex;
        if (!isset($objMutex)) {
            if (function_exists('sem_acquire')) {
                $className = 'Jaws_Mutex_Semaphore';
            } else {
                $className = 'Jaws_Mutex_File';
            }

            $lkey = is_int($lkey)? $lkey : Jaws_Utils::ftok($lkey, Jaws::getInstance()->instance);
            $objMutex = new $className($lkey);
        }

        return $objMutex;
    }

    /**
     * Acquire exclusive access
     *
     * @access  public
     * @param   float   $nowait Wait for the exclusive access to be acquired?
     * @return  bool    True if exclusive access Acquired otherwise False
     */
    function acquire($nowait  = false)
    {
        return Jaws_Error::raiseError(
            'acquire() method not supported by driver.',
            __FUNCTION__
        );
    }

    /**
     * Release exclusive access
     *
     * @access  public
     * @return  void
     */
    function release()
    {
        unset($this->mutexs[$this->lkey]);
    }

}