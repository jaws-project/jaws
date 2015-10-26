<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Admin_Groups extends Jaws_Gadget_Model
{
    /**
     * Insert new group
     *
     * @access  public
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function AddGroup($insertData)
    {
        $fast_url = empty($insertData['fast_url']) ? $insertData['name'] : $insertData['fast_url'];
        $fast_url = $this->GetRealFastUrl($fast_url, 'phoo_group');
        $insertData['fast_url'] = $fast_url;

        $table = Jaws_ORM::getInstance()->table('phoo_group');
        return $table->insert($insertData)->exec();
    }

    /**
     * Update a group
     *
     * @access  public
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function UpdateGroup($gid, $updateData)
    {
        $fast_url = empty($updateData['fast_url']) ? $updateData['name'] : $updateData['fast_url'];
        $fast_url = $this->GetRealFastUrl($fast_url, 'phoo_group');
        $updateData['fast_url'] = $fast_url;

        $table = Jaws_ORM::getInstance()->table('phoo_group');
        return $table->update($updateData)->where('id', $gid)->exec();
    }

    /**
     * Delete a group
     *
     * @access  public
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function DeleteGroup($gid)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_album_group');
        $result = $table->delete()->where('group', $gid)->exec();

        if (Jaws_Error::IsError($result)) {
            return $result;
        } else {
            $table->table('phoo_group');
            return $table->delete()->where('id', $gid)->exec();
        }
    }
}