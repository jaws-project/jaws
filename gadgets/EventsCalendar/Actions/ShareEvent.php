<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/Resources/site_style.css');
class EventsCalendar_Actions_ShareEvent extends Jaws_Gadget_Action
{
    /**
     * Builds sharing UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function ShareEvent()
    {
        // Fetch event
        $id = (int)jaws()->request->fetch('id', 'get');
        $model = $this->gadget->model->load('Event');
        $uid = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $event = $model->GetEvent($id, $uid);
        if (Jaws_Error::IsError($event) ||
            empty($event) ||
            $event['user'] != $uid)
        {
            return;
        }

        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->template->load('ShareEvent.html');
        $tpl->SetBlock('share');
        $tpl->SetVariable('title', _t('EVENTSCALENDAR_SHARE'));
        $tpl->SetVariable('id', $id);
        $tpl->SetVariable('UID', $uid);
        $tpl->SetVariable('subject', $event['subject']);
        $tpl->SetVariable('lbl_users', _t('EVENTSCALENDAR_USERS'));
        $tpl->SetVariable('events_url', $this->gadget->urlMap('ManageEvents'));

        // User groups
        $uModel = new Jaws_User();
        $groups = $uModel->GetGroups($uid, true, 'title');
        if (!Jaws_Error::IsError($groups)) {
            $combo =& Piwi::CreateWidget('Combo', 'sys_groups');
            $combo->AddEvent(ON_CHANGE, 'toggleUsers(this.value)');
            $combo->AddOption(_t('EVENTSCALENDAR_ALL_USERS'), 0);
            foreach ($groups as $group) {
                $combo->AddOption($group['title'], $group['id']);
            }
            $tpl->SetVariable('groups', $combo->Get());
        }
        $tpl->SetVariable('lbl_groups', _t('EVENTSCALENDAR_GROUPS'));

        // Event users
        $model = $this->gadget->model->load('Share');
        $combo =& Piwi::CreateWidget('Combo', 'event_users');
        $combo->SetSize(10);
        $users = $model->GetEventUsers($id);
        if (!Jaws_Error::IsError($users) && !empty($users)) {
            foreach ($users as $user) {
                if ($user['user'] != $uid) {
                    $combo->AddOption($user['nickname'].' ('.$user['username'].')', $user['user']);
                }
            }
        }
        $tpl->SetVariable('event_users', $combo->Get());
        $tpl->SetVariable('lbl_event_users', _t('EVENTSCALENDAR_SHARED_FOR'));

        // Actions
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('url_back', $this->gadget->urlMap('ViewEvent', array('id' => $id)));

        $tpl->ParseBlock('share');
        return $tpl->Get();
    }

    /**
     * Fetches list of jaws users
     *
     * @access  public
     * @return  array   Array of users or an empty array
     */
    function GetUsers()
    {
        $gid = (int)jaws()->request->fetch('gid');
        if ($gid === 0) {
            $gid = false;
        }
        $uModel = new Jaws_User();
        $users = $uModel->GetUsers($gid, null, 1);
        if (Jaws_Error::IsError($users)) {
            return array();
        }
        return $users;
    }

    /**
     * Shares event for passed users
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateShare()
    {
        $id = (int)jaws()->request->fetch('id');
        $model = $this->gadget->model->load('Event');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');

        // Validate event
        $event = $model->GetEvent($id, $user);
        if (Jaws_Error::IsError($event) || empty($event)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_RETRIEVING_DATA'),
                RESPONSE_ERROR
            );
        }

        // Verify owner
        if ($event['user'] != $user) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_NO_PERMISSION'),
                RESPONSE_ERROR
            );
        }

        $users = jaws()->request->fetch('users');
        $users = empty($users)? array() : explode(',', $users);
        $model = $this->gadget->model->load('Share');
        $res = $model->UpdateEventUsers($id, $users);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_SHARE'),
                RESPONSE_ERROR
            );
        }

        $GLOBALS['app']->Session->PushResponse(
            _t('EVENTSCALENDAR_NOTICE_SHARE_UPDATED'),
            'Events.Response'
        );
        return $GLOBALS['app']->Session->GetResponse(
            _t('EVENTSCALENDAR_NOTICE_SHARE_UPDATED')
        );
    }
}