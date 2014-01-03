<?php
/**
 * Blog UpdateComment event
 *
 * @category   Gadget
 * @package    Blog
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Blog_Events_UpdateComment extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($gadget, $action, $reference)
    {
        if ($gadget != 'Blog') {
            return;
        }

        $cModel = Jaws_Gadget::getInstance('Comments')->model->load('Comments');
        $howManyComment = $cModel->GetCommentsCount('Blog', $action, $reference, '',
            Comments_Info::COMMENTS_STATUS_APPROVED);
        $bModel = $this->gadget->model->loadAdmin('Comments');
        return $bModel->UpdatePostCommentsCount($reference, $howManyComment);
    }
}