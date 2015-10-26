<?php
/**
 * Emblems Admin Gadget
 *
 * @category   GadgetModelAdmin
 * @package    Emblems
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_Model_Admin_Emblems extends Jaws_Gadget_Model
{
    /**
     * Adds a new emblem to the system and database
     *
     * @access  public
     * @param   array  $data    Emblem data
     * @return  mixed   True on successful insert, Jaws_Error otherwise
     */
    function AddEmblem($data)
    {
        $emblemTable = Jaws_ORM::getInstance()->table('emblem');
        return $emblemTable->insert($data)->exec();
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
}