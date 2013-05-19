<?php
/**
 * Blog DeleteComment event
 *
 * @category   Gadget
 * @package    Blog
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Blog_Events_DeleteComment extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($action, $reference)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model', 'Comments');
        $howManyComment = $cModel->GetCommentsCount('Blog', $action, $reference, '',
            Comments_Info::COMMENT_STATUS_APPROVED);
        $bModel = $GLOBALS['app']->loadGadget('Blog', 'AdminModel');
        return $bModel->UpdatePostCommentsCount($reference, $howManyComment);
    }
}