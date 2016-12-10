<?php
/**
 * EventsCalendar - Search gadget hook
 *
 * @category    GadgetHook
 * @package     EventsCalendar
 */
class EventsCalendar_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets search fields of the gadget
     *
     * @access  public
     * @return  array   List of search fields
     */
    function GetOptions() {
        return array(
            'eventscalendar' => array('subject', 'location', 'description'),
        );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $table      Table name
     * @param   object  $objORM     Jaws_ORM instance object
     * @return  mixed   An array of entries that matches a certain pattern or false on failure
     */
    function Execute($table, &$objORM)
    {
        $objORM->table('ec_events');
        $objORM->select('id', 'subject', 'description', 'updatetime:integer');
        $objORM->where('user', 0);
        $objORM->and()->loadWhere('search.terms');
        $result = $objORM->orderBy('id desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $events = array();
        foreach ($result as $p) {
            $event = array();
            $event['title']   = $p['subject'];
            $event['url']     = $this->gadget->urlMap('ViewEvent', array('event'  => $p['id']));
            $event['image']   = 'gadgets/EventsCalendar/Resources/images/logo.png';
            $event['snippet'] = $p['description'];
            $event['date']    = $p['updatetime'];
            $stamp            = $p['updatetime'];
            $events[$stamp]   = $event;
        }

        return $events;
    }
}