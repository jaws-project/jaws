<?php
/**
 * Jaws Semaphore Mutex class
 *
 * @category    Mutex
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2020 Jaws Development Group
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
     * @param   int     $lkey   Lock identifier
     * @param   float   $nowait Wait for the exclusive access to be acquired?
     * @return  bool    True if exclusive access Acquired otherwise False
     */
    function acquire($lkey, $nowait  = false)
    {
        if (!isset($this->mutexs[$lkey])) {
            $this->mutexs[$lkey] = sem_get($lkey);
        }

        return sem_acquire($this->mutexs[$lkey], $nowait);
    }

    /**
     * Release exclusive access
     *
     * @access  public
     * @param   int     $lkey   Lock identifier
     * @return  void
     */
    function release($lkey)
    {
        if (isset($this->mutexs[$lkey])) {
            sem_release($this->mutexs[$lkey]);
            parent::release($lkey);
        }
    }

}