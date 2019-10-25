<?php
/**
 * SharedMemory cache driver
 *
 * @category    Cache
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2018-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Cache_SharedMemory extends Jaws_Cache
{
    /**
     * FileMemory object
     * @access  private
     */
    var $shmcache;

    /**
     * Constructor
     *
     * @access  public
     * @return Null
     */
    function __construct()
    {
        $this->shmcache = Jaws_SharedSegment::getInstance('jaws_memcache');
    }

    /**
     * Store value of given key
     *
     * @access  public
     * @param   string  $key    key
     * @param   mixed   $value  value 
     * @param   int     $lifetime
     * @return  mixed
     */
    function set($key, $value, $lifetime = 2592000)
    {
        $result = false;
        if (!empty($lifetime)) {
            $this->shmcache->lock(true);
            if ($this->shmcache->open('c', 64*1024)) {
                $keyscached = @unserialize($this->shmcache->read());
                if (!$keyscached) {
                    $keyscached = array();
                }

                $token = (int)floor(microtime(true)*100000);
                $keyFile = Jaws_SharedSegment::getInstance($token);
                if ($keyFile->open('n', strlen($value))) {
                    $result = $keyFile->write($value);
                    $this->shmcache->delete(@$keyscached[$key]['token']);
                    $keyscached[$key] = array(
                        'token'    => $token,
                        'lifetime' => time() + $lifetime,
                    );
                    $keyFile->close();
                }
                $this->shmcache->write(serialize($keyscached));
                $this->shmcache->close();
            }

            $this->shmcache->lock(false);
        }

        return $result;
    }

    /**
     * Get cached value of given key
     *
     * @access  public
     * @param   string  $key    key
     * @return  mixed   Returns key value
     */
    function get($key)
    {
        $value = false;
        if ($this->shmcache->open('w')) {
            $keyscached = @unserialize($this->shmcache->read());
            if (!$keyscached) {
                $keyscached = array();
            }

            if (mt_rand(1, 10) == mt_rand(1, 10)) {
                // loop for find outdated cached file
                $this->shmcache->lock(true);
                foreach ($keyscached as $bkey => $block) {
                    if (time() > $block['lifetime']) {
                        $this->shmcache->delete($block['token']);
                        unset($keyscached[$bkey]);
                    }
                }
                $this->shmcache->write(serialize($keyscached));
                $this->shmcache->lock(false);
            }

            $this->shmcache->close();
        }

        if (array_key_exists($key, $keyscached) &&
            ($keyscached[$key]['lifetime'] > time())
        ) {
            $keyFile = Jaws_SharedSegment::getInstance($keyscached[$key]['token']);
            if ($keyFile->open('a')) {
                $value = $keyFile->read();
                $keyFile->close();
            }
        }

        return $value;
    }

    /**
     * Delete cached key
     *
     * @access  public
     * @param   string  $key    key
     * @return  mixed
     */
    function delete($key)
    {
        $result = true;
        $this->shmcache->lock(true);
        if ($this->shmcache->open('w')) {
            $keyscached = @unserialize($this->shmcache->read());
            if (!$keyscached) {
                $keyscached = array();
            }

            if (array_key_exists($key, $keyscached)) {
                $result = $this->shmcache->delete($keyscached[$key]['token']);
                unset($keyscached[$key]);
            }

            $this->shmcache->write(serialize($keyscached));
            $this->shmcache->close();
        }

        $this->shmcache->lock(false);
        return $result;
    }

}