<?php
/**
 * Jaws *Nix Lock class
 *
 * @category    Lock
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Lock_Nix extends Jaws_Lock
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
        if (!isset($this->locks[$lname])) {
            $this->locks[$lname] = sem_get(ftok(__FILE__, chr(count($this->locks)+1)));
        }

        return sem_acquire($this->locks[$lname], $nowait);
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
        if (isset($this->locks[$lname])) {
            sem_release($this->locks[$lname]);
            parent::release($lname);
        }
    }

}