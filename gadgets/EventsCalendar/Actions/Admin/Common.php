<?php
/**
 * EventsCalendar Admin HTML file
 *
 * @category    GadgetAdmin
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Actions_Admin_Common extends Jaws_Gadget_Action
{
    /**
     * Displays admin menu bar according to selected action
     *
     * @access  public
     * @param   string  $action    selected action
     * @return  string XHTML template content
     */
    function MenuBar($action)
    {
        $actions = array('PublicEvents', 'UserEvents');
        if (!in_array($action, $actions)) {
            $action = 'PublicEvents';
        }

        $baseUrl = BASE_SCRIPT . '?gadget=EventsCalendar&amp;action=';
        $menuImage = 'gadgets/EventsCalendar/Resources/images/calendar.png';
        $menubar = new Jaws_Widgets_Menubar();

        $menubar->AddOption('PublicEvents',_t('EVENTSCALENDAR_PUBLIC_EVENTS'),
            $baseUrl . 'PublicEvents', $menuImage);

        $menubar->AddOption('UserEvents',_t('EVENTSCALENDAR_USER_EVENTS'),
            $baseUrl . 'UserEvents', $menuImage);

        $menubar->Activate($action);

        return $menubar->Get();
    }
}