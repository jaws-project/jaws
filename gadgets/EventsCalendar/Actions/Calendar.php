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
class EventsCalendar_Actions_Calendar extends Jaws_Gadget_Action
{
    /**
     * Gets Calendar action params
     *
     * @access  public
     * @return  array   List of Calendar action params
     */
    function CalendarLayoutParams()
    {
        return array(
            array(
                'title' => _t('EVENTSCALENDAR_EVENTS'),
                'value' => array(
                    'user' => _t('EVENTSCALENDAR_USER_EVENTS'),
                    'public' => _t('EVENTSCALENDAR_PUBLIC_EVENTS')
                )
            ),
        );
    }

    /**
     * Displays public or user events
     *
     * @access  public
     * @param   string  $param  Calendar type [public|user]
     * @return string XHTML UI
     */
    function Calendar($param)
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return '';
        }

        $param = ($param === 'public')? 0 : (int)$GLOBALS['app']->Session->GetAttribute('user');
        $action = $this->gadget->action->load('ViewYear');
        return $action->ViewYear($param);
    }
}