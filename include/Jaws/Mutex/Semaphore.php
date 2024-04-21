<?php
/**
 * Jaws Semaphore Mutex class
 *
 * @category    Mutex
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Mutex_Semaphore extends Jaws_Mutex
{
    /**
     * Constructor
     *
     * @access  public
     * @param   int     $lkey   Lock identifier
     * @return  void
     */
    function __construct($lkey)
    {
        parent::__construct($lkey);
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
        if (!isset($this->mutexs[$this->lkey])) {
            $this->mutexs[$this->lkey] = sem_get($this->lkey);
        }

        return sem_acquire($this->mutexs[$this->lkey], $nowait);
    }

    /**
     * Release exclusive access
     *
     * @access  public
     * @return  void
     */
    function release()
    {
        if (isset($this->mutexs[$this->lkey])) {
            sem_release($this->mutexs[$this->lkey]);
            parent::release();
        }
    }

}