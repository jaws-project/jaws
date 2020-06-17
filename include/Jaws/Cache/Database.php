<?php
/**
 * Database cache driver
 *
 * @category   Cache
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Cache_Database extends Jaws_Cache
{
    /**
     * Jaws ORM object
     * @var     object  $dbCacheORM
     * @access  private
     */
    private $dbCacheORM = null;

    /**
     * Constructor
     *
     * @access  public
     * @return Null
     */
    function __construct()
    {
        $this->dbCacheORM = Jaws_ORM::getInstance()->table('cache');
    }

    /**
     * Store value of given key
     *
     * @access  public
     * @param   int     $key    key
     * @param   mixed   $value  value 
     * @param   int     $lifetime
     * @return  mixed
     */
    function set($key, $value, $lifetime = 2592000)
    {
        $result = false;
        if (!empty($lifetime)) {
            $result = $this->dbCacheORM->reset()
                ->upsert(
                    array(
                        'key'      => $key,
                        'value'    => array($value, 'clob'),
                        'lifetime' => time() + $lifetime
                    )
                )->where('key', $key)
                ->exec();
            $result = Jaws_Error::IsError($result)? false : strlen($value);
        }

        return $result;
    }

    /**
     * Get cached value of given key
     *
     * @access  public
     * @param   int     $key    key
     * @return  mixed   Returns key value
     */
    function get($key)
    {
        $result = $this->dbCacheORM->reset()
            ->select(
                'id:integer', 'key:integer', 'value:clob', 'lifetime:integer'
            )->where('key', $key)
            ->fetchRow();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return false;
        }

        if ($result['lifetime'] > time()) {
            if (is_resource($result['value'])) {
                $clob = '';
                while (!feof($result['value'])) {
                    $clob.= fread($result['value'], 8192);
                }
                $result['value'] = $clob;
            }

            return $result['value'];
        }

        // delete expired cache
        $result = $this->dbCacheORM->reset()->delete()->where('key', $key)->exec();
        if (Jaws_Error::IsError($result)) {
            // do noting
        }

        return false;
    }

    /**
     * Delete cached key
     *
     * @access  public
     * @param   int     $key    key
     * @return  mixed
     */
    function delete($key)
    {
        $result = $this->dbCacheORM->reset()->delete()->where('key', $key)->exec();
        return Jaws_Error::IsError($result)? false : (bool)$result;
    }

    /**
     * Delete expired cached keys
     *
     * @access  public
     * @return  mixed
     */
    function deleteExpiredKeys()
    {
        $result = $this->dbCacheORM->delete()->where('lifetime', time(), '<')->exec();
        if (Jaws_Error::IsError($result)) {
            // do noting
        }

        return true;
    }

}