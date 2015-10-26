<?php
/**
 * StaticPage Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Actions_Admin_Settings extends StaticPage_Actions_Admin_Default
{
    /**
     * Builds the management UI for gadget properties
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Properties()
    {
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('StaticPage.html');
        $tpl->SetBlock('Properties');

        $action  = jaws()->request->fetch('action', 'get');
        $tpl->SetVariable('menubar', $this->MenuBar($action));

        $model = $this->gadget->model->load('Page');

        //Build the form
        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'POST');
        $form->SetId('frm_settings');

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('GLOBAL_PROPERTIES'));

        //Default page (combo)
        $defaultPage =& Piwi::CreateWidget('Combo', 'default_page');
        $defaultPage->setTitle(_t('STATICPAGE_DEFAULT_PAGE'));
        $pages = $model->GetPages();
        if (Jaws_Error::isError($pages)) {
            $pages = array();
        }
        foreach($pages as $page) {
            $defaultPage->addOption($page['title'], $page['base_id']);
        }
        $defaultPage->setDefault($this->gadget->registry->fetch('default_page'));
        $fieldset->add($defaultPage);

        // Use multilanguage pages?
        $multiLanguage =& Piwi::CreateWidget('Combo', 'multilanguage');
        $multiLanguage->setTitle(_t('STATICPAGE_USE_MULTILANGUAGE'));
        $multiLanguage->addOption(_t('GLOBAL_YES'), 'yes');
        $multiLanguage->addOption(_t('GLOBAL_NO'), 'no');
        $multiLanguage->setDefault($this->gadget->registry->fetch('multilanguage'));
        $fieldset->add($multiLanguage);

        // Save Button
        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript:updateSettings(this.form);');

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetClass('actions');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($save);

        $form->Add($fieldset);
        $form->Add($buttonbox);

        $tpl->SetVariable('form', $form->Get());
        $tpl->ParseBlock('Properties');

        return $tpl->Get();
    }
}