<?php
/**
 * Layout AJAX API
 *
 * @category   Ajax
 * @package    Layout
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Ajax extends Jaws_Gadget_Action
{
    /**
     * Move item
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function MoveElement()
    {
        @list($item, $layout, $old_section, $old_position,
            $new_section, $new_position
        ) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Elements');
        $result = $model->MoveElement(
            $item,
            $layout,
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
        @list($item, $layout, $section, $position) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Elements');
        $result = $model->DeleteElement($item, $layout, $section, $position);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ELEMENT_DELETED'), RESPONSE_NOTICE);
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
        @list($gadget) = $this->gadget->request->fetchAll('post');
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
        @list($gadget, $action, $params, $layout) = $this->gadget->request->fetchAll('post');
        $params = $this->gadget->request->fetch('2:array', 'post');
        $model = $this->gadget->model->loadAdmin('Elements');
        $actions = $model->GetGadgetLayoutActions($gadget, true);
        if (isset($actions[$action])) {
            // check permissions
            $layoutACL = ($layout == 'Index.Dashboard')? 'ManageDashboard' : 'ManageLayout';
            if ($GLOBALS['app']->Session->GetPermission('Users', $layoutACL)) {
                $id = $model->NewElement(
                    $layout,
                    null,
                    'main',
                    $gadget,
                    $action,
                    $params,
                    $actions[$action]['file'],
                    ''
                );
                $id = Jaws_Error::IsError($id)? false : $id;
            }
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
            $el['eaonclick'] = BASE_SCRIPT. '?gadget=Layout&action=ElementAction&id='.$id.'&layout='.$layout;
            unset($info);
            $el['icon']      = 'gadgets/'.$gadget.'/Resources/images/logo.png';
            $el['delete']    = "deleteElement('{$id}');";
            $el['deleteimg'] = 'gadgets/Layout/Resources/images/delete-item.gif';
            $el['dwalways']  = _t('GLOBAL_ALWAYS');
            $el['dwtitle']   = _t('LAYOUT_CHANGE_DW');
            $el['dwdisplay'] = _t('LAYOUT_DISPLAY_IN') . ': ';
            $el['dwid'] = 'dw'.$id;
            $el['dwonclick'] = BASE_SCRIPT. '?gadget=Layout&action=DisplayWhen&id='.$id.'&layout='.$layout;
            $res = $el;
            $res['success'] = true;
        }
        $res['message'] = $GLOBALS['app']->Session->PopLastResponse();
        return $res;
    }

}