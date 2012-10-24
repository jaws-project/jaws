<?php
/**
 * Languages AJAX API
 *
 * @category   Ajax
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class LanguagesAdminAjax extends Jaws_Gadget_Ajax
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
        $this->CheckSession('Languages', 'ModifyLanguageProperties');
        $this->_Model->SaveLanguage($lang_str);
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
        $gadget = $GLOBALS['app']->LoadGadget('Languages', 'AdminHTML');
        $component = explode('|', $component);
        $component[1] = preg_replace("/[^A-Za-z0-9]/", '', $component[1]);
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
        $request =& Jaws_Request::getInstance();
        $data = $request->get(2, 'post', false);

        $component = explode('|', $component);
        $component[1] = preg_replace("/[^A-Za-z0-9]/", '', $component[1]);
        $this->_Model->SetLangData($component[1], (int)$component[0], $langTo, $data);
        return $GLOBALS['app']->Session->PopLastResponse();
    }
}