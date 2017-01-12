<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
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
        if (!$GLOBALS['app']->Session->Logged()) {
            $userGadget = Jaws_Gadget::getInstance('Users');
            Jaws_Header::Location(
                $userGadget->urlMap(
                    'LoginBox',
                    array('referrer' => bin2hex(Jaws_Utils::getRequestURL(true)))
                ), 401
            );
        }

        // Validate user
        $userId = (int)jaws()->request->fetch('user:int', 'get');
        if ($userId > 0 && $userId !== (int)$GLOBALS['app']->Session->GetAttribute('user')) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }


        // Fetch event
        $id = (int)jaws()->request->fetch('event', 'get');
        $model = $this->gadget->model->load('Event');
        $event = $model->GetEvent($id, $userId);
        if (Jaws_Error::IsError($event)) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(500);
        }
        if (empty($event)) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }
        if ($event['user'] != $userId) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        $GLOBALS['app']->Layout->addLink('gadgets/EventsCalendar/Resources/index.css');
        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('ShareEvent.html');
        $tpl->SetBlock('share');
        $tpl->SetVariable('title', _t('EVENTSCALENDAR_SHARE'));
        $tpl->SetVariable('id', $id);
        $tpl->SetVariable('UID', $userId);
        $tpl->SetVariable('subject', $event['subject']);
        $tpl->SetVariable('lbl_users', _t('EVENTSCALENDAR_USERS'));
        $tpl->SetVariable('events_url', $this->gadget->urlMap('ManageEvents', array('user' => $userId)));

        // User groups
        $uModel = new Jaws_User();
        $groups = $uModel->GetGroups($userId, true, 'title');
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
                if ($user['user'] != $userId) {
                    $combo->AddOption($user['nickname'].' ('.$user['username'].')', $user['user']);
                }
            }
        }
        $tpl->SetVariable('event_users', $combo->Get());
        $tpl->SetVariable('lbl_event_users', _t('EVENTSCALENDAR_SHARED_FOR'));

        // Actions
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('url_back', $this->gadget->urlMap('ViewEvent', array('user' => $userId, 'event' => $id)));

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