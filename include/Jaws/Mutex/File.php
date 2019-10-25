<?php
/**
 * Jaws File Mutex class
 *
 * @category    Mutex
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2020 Jaws Development Group
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
     * lock files path
     * @var     string  $lockPath
     * @access  private
     */
    private $lockPath;

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct()
    {
        $this->lockPath =
            rtrim(sys_get_temp_dir(), '/\\') . '/' .
            $this->lockPrefix . Jaws::getInstance()->instance . '_';
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
            $this->mutexs[$lkey] = fopen(
                $this->lockPath. (string)$lkey,
                'a+'
            );
        }

        while (!($lock = flock($this->mutexs[$lkey], LOCK_EX | LOCK_NB)) && !$nowait) {
            //Exclusive access not acquired, try again
            usleep(mt_rand(0, 100)); // 0-100 microseconds
        }

        return $lock;
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
            flock($this->mutexs[$lkey], LOCK_UN);
            fclose($this->mutexs[$lkey]);
            parent::release($lkey);
        }
    }

}