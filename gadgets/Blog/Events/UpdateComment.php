<?php
/**
 * Blog UpdateComment event
 *
 * @category   Gadget
 * @package    Blog
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Blog_Events_UpdateComment extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $params)
    {
        @list($gadget, $action, $reference) = $params;
        if ($gadget != 'Blog') {
            return;
        }

        $cModel = Jaws_Gadget::getInstance('Comments')->model->load('Comments');
        $howManyComment = $cModel->GetCommentsCount(
            $this->gadget->name,
            $action,
            $reference,
            '',
            Comments_Info::COMMENTS_STATUS_APPROVED
        );
        $bModel = $this->gadget->model->loadAdmin('Comments');
        return $bModel->UpdatePostCommentsCount($reference, $howManyComment);
    }
}