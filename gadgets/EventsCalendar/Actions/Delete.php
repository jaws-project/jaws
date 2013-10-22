<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/Resources/site_style.css');
class EventsCalendar_Actions_Delete extends Jaws_Gadget_HTML
{
    /**
     * Deletes passed event(s)
     *
     * @access  public
     * @return  mixed   Response array
     */
    function DeleteEvent()
    {
        $id_set = jaws()->request->fetch('id_set');
        $id_set = explode(',', $id_set);
        if (empty($id_set)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        // Verify events & user
        $model = $this->gadget->loadModel('Event');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $verified_nodes = $model->CheckEvents($id_set, $user);
        if (Jaws_Error::IsError($verified_nodes)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        // No events was verified
        if (empty($verified_nodes)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_NO_PERMISSION'),
                RESPONSE_ERROR
            );
        }

        // Delete events
        $model = $this->gadget->loadModel('Event');
        $res = $model->Delete($verified_nodes);
        if (Jaws_Error::IsError($res)) {
            return $GLOBALS['app']->Session->GetResponse(
                _t('EVENTSCALENDAR_ERROR_EVENT_DELETE'),
                RESPONSE_ERROR
            );
        }

        if (count($id_set) !== count($verified_nodes)) {
            $msg = _t('EVENTSCALENDAR_WARNING_DELETE_EVENTS_FAILED');
            // FIXME: we are creating response twice
            $GLOBALS['app']->Session->PushResponse($msg, 'Events.Response', RESPONSE_WARNING);
            return $GLOBALS['app']->Session->GetResponse($msg, RESPONSE_WARNING);
        }

        $msg = (count($id_set) === 1)?
            _t('EVENTSCALENDAR_NOTICE_EVENT_DELETED') :
            _t('EVENTSCALENDAR_NOTICE_EVENTS_DELETED');
        $GLOBALS['app']->Session->PushResponse($msg, 'Events.Response');
        return $GLOBALS['app']->Session->GetResponse($msg);
    }

}