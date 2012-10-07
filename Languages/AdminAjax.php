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
class LanguagesAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     */
    function LanguagesAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * @access  public
     *
     * @param   string  $lang_str   Language code and name
     * @return  boolean Success/Failure (Jaws_Error)
     */
    function SaveLanguage($lang_str)
    {
        $this->CheckSession('Languages', 'ModifyLanguageProperties');
        $this->_Model->SaveLanguage($lang_str);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * @access  public
     *
     * @param   string  $component  Component name
     * @param   string  $langTo     Slave language code
     * @return  boolean Success/Failure (Jaws_Error)
     */
    function GetLangDataUI($component, $langTo)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Languages', 'AdminHTML');
        $component = explode('|', $component);
        $component[1] = preg_replace("/[^A-Za-z0-9]/", '', $component[1]);
        return $gadget->GetLangDataUI($component[1], (int)$component[0], $langTo);
    }

    /**
     * @access  public
     *
     * @return  boolean Success/Failure (Jaws_Error)
     */
    function SetLangData($component, $langTo, $data)
    {
        $component = explode('|', $component);
        $component[1] = preg_replace("/[^A-Za-z0-9]/", '', $component[1]);
        $this->_Model->SetLangData($component[1], (int)$component[0], $langTo, $data);
        return $GLOBALS['app']->Session->PopLastResponse();
    }
}