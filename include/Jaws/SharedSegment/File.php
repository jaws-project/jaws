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
class Jaws_SharedSegment_File extends Jaws_SharedSegment
{
    /**
     * shared file handle
     * @var     resource    $hSHFile
     * @access  private
     */
    private $hSHFile;

    /**
     * shared files prefix
     * @var     string  $sharedPrefix
     * @access  private
     */
    private $sharedPrefix = 'shared_';

    /**
     * shared files directory
     * @var     string  $sharedDirectory
     * @access  private
     */
    private $sharedDirectory;

    /**
     * Constructor
     *
     * @access  protected
     * @param   int     $ftok   File token
     * @return  void
     */
    protected function __construct($ftok)
    {
        parent::__construct($ftok);
        $this->sharedDirectory = rtrim(sys_get_temp_dir(), '/\\');
    }

    /**
     * Open shared file
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
                // open an existing file for read only 
                $this->hSHFile = @fopen(
                    $this->sharedDirectory . '/'. $this->sharedPrefix . (string)$this->ftok,
                    'r'
                );
                break;

            case 'w':
                // open an existing file for read & write
                $this->hSHFile = @fopen(
                    $this->sharedDirectory . '/'. $this->sharedPrefix . (string)$this->ftok,
                    'r+'
                );
                break;

            case 'c':
                // create new file if exists, try to open it for read & write
                $this->hSHFile = @fopen(
                    $this->sharedDirectory . '/'. $this->sharedPrefix . (string)$this->ftok,
                    'c+'
                );
                break;

            case 'n':
                // create new file if exists exists, fail and return false
                $this->hSHFile = @fopen(
                    $this->sharedDirectory . '/'. $this->sharedPrefix . (string)$this->ftok,
                    'x+'
                );
                break;

            default:
                $this->hSHFile = false;
        }

        return $this->hSHFile !== false;
    }

    /**
     * Read data from shared file
     *
     * @access  public
     * @param   int     $start      Start position
     * @param   int     $count      Count of read bytes
     * @return  mixed   Returns the data or FALSE on failure
     */
    function read($start = 0, $count = 0)
    {
        @fseek($this->hSHFile, $start);
        $count = $count?: @filesize($this->sharedDirectory . '/'. $this->sharedPrefix . (string)$this->ftok);
        return @fread($this->hSHFile, $count);
    }

    /**
     * Write data into shared file
     *
     * @access  public
     * @param   string  $data       Data to write into shared memory block
     * @param   int     $offset     Offset of start writing data
     * @return  mixed   The size of the written data, or FALSE on failure
     */
    function write($data, $offset = 0)
    {
        @fseek($this->hSHFile, $offset);
        return @fwrite($this->hSHFile, $data);
    }

    /**
     * Close shared file
     *
     * @access  public
     * @return  void
     */
    function close()
    {
        @fflush($this->hSHFile);
        @fclose($this->hSHFile);
    }

    /**
     * Delete shared file
     *
     * @access  public
     * @param   int     $ftok   File token or name
     * @return  bool    Returns TRUE on success or FALSE on failure
     */
    function delete($ftok)
    {
        return
            empty($ftok)?
                true :
                Jaws_Utils::delete(
                    $this->sharedDirectory . '/'. $this->sharedPrefix . (string)$this->ftok
                );
    }

}