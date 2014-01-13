<?php
/**
 * Phoo UpdateComment event
 *
 * @category   Gadget
 * @package    Phoo
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Phoo_Events_UpdateComment extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($gadget, $action, $reference)
    {
        if ($gadget != 'Phoo') {
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
        $pModel = $this->gadget->model->loadAdmin('Comments');
        return $pModel->UpdateImageCommentsCount($reference, $howManyComment);
    }
}