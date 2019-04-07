<?php
/**
 * Jaws Semaphore Mutex class
 *
 * @category    Mutex
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Mutex_Semaphore extends Jaws_Mutex
{
    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct()
    {
    }

    /**
     * Acquire exclusive access
     *
     * @access  public
     * @param   string  $lname      Lock identifier
     * @param   float   $nowait     Wait for the exclusive access to be acquired?
     * @return  bool    True if exclusive access Acquired otherwise False
     */
    function acquire($lname, $nowait  = false)
    {
        if (!isset($this->mutexs[$lname])) {
            $this->mutexs[$lname] = sem_get(ftok(__FILE__, chr(count($this->mutexs)+1)));
        }

        return sem_acquire($this->mutexs[$lname], $nowait);
    }

    /**
     * Release exclusive access
     *
     * @access  public
     * @param   string  $lname  Lock unique name
     * @return  void
     */
    function release($lname)
    {
        if (isset($this->mutexs[$lname])) {
            sem_release($this->mutexs[$lname]);
            parent::release($lname);
        }
    }

}