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
     * @return  mixed   Activity ID or Jaws_Error on failure
     */
    function Execute($shouter, $params)
    {
        if (!isset($params['action']) || empty($params['action'])) {
            return false;
        }

        $model = $this->gadget->model->load('Activities');
        $params['hits'] = !isset($params['hits']) ? 1 : $params['hits'];

        return $model->InsertActivity(
            array(
                'gadget' => $shouter,
                'action' => $params['action'],
                'hits' => $params['hits']
            )
        );
    }

}