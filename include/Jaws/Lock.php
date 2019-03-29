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
     * locks handles array
     * @var     array   $locks
     * @access  protected
     */
    protected $locks = array();

    /**
     * Creates the Jaws_Lock instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @return  object  Jaws_Lock type object
     */
    static function getInstance()
    {
        static $objLock;
        if (!isset($objLock)) {
            if (JAWS_OS_WIN) {
                $file = JAWS_PATH . 'include/Jaws/Lock/Win.php';
                $className = 'Jaws_Lock_Win';
            } else {
                $file = JAWS_PATH . 'include/Jaws/Lock/Nix.php';
                $className = 'Jaws_Lock_Nix';
            }

            include_once($file);
            $objLock = new $className();
        }

        return $objLock;
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
        return Jaws_Error::raiseError(
            'acquire() method not supported by driver.',
            __FUNCTION__
        );
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
        unset($this->locks[$lname]);
    }

}