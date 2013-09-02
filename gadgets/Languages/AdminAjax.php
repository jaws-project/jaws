<?php
/**
 * Languages AJAX API
 *
 * @category   Ajax
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Languages_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Saves language
     *
     * @access  public
     *
     * @param   string  $lang_str   Language code and name
     * @return  array   Response array (notice or error)
     */
    function SaveLanguage($lang_str)
    {
        $this->gadget->CheckPermission('ModifyLanguageProperties');
        $model = $GLOBALS['app']->LoadGadget('Languages', 'AdminModel', 'Languages');
        $model->SaveLanguage($lang_str);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Generates Language Data UI
     *
     * @access  public
     *
     * @param   string  $component  Component name
     * @param   string  $langTo     Slave language code
     * @return  string  XHTML template content
     */
    function GetLangDataUI($component, $langTo)
    {
        $component = explode('|', $component);
        $component[1] = preg_replace("/[^A-Za-z0-9]/", '', $component[1]);
        $gadget = $GLOBALS['app']->LoadGadget('Languages', 'AdminHTML', 'Languages');
        return $gadget->GetLangDataUI($component[1], (int)$component[0], $langTo);
    }

    /**
     * Sets language data
     *
     * @access  public
     * @param   string  $component
     * @param   string  $langTo
     * @param   string  $data
     * @return  array   Response array (notice or error)
     */
    function SetLangData($component, $langTo, $data)
    {
        $data = jaws()->request->get('2:array', 'post', false);
        $component = explode('|', $component);
        $component[1] = preg_replace("/[^A-Za-z0-9]/", '', $component[1]);
        $model = $GLOBALS['app']->LoadGadget('Languages', 'AdminModel', 'Languages');
        $model->SetLangData($component[1], (int)$component[0], $langTo, $data);
        return $GLOBALS['app']->Session->PopLastResponse();
    }
}