<?php
/**
 * FileManager Gadget
 *
 * @category    GadgetModel
 * @package     FileManager
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FileManager_Model_Files extends Jaws_Gadget_Model
{
    /**
     * Inserts a new file record
     *
     * @access  public
     * @param   array  $data    File data
     * @return  mixed   True on successful insert, Jaws_Error otherwise
     */
    function InsertFile($data)
    {
        $fmTable = Jaws_ORM::getInstance()->table('fm_files');
        return $fmTable->insert($data)->exec();
    }

    /**
     * Updates the emblem
     *
     * @access  public
     * @param   int     $id     Emblem ID
     * @param   array   $data   Emblem data
     * @return  mixed   True on successful update and Jaws_Error on error
     */
    function UpdateEmblem($id, $data)
    {
        $emblemTable = Jaws_ORM::getInstance()->table('emblem');
        return $emblemTable->update($data)->where('id', $id)->exec();
    }

    /**
     * Deletes the emblem
     *
     * @access  public
     * @param   int      $id     ID that identifies the emblem
     * @param   string   $src    Path to the emblem image
     * @return  mixed    True if success, Jaws_Error otherwise
     */
    function DeleteEmblem($id)
    {
        $table = Jaws_ORM::getInstance()->table('emblem');
        return $table->delete()->where('id', $id)->exec();
    }

    /**
     * Fetches list of files
     *
     * @access  public
     * @param   bool    $published  if need to get only published emblems
     * @param   mixed   $limit      Optional. Limit of data to retrieve (false = returns all)
     * @return  array   Array of emblems and Jaws_Error on error
     */
    function GetFiles($published = false, $limit = false)
    {
        if (is_numeric($limit)) {
            $rs = $GLOBALS['db']->setLimit(10, $limit);
            if (Jaws_Error::IsError($rs)) {
                return new Jaws_Error($rs->getMessage(), 'SQL');
            }
        }

        $emblemTable = Jaws_ORM::getInstance()->table('emblem');
        $emblemTable->select('id', 'title', 'image', 'url', 'type', 'published:boolean');

        if ($published){
            $emblemTable->where('published', true);
        }
        $res = $emblemTable->orderBy('id asc')->fetchAll();
        if (Jaws_Error::IsError($res)){
            return new Jaws_Error($res->getMessage(), 'SQL');
        }
        return $res;
    }

    /**
     * Get information of an emblem
     *
     * @access  public
     * @param   int     $id  Emblem ID
     * @return  mixed   Array of emblem data and Jaws_Error on error
     */
    function GetEmblem($id)
    {
        $emblemTable = Jaws_ORM::getInstance()->table('emblem');
        $emblemTable->select('id:integer', 'title', 'image', 'url', 'type');
        $res = $emblemTable->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }
        return $res;
    }
}