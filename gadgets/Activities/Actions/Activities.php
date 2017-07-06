<?php
/**
 * Activities Gadget
 *
 * @category    Gadget
 * @package     Subscription
 */
class Activities_Actions_Activities extends Jaws_Gadget_Action
{
    /**
     * Send data to parent site
     *
     * @access  public
     * @return  boolean
     */
    function Activities()
    {
        // Load the template
        $tpl = $this->gadget->template->load('Activities.html');
        $tpl->SetBlock('Activities');
        $tpl->SetVariable('title', _t('ACTIVITIES_ACTIONS_ACTIVITIES'));
        $this->SetTitle(_t('ACTIVITIES_ACTIONS_ACTIVITIES'));

        $model = $this->gadget->model->load('Activities');

        $filters = array();
        $today = getdate();
        $todayTime = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
        $filters['domain'] = ''; // fetch just own domain data
        $filters['from_date'] = $todayTime; // fetch today data
        $activities = $model->GetActivities($filters);

        if (!Jaws_Error::isError($activities) && !empty($activities)) {
            $gadgetsActivities = array();
            $gadget = '';
            foreach ($activities as $activity) {
                if ($activity['gadget'] != $gadget) {
                    $gadget = $activity['gadget'];
                }
                $gadgetsActivities[$gadget][$activity['action']] = $activity['hits'];
            }
        }

        $gadgets = $model->GetHookedGadgets();
        if(count($gadgets)>0) {
            foreach ($gadgets as $gadget => $gTitle) {
                // load gadget
                $objGadget = Jaws_Gadget::getInstance($gadget);
                if (Jaws_Error::IsError($objGadget)) {
                    continue;
                }
                // load hook & execute hook
                $actions = $objGadget->hook->load('Activities')->Execute();
                if (Jaws_Error::IsError($actions)) {
                    continue;
                }

                $tpl->SetBlock('Activities/gadget');
                $tpl->SetVariable('gadget_title', $objGadget->title);
                foreach ($actions as $actionName => $actionTitle) {
                    $tpl->SetBlock('Activities/gadget/action');
                    $tpl->SetVariable('action', $actionTitle);
                    $hits = isset($gadgetsActivities[$gadget][$actionName]) ?
                        $gadgetsActivities[$gadget][$actionName] : 0;
                    $tpl->SetVariable('hits', $hits);
                    $tpl->ParseBlock('Activities/gadget/action');

                }
                $tpl->ParseBlock('Activities/gadget');
            }
        } else {
            $tpl->SetBlock('Activities/no_activity');
            $tpl->SetVariable('no_activity', _t('ACTIVITIES_ACTIONS_NOT_FIND_ACTIVITY'));
            $tpl->ParseBlock('Activities/no_activity');
        }

        $tpl->ParseBlock('Activities');
        return $tpl->Get();

    }

    /**
     * Send data to parent site
     *
     * @access  public
     * @return  boolean
     */
    function PostData()
    {
        // Post activities data to parent site
        $hostName = $_SERVER['HTTP_HOST'];
        $parent = $this->gadget->registry->fetch('parent', 'Settings');
        if (empty($parent)) {
            return false;
        }

        $processing = $this->gadget->registry->fetch('processing');
        $lastUpdate = (int)$this->gadget->registry->fetch('last_update');
        $queueMaxTime = (int)$this->gadget->registry->fetch('queue_max_time');
        if ($processing == 'true' && ($lastUpdate + $queueMaxTime) > time()) {
            return false;
        }

        $this->gadget->registry->update('last_update', time());
        $this->gadget->registry->update('processing', 'true');

        $filters = array();
        $filters['sync'] = false;
        $model = $this->gadget->model->load('Activities');
        $activities = $model->GetActivities();
        if (Jaws_Error::IsError($activities) || empty($activities)) {
            $this->gadget->registry->update('processing', 'false');
            return $activities;
        }

        $activityIds = array();
        foreach ($activities as $activity) {
            $activityIds[] = $activity['id'];
        }

        $httpRequest = new Jaws_HTTPRequest();
        $httpRequest->content_type = 'application/json';
        $data = json_encode(array('domain' => $hostName, 'activities' => $activities));
        $result = $httpRequest->rawPostData("http://$parent/activities/get", $data, $retData);
        if (Jaws_Error::IsError($result) || $result != 200) {
            $this->gadget->registry->update('processing', 'false');
            return false;
        }

        // update sync status
        $model->UpdateActivitiesSync($activityIds, true);

        // finish procession
        $this->gadget->registry->update('processing', 'false');
        return $retData;
    }

    /**
     * Receive data from sub site
     *
     * @access  public
     * @return  mixed   Jaws_Error on failure
     */
    function GetData()
    {
        $data = $this->gadget->request->fetch(array('domain', 'activities:array'), 'post');
        $clientDomain = $data['domain'];

        $saData = array();
        foreach ($data['activities'] as $activity) {
            $domain = empty($activity['domain']) ? $clientDomain : $activity['domain'];
            $saData[] = array(
                'domain' => $domain,
                'gadget' => $activity['gadget'],
                'action' => $activity['action'],
                'date' => $activity['date'],
                'hits' => $activity['hits'],
            );
        }

        // insert activities data
        $model = $this->gadget->model->load('Activities');
        return $model->InsertActivities($saData);
    }

}