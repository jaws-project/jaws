<?php
/**
 * Shoutbox Gadget
 *
 * @category   GadgetAdmin
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Returns the default administration action to use if none is specified.
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Admin()
    {
        return $this->ManageComments();
    }

    /**
     * Displays shoutbox admin (comments manager)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageComments()
    {
        $GLOBALS['app']->Layout->AddScriptLink('gadgets/Comments/resources/script.js');

        $cHTML = $GLOBALS['app']->LoadGadget('Comments', 'AdminHTML');
        return $cHTML->Comments('shoutbox', $this->MenuBar('Comments'));
    }


    /**
     * Prepares the shoutbox menubar
     *
     * @access  public
     * @param   string  $action   Selected action
     * @return  string  XHTML of menubar
     */
    function MenuBar($action)
    {
        $actions = array('Comments', 'Settings');
        if (!in_array($action, $actions)) {
            $action = 'Comments';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption(
            'Comments',
            _t('COMMENTS_NAME'),
            BASE_SCRIPT . '?gadget=Shoutbox&amp;action=Admin');
        if ($this->gadget->GetPermission('Settings')) {
            $menubar->AddOption(
                'Settings',
                _t('GLOBAL_SETTINGS'),
                BASE_SCRIPT . '?gadget=Shoutbox&amp;action=Settings',
                STOCK_PREFERENCES);
        }
        $menubar->Activate($action);
        return $menubar->Get();

        return $menubar->Get();
    }

}
