<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_AlbumGroup extends Jaws_Gadget_Model
{
    /**
     * Get AlbumGroup list
     *
     * @access  public
     * @param   int      $album         Album ID
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function GetAlbumGroups($album)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_album_group');
        $table->select('id', 'album', 'group')->where('album', $album);
        return $table->fetchAll();
    }

    /**
     * Get Group ID list with selected AlbumID
     *
     * @access  public
     * @param   int      $album         Album ID
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function GetAlbumGroupsID($album)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_album_group');
        $table->select('group')->where('album', $album);
        return $table->fetchColumn();
    }

    /**
     * Get AlbumGroup list with group information
     *
     * @access  public
     * @param   int      $album         Album ID
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function GetAlbumGroupsInfo($album)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_album_group');
        $table->select('phoo_album_group.group', 'phoo_group.name', 'phoo_group.fast_url');
        $table->join('phoo_group', 'phoo_album_group.group', 'phoo_group.id');
        $table->where('album', $album);
        return $table->fetchAll();
    }
}