<?php
/**
 * EventsCalendar - Sitemap hook
 *
 * @category    GadgetHook
 * @package     EventsCalendar
 */
class EventsCalendar_Hooks_Sitemap extends Jaws_Gadget_Hook
{
    /**
     * Fetch items can be included in sitemap
     *
     * @access  public
     * @param   int     $data_type      Data type
     * @param   int     $updated_time   Last updated time
     * @return  mixed   Array of data otherwise Jaws_Error
     */
    function Execute($data_type = 0, $updated_time = 0)
    {
        $result = array(
            '/' => array(
                'id'     => 0,
                'parent' => 0,
                'title'  => _t('EVENTSCALENDAR_TITLE'),
                'url'    => $this->gadget->urlMap('ViewYear', array(), array('absolute' => true))
            ),
            'levels' => array(),
            'items'  => array()
        );
        $result['levels'][] = array(
            'id' => Directory_Info::FILE_TYPE_TEXT,
            'title' => _t('EVENTSCALENDAR_EVENTS'),
            'url' => $this->gadget->urlMap('ViewYear', array(), array('absolute' => true))
        );
        return $result;
    }
}