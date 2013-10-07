<?php
/**
 * Users Core Gadget
 *
 * @category    Gadget
 * @package     Users
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_Groups extends Users_HTML
{
    /**
     * Prepares a form for manage user's groups
     *
     * @access  public
     * @return  string  XHTML template of a form
     */
    function Groups()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        $this->gadget->CheckPermission('ManageUserGroups');
        $this->AjaxMe('index.js');
        $response = $GLOBALS['app']->Session->PopResponse('Users.Groups');
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $jUser = new Jaws_User;
        // TODO: must set user_id
        $groups = $jUser->GetGroups();

        // Load the template
        $tpl = $this->gadget->loadTemplate('Groups.html');
        $tpl->SetBlock('groups');


        if (!empty($response)) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
        }

        $tpl->SetVariable('title', _t('USERS_GROUPS'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('lbl_enabled', _t('GLOBAL_ENABLED'));

        foreach($groups as $group) {
            $tpl->SetBlock('groups/group');
            $tpl->SetVariable('name', $group['name']);
            $tpl->SetVariable('title', $group['title']);
            $enabled = ($group['enabled']==true)? _t('GLOBAL_YES'):_t('GLOBAL_NO');
            $tpl->SetVariable('enabled', $enabled);
            $tpl->ParseBlock('groups/group');
        }


        $tpl->SetVariable('lbl_actions', _t('GLOBAL_ACTIONS'));
        $tpl->SetVariable('lbl_no_action', _t('GLOBAL_NO_ACTION'));

        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_disable', _t('GLOBAL_DISABLE'));
        $tpl->SetVariable('lbl_enable', _t('GLOBAL_ENABLE'));
        $tpl->SetVariable('icon_filter', STOCK_SEARCH);
        $tpl->SetVariable('icon_ok', STOCK_OK);

        $tpl->ParseBlock('groups');
        return $tpl->Get();
    }



    /**
     * Delete user's group(s)
     *
     * @access  public
     * @return  void
     */
    function DeleteGroups()
    {
        $this->gadget->CheckPermission('ManageUserGroups');

        $ids = jaws()->request->fetchAll('message_checkbox', 'post');
        if(!empty($post['message_checkbox']) && count($post['message_checkbox'])>0) {
            $ids = $post['message_checkbox'];
        }

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $user = $GLOBALS['app']->Session->GetAttribute('user');
        $res = $model->ArchiveInboxMessage($ids, $user, $status);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }

        if ($res == true) {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_MESSAGE_ARCHIVED'),
                'PrivateMessage.Message',
                RESPONSE_NOTICE
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('PRIVATEMESSAGE_ERROR_MESSAGE_NOT_ARCHIVED'),
                'PrivateMessage.Message',
                RESPONSE_ERROR
            );
        }
        Jaws_Header::Location($this->gadget->urlMap('Inbox'));
    }

}