<?php
/**
 * Settings Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Settings
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Actions_Admin_Meta extends Settings_Actions_Admin_Default
{
    /**
     * Displays meta settings form
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function MetaSettings()
    {
        $this->gadget->CheckPermission('MetaSettings');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('settings');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('sidebar', $this->SideBar('Meta'));
        $tpl->SetVariable('custom_meta', _t('SETTINGS_META_CUSTOM'));
        $tpl->SetVariable('legend', _t('SETTINGS_META_SETTINGS'));

        // Add Button
        $addButton =& Piwi::CreateWidget('Button', 'add', _t('SETTINGS_META_ADD_CUSTOM'), STOCK_ADD);
        $addButton->AddEvent(ON_CLICK, 'javascript:addCustomMeta();');
        $tpl->SetVariable('addButton', $addButton->Get());

        // Save Button
        $saveButton =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $saveButton->AddEvent(ON_CLICK, 'javascript:submitMetaForm();');
        $tpl->SetVariable('saveButton', $saveButton->Get());

        // Site description
        $tpl->SetBlock('settings/item');
        $sitedesc =& Piwi::CreateWidget('TextArea',
            'site_description',
            Jaws_XSS::defilter($this->gadget->registry->fetch('site_description')));
        $sitedesc->SetRows(5);
        $sitedesc->setID('site_description');
        $tpl->SetVariable('field-name', 'site_description');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_DESCRIPTION'));
        $tpl->SetVariable('field', $sitedesc->Get());
        $tpl->ParseBlock('settings/item');

        // Site keywords
        $tpl->SetBlock('settings/item');
        $sitekeys =& Piwi::CreateWidget('Entry', 'site_keywords',
            $this->gadget->registry->fetch('site_keywords'));
        $sitekeys->setID('site_keywords');
        $sitekeys->setStyle('direction:ltr;');
        $tpl->SetVariable('field-name', 'site_keywords');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_KEYWORDS'));
        $tpl->SetVariable('field', $sitekeys->Get());
        $tpl->ParseBlock('settings/item');

        // Site author
        $tpl->SetBlock('settings/item');
        $author =& Piwi::CreateWidget('Entry', 'site_author', $this->gadget->registry->fetch('site_author'));
        $author->setID('site_author');
        $tpl->SetVariable('field-name', 'site_author');
        $tpl->SetVariable('label',_t('SETTINGS_SITE_AUTHOR'));
        $tpl->SetVariable('field',$author->Get());
        $tpl->ParseBlock('settings/item');

        // License
        $tpl->SetBlock('settings/item');
        $license =& Piwi::CreateWidget('Entry', 'site_license', $this->gadget->registry->fetch('site_license'));
        $license->setID('site_license');
        $tpl->SetVariable('field-name', 'site_license');
        $tpl->SetVariable('label', _t('SETTINGS_SITE_LICENSE'));
        $tpl->SetVariable('field', $license->Get());
        $tpl->ParseBlock('settings/item');

        // Copyright
        $tpl->SetBlock('settings/item');
        $copyright =& Piwi::CreateWidget('Entry', 'site_copyright', $this->gadget->registry->fetch('site_copyright'));
        $copyright->setID('site_copyright');
        $tpl->SetVariable('field-name', 'site_copyright');
        $tpl->SetVariable('label', _t('SETTINGS_COPYRIGHT'));
        $tpl->SetVariable('field', $copyright->Get());
        $tpl->ParseBlock('settings/item');

        // Custom META
        $Metas = @unserialize($this->gadget->registry->fetch('site_custom_meta'));
        if (!empty($Metas)) {
            foreach ($Metas as $meta) {
                $tpl->SetBlock('settings/custom');
                $tpl->SetVariable('label', _t('SETTINGS_META_CUSTOM'));
                // name
                $nMeta =& Piwi::CreateWidget('Entry', 'meta_name', $meta[0]);
                $nMeta->setClass('meta-name');
                $tpl->SetVariable('name', $nMeta->Get());
                // value
                $vMeta =& Piwi::CreateWidget('Entry', 'meta_value', $meta[1]);
                $vMeta->setClass('meta-value');
                $tpl->SetVariable('value', $vMeta->Get());
                $tpl->ParseBlock('settings/custom');
            }
        }

        $tpl->ParseBlock('settings');
        return $tpl->Get();
    }

}