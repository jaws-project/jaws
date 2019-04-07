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
     * virtual file name
     * @var     string  $fname
     * @access  private
     */
    private $fname;

    /**
     * shared memory resource handle
     * @var     resource    $shmkey
     * @access  private
     */
    private $shmkey;

    /**
     * exclusive access acquire?
     * @var     bool    $exclusive
     * @access  private
     */
    private $exclusive = false;

    /**
     * Constructor
     *
     * @access  private
     * @param   string  $fname  File name
     * @param   int     $fsize  File size
     * @return  void
     */
    private function __construct($fname, $fsize = 4096)
    {
        $this->fname = $fname;
        $this->shmkey = @shmop_open(crc32($this->fname), 'c', 0644, $fsize);
    }

    /**
     * Creates the Jaws_FileMemory instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @param   string  $fname  File name
     * @param   int     $fsize  File size
     * @return  object  Jaws_Lock type object
     */
    static function getInstance($fname, $fsize = 4096, $exclusive = true)
    {
        static $instances = array();
        if (!isset($instances[$fname])) {
            $instances[$fname] = new Jaws_FileMemory($fname, $fsize);
        }

        //exclusive access acquire
        if ($exclusive) {
            $instances[$fname]->lock(true);
        }

        return $instances[$fname];
    }

    /**
     * Read data from shared memory block
     *
     * @access  public
     * @param   int     $start      Start posiotion
     * @param   int     $count      Count of read bytes
     * @return  mixed   Returns the data or FALSE on failure
     */
    function read($start = 0, $count = 0)
    {
        return @unserialize(shmop_read($this->shmkey, $start, $count));
    }

    /**
     * Write data into shared memory block
     *
     * @access  public
     * @param   string  $data       Data to write into shared memory block
     * @param   int     $offset     Offset of start writing data
     * @return  mixed   The size of the written data, or FALSE onfailure
     */
    function write($data, $offset = 0)
    {
        return shmop_write($this->shmkey, serialize($data), $offset);
    }

    /**
     * Close shared memory block
     *
     * @access  public
     * @return  void
     */
    function close()
    {
        $this->lock(false);
        shmop_close($this->shmkey);
    }

    /**
     * Delete shared memory block
     *
     * @access  public
     * @return  void
     */
    function delete ()
    {
        $this->lock(false);
        shmop_delete($this->shmkey);
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
            Jaws_Mutex::getInstance()->acquire($this->fname);
            $this->exclusive = true;
        } else {
            Jaws_Mutex::getInstance()->release($this->fname);
            $this->exclusive = false;
        }
    }

}