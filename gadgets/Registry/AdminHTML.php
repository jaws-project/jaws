<?php
/**
 * Registry Core Gadget Admin
 *
 * @category   Gadget
 * @package    Registry
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Registry_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action(MainMenu)
     *
     * @access  public
     * @return  string template content
     */
    function DefaultAction()
    {
        return $this->View();
    }

    /**
     * Returns the admin template of registry
     *
     * @access  public
     * @return  string  Template content
     */
    function Admin()
    {
        return $this->View();
    }

    /**
     * Returns the admin template of registry
     *
     * @access  public
     * @return  string  Template content
     */
    function EditACL()
    {
        return $this->View();
    }

    /**
     * Returns the admin template of registry
     *
     * @access  public
     * @return  string  Template content
     */
    function EditRegistry()
    {
        return $this->View();
    }

    /**
     * Prepares the registry menubar
     *
     * @access  public
     * @param   string  $action  Selected action
     * @return  string  Template content
     */
    function MenuBar($action)
    {
        $actions = array('EditRegistry', 'EditACL');
        if (!in_array($action, $actions)) {
            $action = 'EditRegistry';
        }

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();
        $menubar->AddOption('EditRegistry', _t('REGISTRY_EDIT_REGISTRY'), BASE_SCRIPT . '?gadget=Registry&amp;action=EditRegistry',
                            STOCK_SAVE);
        $menubar->AddOption('EditACL', _t('REGISTRY_EDIT_ACL'), BASE_SCRIPT . '?gadget=Registry&amp;action=EditACL',
                            STOCK_SAVE);
        $menubar->Activate($action);

        return $menubar->Get();
    }

    /**
     * Allows users to view and edit the registry.
     *
     * @access  public
     * @return  string content
     */
    function View()
    {
        $this->AjaxMe('script.js');
        $GLOBALS['app']->Layout->AddScriptLink('libraries/xtree/xtree.js');

        $tpl = new Jaws_Template('gadgets/Registry/templates/');
        $tpl->Load('Registry.html');
        $tpl->SetBlock('registry');
        $tpl->SetVariable('alertregistry', _t('REGISTRY_DISCLAIMER'));

        $request =& Jaws_Request::getInstance();
        $action  = $request->get('action', 'get');

        if ($action == 'EditACL') {
            $tpl->SetVariable('uisection', 'acl');
        } else {
            $tpl->SetVariable('uisection', 'registry');
        }
        $tpl->SetVariable('menubar', $this->MenuBar($action));
        $tpl->SetVariable('aclMsg', _t('REGISTRY_ACL'));
        $tpl->SetVariable('registryMsg', _t('REGISTRY_REGISTRY'));

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldSet = new Jaws_Widgets_FieldSet(_t('GLOBAL_EDIT'));
        $fieldSet->SetDirection('vertical');

        $kName =& Piwi::CreateWidget('Entry', 'key_name', '');
        $kName->SetTitle(_t('REGISTRY_KEY'));
        $kName->SetReadOnly(true);
        $kName->SetStyle('background-color: #f0f0f0; direction: ltr;');
        $kValue =& Piwi::CreateWidget('Entry', 'key_value', '');
        $kValue->SetStyle('direction: ltr;');
        $kValue->SetTitle(_t('REGISTRY_VALUE'));

        $buttons =& Piwi::CreateWidget('HBox');
        $buttons->SetStyle('float: right;');

        $save =& Piwi::CreateWidget('Button', 'save_key', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript: saveKey(this.form);');

        $cancel =& Piwi::CreateWidget('Button', 'cancel_key', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, 'javascript: cancelKey(this.form);');

        $buttons->Add($cancel);
        $buttons->Add($save);

        $fieldSet->Add($kName);
        $fieldSet->Add($kValue);

        $form->Add($fieldSet);
        $form->Add($buttons);

        $tpl->SetVariable('form', $form->Get());
        $tpl->ParseBlock('registry');

        return $tpl->Get();
    }
}