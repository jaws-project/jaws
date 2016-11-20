<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Actions_Admin_ManageEvent extends Jaws_Gadget_Action
{

    /**
     * Creates a new event
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateEvent()
    {
        $post = $this->gadget->request->fetch(array('subject', 'location', 'description',
            'start_date', 'stop_date', 'start_time', 'stop_date', 'type', 'priority', 'reminder',
            'recurrence', 'month', 'day', 'wday'), 'post');
        if (empty($post['subject']) || empty($post['start_date'])) {
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'), RESPONSE_ERROR);
        }

        $post['user'] = 0;
        if (empty($post['stop_date'])) {
            $post['stop_date'] = $post['start_date'];
        }
        if (empty($post['start_time'])) {
            $post['start_time'] = '00:00';
        }
        if (empty($post['stop_time'])) {
            $post['stop_time'] = $post['start_time'];
        }

        $model = $this->gadget->model->load('Event');
        $id = $model->InsertEvent($post);
        if (Jaws_Error::IsError($id)) {
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_REQUEST_FAILED'), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_NOTICE_EVENT_CREATED'), RESPONSE_NOTICE, $id);
    }

    /**
     * Updates event
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateEvent()
    {
        $post = $this->gadget->request->fetch(array('id:int', 'subject', 'location', 'description',
            'start_date', 'stop_date', 'start_time', 'stop_date', 'type', 'priority', 'reminder',
            'recurrence', 'month', 'day', 'wday'), 'post');
        if (empty($post['subject']) || empty($post['start_date'])) {
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'), RESPONSE_ERROR);
        }

        // Validate event
        $model = $this->gadget->model->load('Event');
        $id = (int)$post['id'];
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $event = $model->GetEvent($id, $user);
        if (Jaws_Error::IsError($event)) {
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_RETRIEVING_DATA'), RESPONSE_ERROR);
        }

        // Verify owner
//        if ($event['owner'] != $user) {
//            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_NO_PERMISSION'), RESPONSE_ERROR);
//        }

        $post['user'] = 0;
        if (empty($post['stop_date'])) {
            $post['stop_date'] = $post['start_date'];
        }
        if (empty($post['stop_time'])) {
            $post['stop_time'] = $post['start_time'];
        }

        $res = $model->UpdateEvent($id, $post, $event);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_REQUEST_FAILED'), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_NOTICE_EVENT_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Deletes the event(s)
     *
     * @access  public
     * @return  mixed   Response array
     */
    function DeleteEvent()
    {
        $events = $this->gadget->request->fetch('events:array');
        if (empty($events)) {
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_EVENT_DELETE'), RESPONSE_ERROR);
        }

        // Verify events & user
//        $model = $this->gadget->model->load('Event');
//        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
//        $verified_events = $model->CheckEvents($events, $user);
//        if (Jaws_Error::IsError($verified_events)) {
//            return $GLOBALS['app']->Session->GetResponse(
//                _t('EVENTSCALENDAR_ERROR_EVENT_DELETE'),
//                RESPONSE_ERROR
//            );
//        }

        // No events are verified
//        if (empty($verified_events)) {
//            return $GLOBALS['app']->Session->GetResponse(
//                _t('EVENTSCALENDAR_ERROR_NO_PERMISSION'),
//                RESPONSE_ERROR
//            );
//        }

        // Delete events
        $model = $this->gadget->model->load('Event');
        $res = $model->Delete($events);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(_t('EVENTSCALENDAR_ERROR_EVENT_DELETE'), RESPONSE_ERROR);
        }

//        if (count($events) !== count($verified_events)) {
//            $msg = _t('EVENTSCALENDAR_WARNING_DELETE_EVENTS_FAILED');
//            // FIXME: we are creating response twice
//            $GLOBALS['app']->Session->PushResponse($msg, 'Events.Response', RESPONSE_WARNING);
//            return $GLOBALS['app']->Session->GetResponse($msg, RESPONSE_WARNING);
//        }

        $msg = (count($events) === 1)?
            _t('EVENTSCALENDAR_NOTICE_EVENT_DELETED') :
            _t('EVENTSCALENDAR_NOTICE_EVENTS_DELETED');
        return $GLOBALS['app']->Session->GetResponse($msg, RESPONSE_NOTICE);
    }
}