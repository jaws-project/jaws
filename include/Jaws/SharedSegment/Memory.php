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
class Jaws_SharedSegment_Memory extends Jaws_SharedSegment
{
    /**
     * shared memory resource handle
     * @var     resource    $hSHMemory
     * @access  private
     */
    private $hSHMemory;

    /**
     * Open shared memory block
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
                // open an existing shared memory segment for read only 
                $this->hSHMemory = @shmop_open($this->ftok, 'a', 0, 0);
                break;

            case 'w':
                // open an existing shared memory segment for read & write
                $this->hSHMemory = @shmop_open($this->ftok, 'w', 0, 0);
                break;

            case 'c':
                // create new shared memory segment if exists, try to open it for read & write
                $this->hSHMemory = @shmop_open($this->ftok, 'c', 0644, $size);
                break;

            case 'n':
                // create new shared memory segment if exists exists, fail and return false
                $this->hSHMemory = @shmop_open($this->ftok, 'c', 0644, $size);
                break;

            default:
                $this->hSHMemory = false;
        }

        return $this->hSHMemory !== false;
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
        return shmop_read($this->hSHMemory, $start, $count);
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
        return shmop_write($this->hSHMemory, $data, $offset);
    }

    /**
     * Close shared memory block
     *
     * @access  public
     * @return  void
     */
    function close()
    {
        @shmop_close($this->hSHMemory);
    }

    /**
     * Delete shared memory block
     *
     * @access  public
     * @param   int     $ftok   File token or name
     * @return  bool    Returns TRUE on success or FALSE on failure
     */
    static function delete($ftok)
    {
        return empty($ftok)? true : shmop_delete(@shmop_open($ftok, 'w', 0, 0));
    }

}