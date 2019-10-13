<?php
/**
 * Notepad Gadget
 *
 * @category    Gadget
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$this->app->layout->addLink('gadgets/Notepad/Resources/site_style.css');
class Notepad_Actions_Delete extends Jaws_Gadget_Action
{
    /**
     * Deletes passed note(s)
     *
     * @access  public
     * @return  mixed   Response array
     */
    function DeleteNote()
    {
        $id_set = $this->gadget->request->fetch('id_set');
        $id_set = explode(',', $id_set);
        if (empty($id_set)) {
            return $this->gadget->session->response(
                _t('NOTEPAD_ERROR_NOTE_DELETE'),
                RESPONSE_ERROR
            );
        }

        // Verify notes & user
        $model = $this->gadget->model->load('Notepad');
        $user = (int)$this->app->session->user;
        $verified_nodes = $model->CheckNotes($id_set, $user);
        if (Jaws_Error::IsError($verified_nodes)) {
            return $this->gadget->session->response(
                _t('NOTEPAD_ERROR_NOTE_DELETE'),
                RESPONSE_ERROR
            );
        }

        // No notes was verified
        if (empty($verified_nodes)) {
            return $this->gadget->session->response(
                _t('NOTEPAD_ERROR_NO_PERMISSION'),
                RESPONSE_ERROR
            );
        }

        // Delete notes
        $res = $model->Delete($verified_nodes);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response(
                _t('NOTEPAD_ERROR_NOTE_DELETE'),
                RESPONSE_ERROR
            );
        }

        if (count($id_set) !== count($verified_nodes)) {
            $msg = _t('NOTEPAD_WARNING_DELETE_NOTES_FAILED');
            // FIXME: we are creating response twice
            $this->gadget->session->push($msg, RESPONSE_WARNING, 'Response');
            return $this->gadget->session->response($msg, RESPONSE_WARNING);
        }

        $msg = (count($id_set) === 1)?
            _t('NOTEPAD_NOTICE_NOTE_DELETED') :
            _t('NOTEPAD_NOTICE_NOTES_DELETED');
        $this->gadget->session->push($msg, RESPONSE_NOTICE, 'Response');
        return $this->gadget->session->response($msg);
    }

}