<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Actions_Menubar extends Jaws_Gadget_Action
{
    /**
     * Displays menu bar according to the selected action
     *
     * @access  public
     * @param   string  $action  Selected action
     * @param   int     $user    User ID
     * @return  string  HTML UI
     */
    function Menubar($action = null, $user = 0)
    {
        $menubar = new Jaws_Widgets_Menubar();

        if ($user > 0) {
            $menubar->AddOption('Events', $this::t('PUBLIC_EVENTS'),
                $this->gadget->urlMap('ViewYear'),
                'gadgets/EventsCalendar/Resources/images/calendar.png'
            );
            if ($action !== 'ManageEvents' && $this->app->session->user->logged) {
                $menubar->AddOption('ManageEvents', $this::t('EVENTS_MANAGE'),
                    $this->gadget->urlMap('ManageEvents', array('user' => $user)),
                    'gadgets/EventsCalendar/Resources/images/events.png'
                );
            }
        } else {
            $user = (int)$this->app->session->user->id;
            if ($user > 0) {
                $menubar->AddOption('Events', $this::t('MY_EVENTS'),
                    $this->gadget->urlMap('ViewYear', array('user' => $user)),
                    'gadgets/EventsCalendar/Resources/images/calendar.png'
                );
            }
        }

        return $menubar->Get();
    }
}