<?php
/**
 * Jaws Mutex class
 *
 * @category    Mutex
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
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
     * Creates the Jaws_Lock instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @return  object  Jaws_Lock type object
     */
    static function getInstance()
    {
        static $objMutex;
        if (!isset($objMutex)) {
            if (JAWS_OS_WIN) {
                $file = JAWS_PATH . 'include/Jaws/Mutex/File.php';
                $className = 'Jaws_Mutex_File';
            } else {
                $file = JAWS_PATH . 'include/Jaws/Mutex/Semaphore.php';
                $className = 'Jaws_Mutex_Semaphore';
            }

            include_once($file);
            $objMutex = new $className();
        }

        return $objMutex;
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
        return Jaws_Error::raiseError(
            'acquire() method not supported by driver.',
            __FUNCTION__
        );
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
        unset($this->mutexs[$lkey]);
    }

}