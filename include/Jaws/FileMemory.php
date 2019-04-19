<?php
/**
 * Jaws FileMemory class
 *
 * @category    FileMemory
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_FileMemory
{
    /**
     * file token
     * @var     int     $ftoken
     * @access  private
     */
    private $ftoken;

    /**
     * shared memory resource handle
     * @var     resource    $shmkey
     * @access  private
     */
    private $shmkey;

    /**
     * Constructor
     *
     * @access  private
     * @param   int     $ftoken     File token
     * @return  void
     */
    private function __construct($ftoken)
    {
        $this->ftoken = $ftoken;
    }

    /**
     * Creates the Jaws_FileMemory instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @param   mixed   $ftoken     File token or name
     * @return  object  Jaws_FileMemory type object
     */
    static function getInstance($ftoken)
    {
        static $instances = array();
        $ftoken = is_int($ftoken)? $ftoken : self::ftok($ftoken);
        if (!isset($instances[$ftoken])) {
            $instances[$ftoken] = new Jaws_FileMemory($ftoken);
        }

        return $instances[$ftoken];
    }

    /**
     * Get file name token
     *
     * @access  public
     * @param   string  $fname  File name
     * @return  int     Returns file token
     */
    static function ftok($fname)
    {
        return crc32($fname);
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
        switch ($mode) {
            case 'a':
                $this->shmkey = @shmop_open($this->ftoken, 'a', 0, 0);
                break;

            case 'w':
                $this->shmkey = @shmop_open($this->ftoken, 'w', 0, 0);
                break;

            case 'c':
                $this->shmkey = @shmop_open($this->ftoken, 'c', 0644, $size);
                break;

            case 'n':
                $this->shmkey = @shmop_open($this->ftoken, 'c', 0644, $size);
                break;

            default:
                $this->shmkey = false;
        }

        return $this->shmkey !== false;
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
        return shmop_read($this->shmkey, $start, $count);
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
        return shmop_write($this->shmkey, $data, $offset);
    }

    /**
     * Close shared memory block
     *
     * @access  public
     * @return  void
     */
    function close()
    {
        @shmop_close($this->shmkey);
    }

    /**
     * Delete shared memory block
     *
     * @access  public
     * @param   int     $ftoken     File token or name
     * @return  bool    Returns TRUE on success or FALSE on failure
     */
    static function delete($ftoken)
    {
        return empty($ftoken)? true : shmop_delete(@shmop_open($ftoken, 'w', 0, 0));
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
            Jaws_Mutex::getInstance()->acquire($this->ftoken);
        } else {
            Jaws_Mutex::getInstance()->release($this->ftoken);
        }
    }

}