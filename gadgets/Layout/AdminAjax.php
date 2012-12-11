<?php
/**
 * Layout AJAX API
 *
 * @category   Ajax
 * @package    Layout
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class LayoutAdminAjax extends Jaws_Gadget_Ajax
{
    /**
     * Move item
     *
     * @access  public
     * @param   int     $item           Item ID
     * @param   string  $old_section    Old section name
     * @param   int     $old_position   Position of item in old section
     * @param   string  $new_section    Old section name
     * @param   int     $new_position   Position of item in new section
     * @return  array   Response array (notice or error)
     */
    function MoveElement($item, $old_section, $old_position, $new_section, $new_position)
    {
        $result = $this->_Model->MoveElement($item, $old_section, $old_position, $new_section, $new_position);
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
     * @param   int     $item       Item ID
     * @param   string  $section    Section name
     * @param   int     $position   Position of item in section
     * @return  array   Response array (notice or error)
     */
    function DeleteElement($item, $section, $position)
    {
        $result = $this->_Model->DeleteElement($item, $section, $position);
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
     * @param   int     $item   Item ID
     * @param   string  $dw     Display in these gadgets
     * @return  array   Response
     */
    function ChangeDisplayWhen($item, $dw) 
    {
        $res = $this->_Model->ChangeDisplayWhen($item, $dw);
        if ($res === false) {
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
     * @params  string  $gadget
     * @return  array   Actions of the given gadget
     */
    function GetGadgetActions($gadget)
    {
        return $this->_Model->GetGadgetLayoutActions($gadget);
    }

    /**
     * Add gadget to layout 
     *
     * @access  public
     * @params  string  $gadget
     * @params  string  $action
     * @params  string  $params
     * @return  array   Details of the added gadget/action
     */
    function AddGadget($gadget, $action, $params)
    {
        $res = array();
        $id = false;
        $actions = $this->_Model->GetGadgetLayoutActions($gadget, true);
        if (isset($actions[$action])) {
            $id = $this->_Model->NewElement('main', $gadget, $action, $params, $actions[$action]['file']);
        }
        if ($id === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_ELEMENT_ADDED'), RESPONSE_ERROR);
            $res['success'] = false;
        } else {
            $el = $this->_Model->GetElement($id);
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ELEMENT_ADDED'), RESPONSE_NOTICE);
            $info = $GLOBALS['app']->LoadGadget($gadget, 'Info');
            $el['tname'] = $info->GetName();
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
            $el['icon']      = 'gadgets/'.$gadget.'/images/logo.png';
            $el['delete']    = 'deleteElement(\''.$id.'\',\''._t('LAYOUT_CONFIRM_DELETE').'\');';
            $el['deleteimg'] = 'gadgets/Layout/images/delete-item.gif';
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
     * @param   int     $item   Item ID
     * @params  string  $action
     * @return  array   Response
     */
    function EditElementAction($item, $gadget, $action, $params) 
    {
        $res = false;
        $actions = $this->_Model->GetGadgetLayoutActions($gadget, true);
        if (isset($actions[$action])) {
            $res = $this->_Model->EditElementAction($item, $action, $params, $actions[$action]['file']);
        }
        if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ERROR_ELEMENT_UPDATED'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_ELEMENT_UPDATED'), RESPONSE_NOTICE);
        }
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}