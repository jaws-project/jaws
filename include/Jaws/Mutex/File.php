<?php
/**
 * Jaws File Mutex class
 *
 * @category    Mutex
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Mutex_File extends Jaws_Mutex
{
    /**
     * lock files prefix
     * @var     string  $lockPrefix
     * @access  private
     */
    private $lockPrefix = 'lock_';

    /**
     * lock files directory
     * @var     string  $lockDirectory
     * @access  private
     */
    private $lockDirectory;

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct()
    {
        $this->lockDirectory = rtrim(sys_get_temp_dir(), '/\\');
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
            $this->mutexs[$lname] = fopen($this->lockDirectory . '/'. $this->lockPrefix . md5($lname), 'a+');
        }

        while (!($lock = flock($this->mutexs[$lname], LOCK_EX | LOCK_NB)) && !$nowait) {
            //Exclusive access not acquired, try again
            usleep(mt_rand(0, 100)); // 0-100 microseconds
        }

        return $lock;
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
            flock($this->mutexs[$lname], LOCK_UN);
            fclose($this->mutexs[$lname]);
            parent::release($lname);
        }
    }

}