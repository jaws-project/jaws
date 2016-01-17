<?php
/**
 * Comments Model
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Model_DeleteComments extends Jaws_Gadget_Model
{
    /**
     * Deletes a comment
     *
     * @access  public
     * @param   int     $id     Comment ID
     * @return  bool    True if success or Jaws_Error on any error
     */
    function Delete($id)
    {
        $cTable = Jaws_ORM::getInstance()->table('comments_details');
        $cTable->select('gadget', 'reference:integer', 'action');
        $commentInfo = $cTable->where('id', $id)->fetchRow();

        $commentTable = Jaws_ORM::getInstance()->table('comments');
        return $commentTable->delete()->where('id', $id)->exec();
    }

    /**
     * Deletes all comments of a certain gadget/reference
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   int     $reference  Gadget reference id
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteGadgetComments($gadget, $reference = '')
    {
        $commentTable = Jaws_ORM::getInstance()->table('comments');
        $commentTable->delete()->where('gadget', $gadget);
        if (!empty($reference)) {
            $commentTable->and()->where('reference', (int)$reference);
        }

        return $commentTable->exec();
    }

    /**
     * Does a massive comment delete
     *
     * @access  public
     * @param   array   $ids  Ids of comments
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function DeleteMassiveComment($ids)
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }

        foreach ($ids as $id) {
            $res = $this->Delete($id);
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        return true;
    }

}