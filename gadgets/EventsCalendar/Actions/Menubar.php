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
            $menubar->AddOption('Events', _t('EVENTSCALENDAR_PUBLIC_EVENTS'),
                $this->gadget->urlMap('ViewYear'),
                'gadgets/EventsCalendar/Resources/images/calendar.png'
            );
            if ($action !== 'ManageEvents' && $GLOBALS['app']->Session->Logged()) {
                $menubar->AddOption('ManageEvents', _t('EVENTSCALENDAR_EVENTS_MANAGE'),
                    $this->gadget->urlMap('ManageEvents', array('user' => $user)),
                    'gadgets/EventsCalendar/Resources/images/events.png'
                );
            }
        } else {
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            if ($user > 0) {
                $menubar->AddOption('Events', _t('EVENTSCALENDAR_MY_EVENTS'),
                    $this->gadget->urlMap('ViewYear', array('user' => $user)),
                    'gadgets/EventsCalendar/Resources/images/calendar.png'
                );
            }
        }

        return $menubar->Get();
    }
}