<?php
/**
 * Layout Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Element extends Jaws_Gadget_Action
{
    /**
     * Adds layout element
     *
     * @access  public
     * @return  XHTML template content
     */
    function AddLayoutElement()
    {
        $user = jaws()->request->fetch('user', 'get');
        // dashboard_user
        if (empty($user) && $this->gadget->GetPermission('ManageLayout')) {
            $user = 0;
        } else {
            $GLOBALS['app']->Session->CheckPermission('Users', 'ManageDashboard');
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        }

        $tpl = $this->gadget->template->load('AddGadget.html');
        $tpl->SetBlock('template');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $GLOBALS['app']->GetSiteURL('/');

        $tpl->SetVariable('BASE_URL', $base_url);
        $tpl->SetVariable('.dir', $dir);
        $tpl->SetVariable('.browser', $brow);
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $tpl->SetVariable('gadgets', _t('LAYOUT_GADGETS'));
        $tpl->SetVariable('actions', _t('LAYOUT_ACTIONS'));
        $tpl->SetVariable('no_actions_msg', _t('LAYOUT_NO_GADGET_ACTIONS'));
        $addButton =& Piwi::CreateWidget('Button', 'add',_t('LAYOUT_NEW'), STOCK_ADD);
        $addButton->AddEvent(ON_CLICK, "getAction();");
        $tpl->SetVariable('add_button', $addButton->Get());

        $section = jaws()->request->fetch('section', 'post');
        if (is_null($section)) {
            $section = jaws()->request->fetch('section', 'get');
            $section = !is_null($section) ? $section : '';
        }

        $tpl->SetVariable('section', $section);

        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadget_list = $cmpModel->GetGadgetsList(null, true, true, true);

        //Hold.. if we dont have a selected gadget?.. like no gadgets?
        if (count($gadget_list) <= 0) {
            Jaws_Error::Fatal('You don\'t have any installed gadgets, please enable/install one and then come back',
                __FILE__, __LINE__);
        }

        reset($gadget_list);
        $first = current($gadget_list);
        $tpl->SetVariable('first', $first['name']);

        $tpl->SetBlock('template/working_notification');
        $tpl->SetVariable('loading-message', _t('GLOBAL_LOADING'));
        $tpl->ParseBlock('template/working_notification');

        foreach ($gadget_list as $gadget) {
            $tpl->SetBlock('template/gadget');
            $tpl->SetVariable('id',     $gadget['name']);
            $tpl->SetVariable('icon',   'gadgets/'.$gadget['name'].'/Resources/images/logo.png');
            $tpl->SetVariable('gadget', $gadget['title']);
            $tpl->SetVariable('desc',   $gadget['description']);
            $tpl->ParseBlock('template/gadget');
        }

        $tpl->ParseBlock('template');

        return $tpl->Get();
    }

    /**
     * Changes action of a given gadget
     *
     * @access  public
     * @return  XHTML template content
     */
    function ElementAction()
    {
        $rqst = jaws()->request->fetch(array('id', 'user'), 'get');
        // dashboard_user
        if (empty($rqst['user']) && $this->gadget->GetPermission('ManageLayout')) {
            $user = 0;
        } else {
            $GLOBALS['app']->Session->CheckPermission('Users', 'ManageDashboard');
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        }

        $model = $this->gadget->model->loadAdmin('Elements');
        $layoutElement = $model->GetElement($rqst['id'], $user);
        if (!$layoutElement || !isset($layoutElement['id'])) {
            return false;
        }
        $id = $layoutElement['id'];

        $tpl = $this->gadget->template->load('EditGadget.html');
        $tpl->SetBlock('template');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $GLOBALS['app']->GetSiteURL('/');

        $tpl->SetVariable('BASE_URL', $base_url);
        $tpl->SetVariable('.dir', $dir);
        $tpl->SetVariable('.browser', $brow);
        $tpl->SetVariable('base_script', BASE_SCRIPT);

        $gInfo = Jaws_Gadget::getInstance($layoutElement['gadget']);
        if (Jaws_Error::isError($gInfo)) {
            return false;
        }

        $tpl->SetVariable('gadget', $layoutElement['gadget']);
        $tpl->SetVariable('gadget_name', $gInfo->title);
        $tpl->SetVariable('gadget_description', $gInfo->description);

        $btnSave =& Piwi::CreateWidget('Button', 'ok',_t('GLOBAL_SAVE'), STOCK_SAVE);
        $btnSave->AddEvent(ON_CLICK, "getAction('{$id}', '{$layoutElement['gadget']}');");
        $tpl->SetVariable('save', $btnSave->Get());

        $actionsList =& Piwi::CreateWidget('RadioButtons', 'action_field', 'vertical');
        $actions = $model->GetGadgetLayoutActions($layoutElement['gadget']);
        if (count($actions) > 0) {
            foreach ($actions as $aIndex => $action) {
                $tpl->SetBlock('template/gadget_action');
                $tpl->SetVariable('aindex', $aIndex);
                $tpl->SetVariable('name',   $action['name']);
                $tpl->SetVariable('action', $action['action']);
                $tpl->SetVariable('desc',   $action['desc']);
                $action_selected = $layoutElement['gadget_action'] == $action['action'];
                if($action_selected) {
                    $tpl->SetVariable('action_checked', 'checked="checked"');
                } else {
                    $tpl->SetVariable('action_checked', '');
                }

                if (!empty($action['params'])) {
                    $action_params = unserialize($layoutElement['action_params']);
                    foreach ($action['params'] as $pIndex => $param) {
                        $tpl->SetBlock('template/gadget_action/action_param');
                        $param_name = "action_{$aIndex}_param_{$pIndex}";
                        switch (gettype($param['value'])) {
                            case 'integer':
                            case 'double':
                            case 'string':
                                $element =& Piwi::CreateWidget('Entry', $param_name, $param['value']);
                                $element->SetID($param_name);
                                $element->SetStyle('width:120px;');
                                if ($action_selected) {
                                    $element->SetValue($action_params[$pIndex]);
                                }
                                break;

                            case 'boolean':
                                $element =& Piwi::CreateWidget('CheckButtons', $param_name);
                                $element->AddOption('', 1, $param_name);
                                if ($action_selected && $action_params[$pIndex]) {
                                    $element->setDefault($action_params[$pIndex]);
                                }
                                break;

                            default:
                                $element =& Piwi::CreateWidget('Combo', $param_name);
                                $element->SetID($param_name);
                                foreach ($param['value'] as $value => $title) {
                                    $element->AddOption($title, $value);
                                }
                                if ($action_selected) {
                                    $element->SetDefault($action_params[$pIndex]);
                                }
                        }

                        $tpl->SetVariable('aindex', $aIndex);
                        $tpl->SetVariable('pindex', $pIndex);
                        $tpl->SetVariable('ptitle', $param['title']);
                        $tpl->SetVariable('param',  $element->Get());
                        $tpl->ParseBlock('template/gadget_action/action_param');
                    }
                }

                $tpl->ParseBlock('template/gadget_action');
            }
        } else {
            $tpl->SetBlock('template/no_action');
            $tpl->SetVariable('no_gadget_desc', _t('LAYOUT_NO_GADGET_ACTIONS'));
            $tpl->ParseBlock('template/no_action');
        }

        $tpl->ParseBlock('template');
        return $tpl->Get();
    }

    /**
     * Update layout's element action
     * 
     * @access  public
     * @return  array   Response
     */
    function UpdateElementAction() 
    {
        $res = false;
        @list($item, $gadget, $action, $params, $user) = jaws()->request->fetchAll('post');
        $params = jaws()->request->fetch('3:array', 'post');
        // dashboard_user
        if (empty($user) && $this->gadget->GetPermission('ManageLayout')) {
            $user = 0;
        } else {
            $GLOBALS['app']->Session->CheckPermission('Users', 'ManageDashboard');
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        }

        $eModel = $this->gadget->model->loadAdmin('Elements');
        $lModel = $this->gadget->model->loadAdmin('Layout');
        $actions = $eModel->GetGadgetLayoutActions($gadget, true);
        if (isset($actions[$action])) {
            $res = $lModel->UpdateElementAction($item, $action, $params, $actions[$action]['file'], $user);
            $res = Jaws_Error::IsError($res)? false : true;
        }
        if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_ELEMENT_UPDATED'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ELEMENT_UPDATED'), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}