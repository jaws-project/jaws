<?php
/**
 * Languages AJAX API
 *
 * @category   Ajax
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Languages_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Saves language
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function SaveLanguage()
    {
        $this->gadget->CheckPermission('ModifyLanguageProperties');
        @list($lang_str) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Languages');
        $model->SaveLanguage($lang_str);
        return $this->gadget->session->pop();
    }

    /**
     * Generates Language Data UI
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetLangDataUI()
    {
        @list($component, $langTo) = $this->gadget->request->fetchAll('post');
        $component = explode('|', $component);
        $component[1] = preg_replace("/[^A-Za-z0-9]/", '', $component[1]);
        $gadget = $this->gadget->action->loadAdmin('Languages');
        return $gadget->GetLangDataUI($component[1], (int)$component[0], $langTo);
    }

    /**
     * Sets language data
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function SetLangData()
    {
        @list($component, $langTo, $data) = $this->gadget->request->fetchAll('post');
        $data = $this->gadget->request->fetch('2:array', 'post', false);
        $component = explode('|', $component);
        $component[1] = preg_replace("/[^A-Za-z0-9]/", '', $component[1]);
        $model = $this->gadget->model->loadAdmin('Languages');
        $model->SetLangData($component[1], (int)$component[0], $langTo, $data);
        return $this->gadget->session->pop();
    }
}