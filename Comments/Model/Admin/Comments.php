<?php
/**
 * Comments Gadget Admin
 *
 * @category    GadgetModel
 * @package     Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Model_Admin_Comments extends Jaws_Gadget_Model
{
    /**
     * Mark as a different status several comments
     *
     * @access  public
     * @param   string $gadget  Gadget's name
     * @param   array   $ids     Id's of the comments to mark as spam
     * @param   string  $status  New status (spam by default)
     * @return  bool
     */
    function MarkAs($gadget, $ids, $status = 'spam')
    {
        if (count($ids) == 0) {
            return true;
        }

        if (!in_array($status, array(1, 2, 3))) {
            $status = Comments_Info::COMMENT_STATUS_SPAM;
        }

        // Update status...
        $commentsTable = Jaws_ORM::getInstance()->table('comments');
        $commentsTable->update(array('status'=>$status))->where('id', $ids, 'in')->exec();

        if ($status == Comments_Info::COMMENT_STATUS_SPAM) {
            $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'AdminModel');
            // Submit spam...
            $commentsTable = Jaws_ORM::getInstance()->table('comments');
            $commentsTable->select('id:integer', 'name', 'email', 'url', 'msg_txt', 'msg_txt', 'status:integer');
            $items = $commentsTable->where('id', $ids, 'in')->getAll();
            if (Jaws_Error::IsError($items)) {
                return $items;
            }

            foreach ($items as $i) {
                if ($i['status'] != Comments_Info::COMMENT_STATUS_SPAM) {
                    // FIXME Get $permalink
                    $permalink = '';
                    $mPolicy->SubmitSpam($permalink, $gadget, $i['name'], $i['email'], $i['url'], $i['message']);
                }
            }
        }

        return true;
    }

    /**
     * Deletes a comment
     *
     * @access  public
     * @param   int     $id     Comment ID
     * @return  bool    True if success or Jaws_Error on any error
     */
    function Delete($id)
    {
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
    function MassiveCommentDelete($ids)
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