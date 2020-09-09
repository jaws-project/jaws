<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016-2020 Jaws Development Group
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
        $post = $this->gadget->request->fetch(
            array(
                'summary', 'location', 'link', 'verbose', 'start_date', 'stop_date',
                'start_time', 'stop_time', 'type', 'priority', 'reminder', 'recurrence',
                'month', 'day', 'wday', 'symbol'
            ),
            'post'
        );
        if (empty($post['summary']) || empty($post['start_date'])) {
            return $this->gadget->session->response(_t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'), RESPONSE_ERROR);
        }

        $post['public'] = true;
        $post['owner'] = $post['user'] = 0;
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
            return $this->gadget->session->response(_t('EVENTSCALENDAR_ERROR_REQUEST_FAILED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(_t('EVENTSCALENDAR_NOTICE_EVENT_CREATED'), RESPONSE_NOTICE, $id);
    }

    /**
     * Updates event
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateEvent()
    {
        $post = $this->gadget->request->fetch(
            array(
                'id:int', 'summary', 'location', 'verbose', 'link',
                'start_date', 'stop_date', 'start_time', 'stop_time', 'type', 'priority',
                'reminder', 'recurrence', 'month', 'day', 'wday', 'symbol'
            ),
            'post'
        );
        if (empty($post['summary']) || empty($post['start_date'])) {
            return $this->gadget->session->response(_t('EVENTSCALENDAR_ERROR_INCOMPLETE_DATA'), RESPONSE_ERROR);
        }

        // Validate event
        $model = $this->gadget->model->load('Event');
        $id = (int)$post['id'];
        $user = (int)$this->app->session->user->id;
        $event = $model->GetEvent($id, $user);
        if (Jaws_Error::IsError($event)) {
            return $this->gadget->session->response(_t('EVENTSCALENDAR_ERROR_RETRIEVING_DATA'), RESPONSE_ERROR);
        }

        // Verify owner
        $post['public'] = true;
        $post['owner'] = $post['user'] = 0;
        if (empty($post['stop_date'])) {
            $post['stop_date'] = $post['start_date'];
        }
        if (empty($post['stop_time'])) {
            $post['stop_time'] = $post['start_time'];
        }

        $res = $model->UpdateEvent($id, $post, $event);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response(_t('EVENTSCALENDAR_ERROR_REQUEST_FAILED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(_t('EVENTSCALENDAR_NOTICE_EVENT_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Deletes the event(s)
     *
     * @access  public
     * @return  mixed   Response array
     */
    function DeleteEvents()
    {
        $events = $this->gadget->request->fetch('ids:array');
        if (empty($events)) {
            return $this->gadget->session->response(_t('EVENTSCALENDAR_ERROR_EVENT_DELETE'), RESPONSE_ERROR);
        }

        // Delete events
        $model = $this->gadget->model->load('Event');
        $res = $model->DeleteEvents($events);
        if (Jaws_Error::IsError($res)) {
            return $this->gadget->session->response(_t('EVENTSCALENDAR_ERROR_EVENT_DELETE'), RESPONSE_ERROR);
        }

        $msg = (count($events) === 1)?
            _t('EVENTSCALENDAR_NOTICE_EVENT_DELETED') :
            _t('EVENTSCALENDAR_NOTICE_EVENTS_DELETED');
        return $this->gadget->session->response($msg, RESPONSE_NOTICE);
    }
}