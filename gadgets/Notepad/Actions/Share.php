<?php
/**
 * Notepad Gadget
 *
 * @category    Gadget
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2008-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$this->app->layout->addLink('gadgets/Notepad/Resources/site_style.css');
class Notepad_Actions_Share extends Jaws_Gadget_Action
{
    /**
     * Builds sharing UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ShareNote()
    {

        // Fetch note
        $id = (int)$this->gadget->request->fetch('id', 'get');
        $model = $this->gadget->model->load('Notepad');
        $uid = (int)$this->app->session->user->id;
        $note = $model->GetNote($id, $uid);
        if (Jaws_Error::IsError($note) ||
            empty($note) ||
            $note['user'] != $uid)
        {
            return;
        }

        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->template->load('Share.html');
        $tpl->SetBlock('share');
        $tpl->SetVariable('id', $id);
        $tpl->SetVariable('UID', $uid);
        $tpl->SetVariable('note_title', $note['title']);
        $tpl->SetVariable('title', _t('NOTEPAD_SHARE'));
        $tpl->SetVariable('lbl_users', _t('NOTEPAD_USERS'));
        $tpl->SetVariable('notepad_url', $this->gadget->urlMap('Notepad'));

        // User groups
        $groups = Jaws_Gadget::getInstance('Users')->model->load('Group')->list(
            0, 0, 0,
            array('enabled'  => true),
            array(), // default fieldset
            array('title' => true ) // order by title ascending
        );
        if (!Jaws_Error::IsError($groups)) {
            $combo =& Piwi::CreateWidget('Combo', 'sys_groups');
            $combo->AddEvent(ON_CHANGE, 'toggleUsers(this.value)');
            $combo->AddOption(_t('NOTEPAD_ALL_USERS'), 0);
            foreach ($groups as $group) {
                $combo->AddOption($group['title'], $group['id']);
            }
            $tpl->SetVariable('groups', $combo->Get());
        }
        $tpl->SetVariable('lbl_groups', _t('NOTEPAD_GROUPS'));

        // Note users
        $model = $this->gadget->model->load('Share');
        $combo =& Piwi::CreateWidget('Combo', 'note_users');
        $combo->SetSize(10);
        $users = $model->GetNoteUsers($id);
        if (!Jaws_Error::IsError($users) && !empty($users)) {
            foreach ($users as $user) {
                if ($user['user_id'] != $uid) {
                    $combo->AddOption($user['nickname'].' ('.$user['username'].')', $user['user_id']);
                }
            }
        }
        $tpl->SetVariable('note_users', $combo->Get());
        $tpl->SetVariable('lbl_note_users', _t('NOTEPAD_SHARED_FOR'));

        // Actions
        $tpl->SetVariable('lbl_ok', Jaws::t('OK'));
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('url_back', $this->gadget->urlMap('Notepad'));

        $tpl->ParseBlock('share');
        return $tpl->Get();
    }

    /**
     * Fetches list of system users
     *
     * @access  public
     * @return  array   Array of users or an empty array
     */
    function GetUsers()
    {
        $gid = (int)$this->gadget->request->fetch('gid');
        if ($gid === 0) {
            $gid = false;
        }
        $users = Jaws_Gadget::getInstance('Users')->model->load('User')->list(
            0, $gid,
            array('status' => 1)
        );
        if (Jaws_Error::IsError($users)) {
            return array();
        }
        return $users;
    }

    /**
     * Shares note for passed users
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateShare()
    {
        $id = (int)$this->gadget->request->fetch('id');
        $model = $this->gadget->model->load('Notepad');
        $user = (int)$this->app->session->user->id;

        // Validate note
        $note = $model->GetNote($id, $user);
        if (Jaws_Error::IsError($note) || empty($note)) {
            return $this->gadget->session->response(
                _t('NOTEPAD_ERROR_RETRIEVING_DATA'),
                RESPONSE_ERROR
            );
        }

        // Verify owner
        if ($note['user'] != $user) {
            return $this->gadget->session->response(
                _t('NOTEPAD_ERROR_NO_PERMISSION'),
                RESPONSE_ERROR
            );
        }

        $users = $this->gadget->request->fetch('users');
        $users = empty($users)? array() : explode(',', $users);
        $model = $this->gadget->model->load('Share');
        $res = $model->UpdateNoteUsers($id, $users);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response(
                _t('NOTEPAD_ERROR_NOTE_SHARE'),
                RESPONSE_ERROR
            );
        }

        $this->gadget->session->push(
            _t('NOTEPAD_NOTICE_SHARE_UPDATED'),
            RESPONSE_NOTICE,
            'Response'
        );
        return $this->gadget->session->response(
            _t('NOTEPAD_NOTICE_SHARE_UPDATED')
        );
    }
}