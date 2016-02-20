<?php
/**
 * Activities event
 *
 * @category    Gadget
 * @package     Activities
 */
class Activities_Events_Activities extends Jaws_Gadget_Event
{
    /**
     * Grabs activities and save to DB
     *
     * @access  public
     * @param   string  $shouter    The shouting gadget
     * @param   array   $params     [user, group, title, summary, description, priority, send]
     * @return  bool
     */
    function Execute($shouter, $params)
    {
        if (!isset($params['action']) || empty($params['action'])) {
            return false;
        }

        $model = $this->gadget->model->load('Activities');
        $params['hits'] = !isset($params['hits']) ? 1 : $params['hits'];

        $res = $model->InsertActivities(
            array(
                'gadget' => $shouter,
                'action' => $params['action'],
                'hits' => $params['hits']
            )
        );
        if (Jaws_Error::IsError($res)) {
            return $res;
        }
        return true;

        return false;
    }
}
