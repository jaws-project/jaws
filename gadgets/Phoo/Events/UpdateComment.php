<?php
/**
 * Phoo UpdateComment event
 *
 * @category   Gadget
 * @package    Phoo
 * @author     ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2008-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Phoo_Events_UpdateComment extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $params)
    {
        @list($gadget, $action, $reference) = $params;
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