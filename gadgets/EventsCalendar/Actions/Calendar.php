<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016-2024 Jaws Development Group
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
                'title' => $this::t('EVENTS'),
                'value' => array(
                    'user' => $this::t('USER_EVENTS'),
                    'public' => $this::t('PUBLIC_EVENTS')
                )
            ),
        );
    }

    /**
     * Displays public or user events
     *
     * @access  public
     * @param   string  $user   Calendar type [public|user]
     * @return string XHTML UI
     */
    function Calendar($user)
    {
        if (!$this->app->session->user->logged) {
            return '';
        }

        $user = ($user === 'public')? 0 : (int)$this->app->session->user->id;
        $action = $this->gadget->action->load('ViewYear');
        return $action->ViewYear($user);
    }
}