<?php
/**
 * Jaws Lock class
 *
 * @category    Lock
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Lock
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
     * locks handles array
     * @var     array   $locks
     * @access  private
     */
    private $locks = array();


    /**
     * Constructor
     *
     * @access  private
     * @return  void
     */
    private function __construct()
    {
        $this->lockDirectory = rtrim(sys_get_temp_dir(), '/\\');
    }

    /**
     * Creates the Jaws_Lock instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @return  object returns the instance
     */
    static function getInstance()
    {
        static $objLock;
        if (!isset($objLock)) {
            $objLock = new Jaws_Lock();
        }

        return $objLock;
    }

    /**
     * Acquire exclusive access
     *
     * @access  public
     * @param   string  $lname      Lock unique name
     * @param   float   $timeout    Timeout(second) for cancel trying acquire exclusive access
     * @return  bool    True if exclusive access Acquired otherwise False
     */
    function acquire($lname, $timeout = 0)
    {
        if (!isset($this->locks[$lname])) {
            $this->locks[$lname] = fopen($this->lockDirectory . '/'. $this->lockPrefix . md5($lname), 'a+');
        }

        $endTime = microtime(true) + $timeout;
        while (!($lock = flock($this->locks[$lname], LOCK_EX | LOCK_NB)) && (microtime(true) < $endTime)) {
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
        if (isset($this->locks[$lname])) {
            flock($this->locks[$lname], LOCK_UN);
            fclose($this->locks[$lname]);
            unset($this->locks[$lname]);
        }
    }

}