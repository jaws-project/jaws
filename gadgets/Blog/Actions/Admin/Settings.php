<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Admin_Settings extends Blog_Actions_Admin_Default
{
    /**
     * Displays blog settings administration panel
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function AdditionalSettings()
    {
        $this->gadget->CheckPermission('Settings');
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Settings.html');
        $tpl->SetBlock('additional');

        // Header
        $tpl->SetVariable('menubar', $this->MenuBar('AdditionalSettings'));

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'POST');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Blog'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'SaveAdditionalSettings'));

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('BLOG_ADDITIONAL_SETTINGS'));
        // $fieldset =& Piwi::CreateWidget('FieldSet', _t('BLOG_ADDITIONAL_SETTINGS'));

        // Save Button
        $save =& Piwi::CreateWidget('Button', 'save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'javascript: saveSettings(this.form);');

        $sModel = $this->gadget->model->loadAdmin('Settings');
        $cModel = $this->gadget->model->load('Categories');
        $settings = $sModel->GetSettings();
        if (Jaws_Error::IsError($settings)) {
            $settings = array();
        }

        // Default View
        $tpl->SetVariable('label', _t('BLOG_DEFAULT_VIEW'));
        $viewCombo =& Piwi::CreateWidget('Combo', 'default_view');
        $viewCombo->setContainerClass('oneline');
        $viewCombo->SetTitle(_t('BLOG_DEFAULT_VIEW'));
        $viewCombo->AddOption(_t('BLOG_MONTHLY'), 'monthly');
        $viewCombo->AddOption(_t('BLOG_LATEST_ENTRY'), 'latest_entry');
        $viewCombo->AddOption(_t('BLOG_LAST_ENTRIES'), 'last_entries');
        $viewCombo->AddOption(_t('BLOG_DEFAULT_CATEGORY'), 'default_category');
        $viewCombo->SetDefault(isset($settings['default_view']) ?
                               $settings['default_view'] : '');

        // Last entries limit
        $limitCombo =& Piwi::CreateWidget('Combo', 'last_entries_limit');
        $limitCombo->setContainerClass('oneline');
        $limitCombo->SetTitle(_t('BLOG_LAST_ENTRIES_LIMIT'));
        for ($i = 5; $i <= 30; $i += 5) {
            $limitCombo->AddOption($i, $i);
        }
        $limitCombo->SetDefault(isset($settings['last_entries_limit']) ?
                                $settings['last_entries_limit'] : '');

        // Popular limit
        $popCombo =& Piwi::CreateWidget('Combo', 'popular_limit');
        $popCombo->setContainerClass('oneline');
        $popCombo->SetTitle(_t('BLOG_POPULAR_ENTRIES_LIMIT'));
        for ($i = 5; $i <= 30; $i += 5) {
            $popCombo->AddOption($i, $i);
        }
        $popCombo->SetDefault(isset($settings['popular_limit']) ?
                                $settings['popular_limit'] : '');

        // Last comments limit
        $commentslimitCombo =& Piwi::CreateWidget('Combo', 'last_comments_limit');
        $commentslimitCombo->setContainerClass('oneline');
        $commentslimitCombo->SetTitle(_t('BLOG_LAST_COMMENTS_LIMIT'));
        for ($i = 5; $i <= 30; $i += 5) {
            $commentslimitCombo->AddOption($i, $i);
        }
        $commentslimitCombo->SetDefault(isset($settings['last_comments_limit']) ?
                                        $settings['last_comments_limit'] : '');

        // Last recent comments
        $recentcommentsLimitCombo =& Piwi::CreateWidget('Combo', 'last_recentcomments_limit');
        $recentcommentsLimitCombo->setContainerClass('oneline');
        $recentcommentsLimitCombo->SetTitle(_t('BLOG_LAST_RECENTCOMMENTS_LIMIT'));
        for ($i = 5; $i <= 30; $i += 5) {
            $recentcommentsLimitCombo->AddOption($i, $i);
        }
        $recentcommentsLimitCombo->SetDefault(isset($settings['last_recentcomments_limit']) ?
                                              $settings['last_recentcomments_limit'] : '');

        $categories = $cModel->GetCategories();
        if (!Jaws_Error::IsError($categories)) {
            // Default category

            $catCombo =& Piwi::CreateWidget('Combo', 'default_category');
            $catCombo->setContainerClass('oneline');
            $catCombo->SetTitle(_t('BLOG_DEFAULT_CATEGORY'));
            foreach ($categories as $cat) {
                $catCombo->AddOption($cat['name'], $cat['id']);
            }
            $catCombo->SetDefault(isset($settings['default_category']) ?
                                  $settings['default_category'] : '');
        }

        // RSS/Atom limit
        $xmlCombo =& Piwi::CreateWidget('Combo', 'xml_limit');
        $xmlCombo->setContainerClass('oneline');
        $xmlCombo->SetTitle(_t('BLOG_RSS_ENTRIES_LIMIT'));
        for ($i = 5; $i <= 50; $i += 5) {
            $xmlCombo->AddOption($i, $i);
        }
        $xmlCombo->SetDefault(isset($settings['xml_limit']) ? $settings['xml_limit'] : '');

        Jaws_Translate::getInstance()->LoadTranslation('Comments', JAWS_COMPONENT_GADGET);
        // Comments
        $commCombo =& Piwi::CreateWidget('Combo', 'comments');
        $commCombo->setContainerClass('oneline');
        $commCombo->SetTitle(_t('BLOG_COMMENTS'));
        $commCombo->AddOption(_t('GLOBAL_ENABLED'), 'true');
        $commCombo->AddOption(_t('GLOBAL_DISABLED'), 'false');
        $commCombo->SetDefault(isset($settings['comments']) ? $settings['comments'] : '');

        // Comment status
        $commStatusCombo =& Piwi::CreateWidget('Combo', 'comment_status');
        $commStatusCombo->setContainerClass('oneline');
        $commStatusCombo->SetTitle(_t('BLOG_DEFAULT_STATUS', _t('BLOG_COMMENTS')));
        $commStatusCombo->AddOption(_t('COMMENTS_STATUS_APPROVED'), 'approved');
        $commStatusCombo->AddOption(_t('COMMENTS_STATUS_WAITING'), 'waiting');
        $commStatusCombo->SetDefault($settings['comment_status']);

        // Trackback
        $tbCombo =& Piwi::CreateWidget('Combo', 'trackback');
        $tbCombo->setContainerClass('oneline');
        $tbCombo->SetTitle(_t('BLOG_TRACKBACK'));
        $tbCombo->AddOption(_t('GLOBAL_ENABLED'), 'true');
        $tbCombo->AddOption(_t('GLOBAL_DISABLED'), 'false');
        $tbCombo->SetDefault($settings['trackback']);

        // Trackback status
        $tbStatusCombo =& Piwi::CreateWidget('Combo', 'trackback_status');
        $tbStatusCombo->setContainerClass('oneline');
        $tbStatusCombo->SetTitle(_t('BLOG_DEFAULT_STATUS', _t('BLOG_TRACKBACK')));
        $tbStatusCombo->AddOption(_t('COMMENTS_STATUS_APPROVED'), 'approved');
        $tbStatusCombo->AddOption(_t('COMMENTS_STATUS_WAITING'), 'waiting');
        $tbStatusCombo->SetDefault($settings['trackback_status']);

        // Pingback
        $pbCombo =& Piwi::CreateWidget('Combo', 'pingback');
        $pbCombo->setContainerClass('oneline');
        $pbCombo->SetTitle(_t('BLOG_PINGBACK'));
        $pbCombo->AddOption(_t('GLOBAL_ENABLED'), 'true');
        $pbCombo->AddOption(_t('GLOBAL_DISABLED'), 'false');
        $pbCombo->SetDefault($settings['pingback']);

        $fieldset->Add($viewCombo);
        $fieldset->Add($limitCombo);
        $fieldset->Add($popCombo);
        $fieldset->Add($commentslimitCombo);
        $fieldset->Add($recentcommentsLimitCombo);
        if (!Jaws_Error::IsError($categories)) {
            $fieldset->Add($catCombo);
        }
        $fieldset->Add($xmlCombo);
        $fieldset->Add($commCombo);
        $fieldset->Add($commStatusCombo);
        $fieldset->Add($tbCombo);
        $fieldset->Add($tbStatusCombo);
        $fieldset->Add($pbCombo);
        $fieldset->SetDirection('vertical');
        $form->Add($fieldset);

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($save);

        $form->Add($buttonbox);

        $tpl->SetVariable('form', $form->Get());

        $tpl->ParseBlock('additional');

        return $tpl->Get();
    }

    /**
     * Applies modifications on blog settings
     *
     * @access  public
     */
    function SaveAdditionalSettings()
    {
        $this->gadget->CheckPermission('Settings');

        $names = array(
            'default_view', 'last_entries_limit', 'last_comments_limit',
            'last_recentcomments_limit', 'default_category', 'xml_limit',
            'comments', 'comment_status', 'trackback', 'trackback_status');
        $post = jaws()->request->fetch($names, 'post');

        $model = $this->gadget->model->loadAdmin('Settings');
        $model->SaveSettings($post['default_view'], $post['last_entries_limit'],
                             $post['last_comments_limit'], $post['last_recentcomments_limit'],
                             $post['default_category'], $post['xml_limit'],
                             $post['comments'], $post['comment_status'],
                             $post['trackback'], $post['trackback_status']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Blog&action=AdditionalSettings');
    }

}