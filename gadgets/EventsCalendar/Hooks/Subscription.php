<?php
/**
 * EventsCalendar gadget hook
 *
 * @category    GadgetHook
 * @package     EventsCalendar
 */
class EventsCalendar_Hooks_Subscription extends Jaws_Gadget_Hook
{
    /**
     * Returns available subscription items
     *
     * @access  public
     * @return  array   An array of subscription
     */
    function Execute()
    {
        $options = array();

        // public events
        $options[] = array(
            'selectable' => true,
            'action' => 'ViewYear',
            'reference' => '0',
            'title' => $this::t('PUBLIC_EVENTS'),
            'url' => $this->gadget->urlMap('ViewYear')
        );

        // user events
        $user = (int)$this->app->session->user->id;
        $options[] = array(
            'selectable' => true,
            'action' => 'ViewYear',
            'reference' => $user,
            'title' => $this::t('USER_EVENTS'),
            'url' => $this->gadget->urlMap('ViewYear', array('user' => $user))
        );

        return $options;
    }
}