<?php
/**
 * EventsCalendar user's activities hook
 *
 * @category    GadgetHook
 * @package     EventsCalendar
 */
class EventsCalendar_Hooks_Users extends Jaws_Gadget_Hook
{
    /**
     * Returns User activity in EventsCalendar
     *
     * @access  public
     * @param   int     $uid    User's ID
     * @param   string  $uname  User's name
     * @return  array   An array of user activity
     */
    function Execute($uid, $uname)
    {
        $entity = array();
        $model = $this->gadget->model->load('Events');
        $eventsCount = $model->GetEvents(array('user' => $uid), true);
        if ($eventsCount > 0) {
            $entity[0]['title'] = _t('EVENTSCALENDAR_EVENT');
            $entity[0]['count'] = $eventsCount;
            $entity[0]['url'] = $this->gadget->urlMap('ViewYear', array('user' => $uid));
        }

        return $entity;
    }
}