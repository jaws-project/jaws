<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Actions_Menubar extends Jaws_Gadget_Action
{
    /**
     * Displays menu bar according to selected action
     *
     * @access  public
     * @param   string  $action  Selected action
     * @return  string  XHTML UI
     */
    function MenuBar($action = null)
    {
        $menubar = new Jaws_Widgets_Menubar();

        $menubar->AddOption('ManageEvents',_t('EVENTSCALENDAR_EVENTS_MANAGE'),
            $this->gadget->urlMap('ManageEvents'), 'gadgets/EventsCalendar/Resources/images/events.png');

        $menubar->AddOption('Events',_t('EVENTSCALENDAR_CALENDAR'),
            $this->gadget->urlMap('ViewYear'), 'gadgets/EventsCalendar/Resources/images/calendar.png');

        if (!empty($action)) {
            $menubar->Activate($action);
        }

        return $menubar->Get();
    }
}