<?php
/**
 * Jaws FileMemory class
 *
 * @category    FileMemory
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_SharedSegment
{
    /**
     * file token
     * @var     int     $ftok
     * @access  private
     */
    protected $ftok;

    /**
     * Constructor
     *
     * @access  protected
     * @param   int     $ftok   File token
     * @return  void
     */
    protected function __construct($ftok)
    {
        $this->ftok = $ftok;
    }

    /**
     * An interface for available drivers
     *
     * @access  public
     * @param   mixed   $ftok           File token or name
     * @param   string  $segmentDriver  Cache Driver name
     * @return  mixed   Shared Segment driver object on success otherwise Jaws_Error on failure
     */
    static function getInstance($ftok, $segmentDriver = '')
    {
        $segmentDriver = preg_replace('/[^[:alnum:]_\-]/', '', $segmentDriver);
        if (empty($segmentDriver)) {
            $segmentDriver = 'Memory';
        }
        
        if (!extension_loaded('shmop')) {
            $GLOBALS['log']->Log(JAWS_DEBUG, "Loading 'shmop' shared segment driver failed.");
            $segmentDriver = 'File';
        }
        $className = "Jaws_SharedSegment_$segmentDriver";

        static $instances = array();
        $ftok = is_int($ftok)? $ftok : Jaws_Utils::ftok($ftok, Jaws::getInstance()->instance);
        if (!isset($instances[$ftok])) {
            $instances[$ftok] = new $className($ftok);
        }

        return $instances[$ftok];
    }

    /**
     * Open shared memory block by key
     *
     * @access  public
     * @param   string  $mode   Open mode(a, c, w, n)
     * @param   int     $size   File size
     * @return  mixed   Returns the data or FALSE on failure
     */
    function open($mode = 'a', $size = 0)
    {
        return Jaws_Error::raiseError(
            'open() method not supported by driver.',
            __FUNCTION__
        );
    }

    /**
     * Read data from shared memory block
     *
     * @access  public
     * @param   int     $start      Start position
     * @param   int     $count      Count of read bytes
     * @return  mixed   Returns the data or FALSE on failure
     */
    function read($start = 0, $count = 0)
    {
        return Jaws_Error::raiseError(
            'read() method not supported by driver.',
            __FUNCTION__
        );
    }

    /**
     * Write data into shared memory block
     *
     * @access  public
     * @param   string  $data       Data to write into shared memory block
     * @param   int     $offset     Offset of start writing data
     * @return  mixed   The size of the written data, or FALSE on failure
     */
    function write($data, $offset = 0)
    {
        return Jaws_Error::raiseError(
            'write() method not supported by driver.',
            __FUNCTION__
        );
    }

    /**
     * Close shared memory block
     *
     * @access  public
     * @return  void
     */
    function close()
    {
        return Jaws_Error::raiseError(
            'close() method not supported by driver.',
            __FUNCTION__
        );
    }

    /**
     * Delete shared memory block
     *
     * @access  public
     * @param   int     $ftok   File token or name
     * @return  bool    Returns TRUE on success or FALSE on failure
     */
    function delete($ftok)
    {
        return Jaws_Error::raiseError(
            'delete() method not supported by driver.',
            __FUNCTION__
        );
    }

    /**
     * Lock shared memory block
     *
     * @access  public
     * @param   bool    $state  Lock/Unlock operation
     * @return  void
     */
    function lock($state = true)
    {
        if ($state) {
            Jaws_Mutex::getInstance($this->ftok)->acquire();
        } else {
            Jaws_Mutex::getInstance($this->ftok)->release();
        }
    }

}