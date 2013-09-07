<?php
/**
 * Directory Gadget
 *
 * @category    GadgetModel
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Model_Share extends Jaws_Gadget_Model
{
    /**
     * Fetches list of users the file is shared for
     *
     * @access  public
     * @param   int     $id  File ID
     * @return  array   Array of users or an empty array
     */
    function GetFileUsers($id)
    {
        $fmTable = Jaws_ORM::getInstance()->table('directory');
        $fmTable->select('id', 'title', 'is_dir:boolean');

        if ($parent !== null){
            $fmTable->where('parent', $parent);
        }
        return $fmTable->orderBy('id asc')->fetchAll();
    }

    /**
     * Updates users which the file is shared for
     *
     * @access  public
     * @param   int     $id  File ID
     * @return  array   Array of users or an empty array
     */
    function UpdateFileUsers($id, $users)
    {
        $fmTable = Jaws_ORM::getInstance()->table('directory');
        $fmTable->select('id', 'title', 'is_dir:boolean');

        if ($parent !== null){
            $fmTable->where('parent', $parent);
        }
        return $fmTable->orderBy('id asc')->fetchAll();
    }
}