<?php
/**
 * EventsCalendar Admin HTML file
 *
 * @category    GadgetAdmin
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016-2022 Jaws Development Group
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

        $baseUrl = BASE_SCRIPT . '?reqGadget=EventsCalendar&amp;reqAction=';
        $menuImage = 'gadgets/EventsCalendar/Resources/images/calendar.png';
        $menubar = new Jaws_Widgets_Menubar();

        $menubar->AddOption('PublicEvents',$this::t('PUBLIC_EVENTS'),
            $baseUrl . 'PublicEvents', $menuImage);

        $menubar->AddOption('UserEvents',$this::t('USER_EVENTS'),
            $baseUrl . 'UserEvents', $menuImage);

        $menubar->Activate($action);

        return $menubar->Get();
    }
}