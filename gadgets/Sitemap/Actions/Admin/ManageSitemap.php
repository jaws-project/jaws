<?php
/**
 * Sitemap Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Actions_Admin_ManageSitemap extends Jaws_Gadget_Action
{
    /**
     * Prepares the menubar
     *
     * @access  public
     * @return  string  XHTML menubar
     */
    function Menubar()
    {
        if ($this->gadget->GetPermission('PingSite')) {
            $menubar = new Jaws_Widgets_Menubar();
            $menubar->AddOption('PingSite', _t('SITEMAP_PING_SITEMAP'),
                                'javascript: pingSitemap();',
                                STOCK_RESET);
            return $menubar->Get();
        } else {
            return '';
        }
    }
 
    /**
     * Displays gadget administration section
     *
     * @access  public
     * @return  string HTML template content
     */
    function ManageSitemap()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Sitemap.html');
        $tpl->SetBlock('sitemap');
        
        $tpl->SetVariable('empty_message', _t('SITEMAP_EMPTY'));
        $tpl->SetVariable('new_message',   _t('GLOBAL_NEW'));
        $tpl->SetVariable('self_parent_error', _t('SITEMAP_ERROR_SAME_PARENT'));
        $tpl->SetVariable('shortname_error',   _t('SITEMAP_ERROR_SHORTNAME_ERROR'));
        $tpl->SetVariable('menubar', $this->Menubar(''));
        
        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');

        $parent_id =& Piwi::CreateWidget('HiddenEntry', 'id', '');
        $parent_id->SetId('ssid');
        $form->Add($parent_id);

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('GLOBAL_EDIT'));
        $fieldset->SetDirection('vertical');
        $fieldset->SetID('ssfieldset');

        $title =& Piwi::CreateWidget('Entry', 'title', '', _t('GLOBAL_TITLE'));
        $title->SetId('sstitle');
        $title->SetStyle('width: 330px;');
        $fieldset->Add($title);

        $shortname =& Piwi::CreateWidget('Entry', 'shortname', '', _t('SITEMAP_SHORTNAME'));
        $shortname->SetId('ssshortname');
        $shortname->SetStyle('width: 330px;');
        $fieldset->Add($shortname);

        $type =& Piwi::CreateWidget('Combo', 'type');
        $type->SetId('sstype');
        $type->SetTitle(_t('SITEMAP_TYPE'));
        $type->AddOption(_t('GLOBAL_URL'), 'url');
        if (Jaws_Gadget::IsGadgetInstalled('StaticPage')) {
            $type->AddOption(_t('SITEMAP_STATICPAGE'), 'StaticPage');
        }
        if (Jaws_Gadget::IsGadgetInstalled('Blog')) {
            $type->AddOption(_t('SITEMAP_BLOG'), 'Blog');
        }
        if (Jaws_Gadget::IsGadgetInstalled('Launcher')) {
            $type->AddOption(_t('SITEMAP_LAUNCHER'), 'Launcher');
        }
        $type->SetStyle('width: 330px;');
        $type->AddEvent(ON_CHANGE, 'createReference(this.value);');
        $fieldset->Add($type);

        $ref =& Piwi::CreateWidget('Combo', 'reference');
        $ref->SetTitle(_t('SITEMAP_REFERENCE'));
        $ref->SetId('ssreference');
        $ref->SetStyle('width: 330px;');
        $fieldset->Add($ref);

        $parent =& Piwi::CreateWidget('Combo', 'parent');
        $parent->SetTitle(_t('SITEMAP_PARENT'));
        $parent->AddOption(_t('SITEMAP_TOP'), 0);
        $parent->SetId('ssparent');
        $parent->SetStyle('width: 330px;');
        $fieldset->Add($parent);

        $changeFreq =& Piwi::CreateWidget('Combo', 'changefreq');
        $changeFreq->SetTitle(_t('SITEMAP_CHANGE_FREQ'));
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_NONE'), 'none');
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_ALWAYS'), 'always');
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_HOURLY'), 'hourly');
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_DAILY'), 'daily');
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_WEEKLY'), 'weekly');
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_MONTHLY'), 'monthly');
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_YEARLY'), 'yearly');
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_NEVER'), 'never');
        $changeFreq->SetDefault('none');
        $changeFreq->SetId('sschangefreq');
        $changeFreq->SetStyle('width: 330px;');
        $fieldset->Add($changeFreq);

        $priority =& Piwi::CreateWidget('Combo', 'priority');
        $priority->SetTitle(_t('SITEMAP_PRIORITY'));
        for($i=1; $i<10; $i++) {
            $priority->AddOption('0.'.$i, '0.'.$i);
        }
        $priority->AddOption('1.0', '1.0');
        $priority->SetDefault('0.5');
        $priority->SetId('sspriority');
        $priority->SetStyle('width: 330px;');
        $fieldset->Add($priority);

        $form->Add($fieldset);

        $hbox =& Piwi::CreateWidget('HBox');
        $hbox->SetStyle('float: right;'); //hig style
        $delete =& Piwi::CreateWidget('Button', 'delete', _t('GLOBAL_DELETE'), STOCK_DELETE);
        $delete->AddEvent(ON_CLICK, 'if (confirm(\'' . _t('GLOBAL_CONFIRM_DELETE') . '\')) { deleteCurrent(); }');
        $delete->SetId('delete_button');
        $hbox->Add($delete);
        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'saveCurrent();');
        $save->SetId('save_button');
        $hbox->Add($save);
        $form->Add($hbox);

        $tpl->SetVariable('form', $form->Get());

        // Buttons
        $btns =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');

        $hbox2 =& Piwi::CreateWidget('HBox');

        $new =& Piwi::CreateWidget('Button', 'new', _t('GLOBAL_NEW'), STOCK_NEW);
        $new->AddEvent(ON_CLICK, 'newItem();');
        $new->SetId('new_button');
        $hbox2->Add($new);

        $up =& Piwi::CreateWidget('Button', 'up', '', STOCK_UP);
        $up->AddEvent (ON_CLICK, 'moveItem(\'up\');');
        $up->SetTitle(_t('SITEMAP_MOVEUP'));
        $up->SetId('up_button');
        $hbox2->Add($up);

        $down =& Piwi::CreateWidget('Button', 'down', '', STOCK_DOWN);
        $down->AddEvent (ON_CLICK, 'moveItem(\'down\');');
        $down->SetTitle(_t('SITEMAP_MOVEDOWN'));
        $down->SetId('down_button');
        $hbox2->Add($down);

        $btns->Add($hbox2);

        $tpl->SetVariable('buttons', $btns->Get());

        $tpl->ParseBlock('sitemap');

        return $tpl->Get();
    }
}