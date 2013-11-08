<?php
/**
 * Layout AJAX API
 *
 * @category   Ajax
 * @package    Layout
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_AdminAjax extends Jaws_Gadget_Action
{
    /**
     * Move item
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function MoveElement()
    {
        @list($item, $old_section, $old_position, $new_section, $new_position) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Elements');
        $result = $model->MoveElement(
            $item,
            $old_section,
            (int)$old_position,
            $new_section,
            (int)$new_position
        );
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ELEMENT_MOVED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes an element
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteElement()
    {
        @list($item, $section, $position) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Elements');
        $result = $model->DeleteElement($item, $section, $position);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ELEMENT_DELETED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Change when to display a gadget
     * 
     * @access  public
     * @return  array   Response
     */
    function ChangeDisplayWhen() 
    {
        @list($item, $dw) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Elements');
        $res = $model->ChangeDisplayWhen($item, $dw);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_CHANGE_WHEN'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ELEMENT_CHANGE_WHEN'), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get actions of a given gadget
     *
     * @access  public
     * @return  array   Actions of the given gadget
     */
    function GetGadgetActions()
    {
        @list($gadget) = jaws()->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Elements');
        return $model->GetGadgetLayoutActions($gadget);
    }

    /**
     * Add gadget to layout 
     *
     * @access  public
     * @return  array   Details of the added gadget/action
     */
    function AddGadget()
    {
        $res = array();
        $id = false;
        @list($gadget, $action, $params) = jaws()->request->fetchAll('post');
        $params = jaws()->request->fetch('2:array', 'post');
        $model = $this->gadget->model->loadAdmin('Elements');
        $actions = $model->GetGadgetLayoutActions($gadget, true);
        if (isset($actions[$action])) {
            $user = (int)$GLOBALS['app']->Session->GetAttribute('layout');
            $id = $model->NewElement('main', $gadget, $action, $params, $actions[$action]['file'], '', $user);
            $id = Jaws_Error::IsError($id)? false : $id;
        }
        if ($id === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_ELEMENT_ADDED'), RESPONSE_ERROR);
            $res['success'] = false;
        } else {
            $el = $model->GetElement($id);
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ELEMENT_ADDED'), RESPONSE_NOTICE);
            $info = Jaws_Gadget::getInstance($gadget);
            $el['tname'] = $info->title;
            if (isset($actions[$action])) {
                $el['taction'] = $actions[$action]['name'];
                $el['tactiondesc'] = $actions[$action]['desc'];
            } else {
                $el['taction'] =  _t('LAYOUT_ACTION');
                $el['tactiondesc'] = '';
            }

            $el['eaid'] = 'ea'.$id;
            $url_ea = BASE_SCRIPT. '?gadget=Layout&action=EditElementAction&id='.$id;
            $el['eaonclick'] = "editElementAction('$url_ea');";
            unset($info);
            $el['icon']      = 'gadgets/'.$gadget.'/Resources/images/logo.png';
            $el['delete']    = "deleteElement('{$id}');";
            $el['deleteimg'] = 'gadgets/Layout/Resources/images/delete-item.gif';
            $el['dwalways']  = _t('GLOBAL_ALWAYS');
            $el['dwtitle']   = _t('LAYOUT_CHANGE_DW');
            $el['dwdisplay'] = _t('LAYOUT_DISPLAY_IN') . ': ';
            $el['dwid'] = 'dw'.$id;
            $url_dw = BASE_SCRIPT. '?gadget=Layout&action=ChangeDisplayWhen&id='.$id;
            $el['dwonclick'] = "changeDisplayWhen('$url_dw');";
            $res = $el;
            $res['success'] = true;
        }
        $res['message'] = $GLOBALS['app']->Session->PopLastResponse();
        return $res;
    }

    /**
     * Edit layout's element action
     * 
     * @access  public
     * @return  array   Response
     */
    function EditElementAction() 
    {
        $res = false;
        @list($item, $gadget, $action, $params) = jaws()->request->fetchAll('post');
        $params = jaws()->request->fetch('3:array', 'post');
        $eModel = $this->gadget->model->loadAdmin('Elements');
        $lModel = $this->gadget->model->loadAdmin('Layout');
        $actions = $eModel->GetGadgetLayoutActions($gadget, true);
        if (isset($actions[$action])) {
            $res = $lModel->EditElementAction($item, $action, $params, $actions[$action]['file']);
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