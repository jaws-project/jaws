<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Actions_Reminder extends Jaws_Gadget_Action
{
    /**
     * Gets Reminder action params
     *
     * @access  public
     * @return  array   List of Calendar action params
     */
    function ReminderLayoutParams()
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
     * Displays events ahead
     *
     * @access  public
     * @param   string  $user   Reminder type [public|user]
     * @return  string  XHTML UI
     */
    function Reminder($user)
    {
        if ($user === 'user' && !$this->app->session->user->logged) {
            return '';
        }

        $this->title = $this::t('EVENTS');

        $assigns = array();

        // Menu navigation
        $assigns['navigation'] = $this->gadget->action->load('MenuNavigation')->xnavigation();

        $assigns['user'] = $this->app->session->user->id;

        // Fetch events
        $model = $this->gadget->model->load('Reminder');
        if ($this->app->session->user->logged) {
            $events = $model->GetUserEvents($this->app->session->user->id, time());
        } else {
            $events = $model->GetPublicEvents(time());
        }
        if (Jaws_Error::IsError($events)) {
            $events = array();
        }

        $assigns['events'] = $events;

        return $this->gadget->template->xLoad('Reminder.html')->render($assigns);
    }

}