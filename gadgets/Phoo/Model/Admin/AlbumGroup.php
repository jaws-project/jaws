<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Admin_AlbumGroup extends Jaws_Gadget_Model
{
    /**
     * Insert new album,group
     *
     * @access  public
     * @param   array    $insertData   Array With Album ID & Group ID
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function AddAlbumGroup($insertData)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_album_group');
        return $table->insert($insertData)->exec();
    }

    /**
     * Delete all record with this Group ID
     *
     * @access  public
     * @param   int      $gid         Group ID
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function DeleteGroup($gid)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_album_group');
        $result = $table->delete()->where('group', $gid)->exec();
    }

    /**
     * Delete all record with this Album ID
     *
     * @access  public
     * @param   int      $aid         Album ID
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function DeleteAlbum($aid)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_album_group');
        $result = $table->delete()->where('album', $aid)->exec();
    }
}