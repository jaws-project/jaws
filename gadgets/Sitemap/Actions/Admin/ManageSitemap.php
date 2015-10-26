<?php
/**
 * Sitemap Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Actions_Admin_ManageSitemap extends Sitemap_Actions_Admin_Default
{
    /**
     * Administration section
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageSitemap()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Sitemap.html');
        $tpl->SetBlock('sitemap');

        $tpl->SetBlock('sitemap/sitemap_base');
        $tpl->SetVariable('menubar', $this->MenuBar('Sitemap'));

        $tpl->SetVariable('gadgets_tree', $this->GetGadgetTreeUI());

        $save_btn =& Piwi::CreateWidget('Button','btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
        $save_btn->SetStyle('display: none;');
        $save_btn->AddEvent(ON_CLICK, 'javascript:saveProperties();');
        $tpl->SetVariable('save', $save_btn->Get());

        $cancel_btn =& Piwi::CreateWidget('Button','btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel_btn->SetStyle('display: none;');
        $cancel_btn->AddEvent(ON_CLICK, 'javascript:stopAction();');
        $tpl->SetVariable('cancel', $cancel_btn->Get());

        $tpl->SetVariable('sitemap_tree_image', 'gadgets/Sitemap/Resources/images/logo.mini.png');
        $tpl->SetVariable('sitemap_tree_title', _t('SITEMAP_TREE_TITLE'));
        $tpl->SetVariable('editCategoryTitle',  _t('SITEMAP_CATEGORY_EDIT'));
        $tpl->SetVariable('editGadgetTitle',    _t('SITEMAP_GADGET_EDIT'));
        $tpl->SetVariable('sitemapImageSrc',       'gadgets/Sitemap/Resources/images/logo.mini.png');
        $tpl->SetVariable('sitemapListOpenImageSrc',  STOCK_ADD);
        $tpl->SetVariable('sitemapListCloseImageSrc', STOCK_REMOVE);
        $tpl->SetVariable('noCategoryExists',       _t('SITEMAP_CATEGORY_NOEXISTS'));
        $tpl->SetVariable('incompleteFields',   _t('SITEMAP_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('ping_icon', STOCK_RESET);
        $tpl->SetVariable('js_ping_func', "pingSearchEngines()");
        $tpl->SetVariable('ping_title', _t('SITEMAP_PING_SEARCHENGINES'));

        $tpl->ParseBlock('sitemap/sitemap_base');
        $tpl->ParseBlock('sitemap');
        return $tpl->Get();
    }

    /**
     * Providing a list of gadgets that have Sitemap
     *
     * @access  public
     * @return  string XHTML Template content
     */
    function GetGadgetTreeUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Sitemap.html');
        $tpl->SetBlock('sitemap');

        $model = $this->gadget->model->load('Sitemap');
        $gadgets = $model->GetAvailableSitemapGadgets();
        foreach ($gadgets as $gadget) {
            $tpl->SetBlock('sitemap/sitemap_gadget');
            $tpl->SetVariable('lg_id', 'gadget_'.$gadget['name']);
            $tpl->SetVariable('icon', STOCK_ADD);
            $tpl->SetVariable('js_list_func', "listCategories('" . $gadget['name'] . "')");
            $tpl->SetVariable('title', $gadget['title']);
            $tpl->SetVariable('js_edit_func', "editGadget('" . $gadget['name'] . "')");
            $tpl->SetVariable('sync_icon', STOCK_REFRESH);
            $tpl->SetVariable('js_sync_func', "syncSitemap('" . $gadget['name'] . "')");
            $tpl->SetVariable('sync_title', _t('SITEMAP_SYNC_SITEMAP'));
            $tpl->ParseBlock('sitemap/sitemap_gadget');
        }

        $tpl->ParseBlock('sitemap');
        return $tpl->Get();
    }

    /**
     * Get Gadget Categories List
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetCategoriesList()
    {
        @list($gadget) = jaws()->request->fetchAll('post');

        $tpl = $this->gadget->template->loadAdmin('Sitemap.html');
        $tpl->SetBlock('sitemap');

        $objGadget = Jaws_Gadget::getInstance($gadget);
        if (Jaws_Error::IsError($objGadget)) {
            return '';
        }
        $objHook = $objGadget->hook->load('Sitemap');
        if (Jaws_Error::IsError($objHook)) {
            return '';
        }

        $gResult = $objHook->Execute(0);
        if (Jaws_Error::IsError($gResult) || empty($gResult)) {
            return '';
        }

        foreach ($gResult as $category) {
            $tpl->SetBlock('sitemap/category_list');
            $tpl->SetVariable('cid', 'category_'.$category['id']);
            $tpl->SetVariable('icon', 'gadgets/Sitemap/Resources/images/logo.mini.png');
            $tpl->SetVariable('title', $category['title']);
            $tpl->SetVariable('js_edit_func', "editCategory(this, '$gadget', {$category['id']})");
            $tpl->SetVariable('add_icon', STOCK_NEW);
            $tpl->ParseBlock('sitemap/category_list');
        }

        $tpl->ParseBlock('sitemap');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given category properties
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetCategoryUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Sitemap.html');

        $tpl->SetBlock('sitemap');
        $tpl->SetBlock('sitemap/categoryUI');

        // Priority
        $priority =& Piwi::CreateWidget('Combo', 'priority');
        $priority->SetTitle(_t('SITEMAP_PRIORITY'));
        $priority->AddOption(_t('SITEMAP_INHERITANCE_SETTINGS'), '0');
        for($i=1; $i<10; $i++) {
            $priority->AddOption('0.'.$i, '0.'.$i);
        }
        $priority->AddOption('1.0', '1.0');
        $priority->SetDefault(null);
        $priority->SetId('priority');
        $priority->SetStyle('width: 330px;');
        $tpl->SetVariable('lbl_priority', _t('SITEMAP_PRIORITY'));
        $tpl->SetVariable('priority', $priority->Get());

        // Change Frequency
        $changeFreq =& Piwi::CreateWidget('Combo', 'frequency');
        $changeFreq->SetTitle(_t('SITEMAP_CHANGE_FREQ'));
        $changeFreq->AddOption(_t('SITEMAP_INHERITANCE_SETTINGS'), 0);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_ALWAYS'), Sitemap_Info::SITEMAP_CHANGE_FREQ_ALWAYS);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_HOURLY'), Sitemap_Info::SITEMAP_CHANGE_FREQ_HOURLY);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_DAILY'), Sitemap_Info::SITEMAP_CHANGE_FREQ_DAILY);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_WEEKLY'), Sitemap_Info::SITEMAP_CHANGE_FREQ_WEEKLY);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_MONTHLY'), Sitemap_Info::SITEMAP_CHANGE_FREQ_MONTHLY);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_YEARLY'), Sitemap_Info::SITEMAP_CHANGE_FREQ_YEARLY);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_NEVER'), Sitemap_Info::SITEMAP_CHANGE_FREQ_NEVER);
        $changeFreq->SetDefault(0);
        $changeFreq->SetId('frequency');
        $changeFreq->SetStyle('width: 330px;');
        $tpl->SetVariable('lbl_frequency', _t('SITEMAP_CHANGE_FREQ'));
        $tpl->SetVariable('frequency', $changeFreq->Get());

        // Status
        $changeFreq =& Piwi::CreateWidget('Combo', 'status');
        $changeFreq->SetTitle(_t('GLOBAL_STATUS'));
        $changeFreq->AddOption(_t('SITEMAP_INHERITANCE_SETTINGS'), 0);
        $changeFreq->AddOption(_t('SITEMAP_CATEGORY_SHOW_IN_NONE'), Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_NONE);
        $changeFreq->AddOption(_t('SITEMAP_CATEGORY_SHOW_IN_XML'), Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_XML);
        $changeFreq->AddOption(_t('SITEMAP_CATEGORY_SHOW_IN_USER_SIDE'), Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_USER_SIDE);
        $changeFreq->AddOption(_t('SITEMAP_CATEGORY_SHOW_IN_BOTH'), Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_BOTH);
        $changeFreq->SetDefault(0);
        $changeFreq->SetId('status');
        $changeFreq->SetStyle('width: 330px;');
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('status', $changeFreq->Get());

        $tpl->ParseBlock('sitemap/categoryUI');
        $tpl->ParseBlock('sitemap');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given gadget properties
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetGadgetUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Sitemap.html');

        $tpl->SetBlock('sitemap');
        $tpl->SetBlock('sitemap/gadgetUI');

        // Priority
        $priority =& Piwi::CreateWidget('Combo', 'priority');
        $priority->SetTitle(_t('SITEMAP_PRIORITY'));
        $priority->AddOption(_t('SITEMAP_INHERITANCE_SETTINGS'), null);
        for($i=1; $i<10; $i++) {
            $priority->AddOption('0.'.$i, '0.'.$i);
        }
        $priority->AddOption('1.0', '1.0');
        $priority->SetDefault(null);
        $priority->SetId('priority');
        $priority->SetStyle('width: 330px;');
        $tpl->SetVariable('lbl_priority', _t('SITEMAP_PRIORITY'));
        $tpl->SetVariable('priority', $priority->Get());

        // Change Frequency
        $changeFreq =& Piwi::CreateWidget('Combo', 'frequency');
        $changeFreq->SetTitle(_t('SITEMAP_CHANGE_FREQ'));
        $changeFreq->AddOption(_t('SITEMAP_INHERITANCE_SETTINGS'), 0);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_ALWAYS'), Sitemap_Info::SITEMAP_CHANGE_FREQ_ALWAYS);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_HOURLY'), Sitemap_Info::SITEMAP_CHANGE_FREQ_HOURLY);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_DAILY'), Sitemap_Info::SITEMAP_CHANGE_FREQ_DAILY);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_WEEKLY'), Sitemap_Info::SITEMAP_CHANGE_FREQ_WEEKLY);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_MONTHLY'), Sitemap_Info::SITEMAP_CHANGE_FREQ_MONTHLY);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_YEARLY'), Sitemap_Info::SITEMAP_CHANGE_FREQ_YEARLY);
        $changeFreq->AddOption(_t('SITEMAP_CHANGE_FREQ_NEVER'), Sitemap_Info::SITEMAP_CHANGE_FREQ_NEVER);
        $changeFreq->SetDefault(0);
        $changeFreq->SetId('frequency');
        $changeFreq->SetStyle('width: 330px;');
        $tpl->SetVariable('lbl_frequency', _t('SITEMAP_CHANGE_FREQ'));
        $tpl->SetVariable('frequency', $changeFreq->Get());

        // Status
        $changeFreq =& Piwi::CreateWidget('Combo', 'status');
        $changeFreq->SetTitle(_t('GLOBAL_STATUS'));
        $changeFreq->AddOption(_t('SITEMAP_INHERITANCE_SETTINGS'), 0);
        $changeFreq->AddOption(_t('SITEMAP_CATEGORY_SHOW_IN_NONE'), Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_NONE);
        $changeFreq->AddOption(_t('SITEMAP_CATEGORY_SHOW_IN_XML'), Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_XML);
        $changeFreq->AddOption(_t('SITEMAP_CATEGORY_SHOW_IN_USER_SIDE'), Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_USER_SIDE);
        $changeFreq->AddOption(_t('SITEMAP_CATEGORY_SHOW_IN_BOTH'), Sitemap_Info::SITEMAP_CATEGORY_SHOW_IN_BOTH);
        $changeFreq->SetDefault(0);
        $changeFreq->SetId('status');
        $changeFreq->SetStyle('width: 330px;');
        $tpl->SetVariable('lbl_status', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('status', $changeFreq->Get());

        // Last update
        $tpl->SetVariable('lbl_last_update', _t('GLOBAL_UPDATETIME'));
        $tpl->SetVariable('last_update', _t('SITEMAP_NEVER'));


        $tpl->ParseBlock('sitemap/gadgetUI');
        $tpl->ParseBlock('sitemap');
        return $tpl->Get();
    }

    /**
     * Get category properties
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetCategory()
    {
        $post = jaws()->request->fetch(array('gname', 'cid'), 'post');
        $model = $this->gadget->model->loadAdmin('Sitemap');
        $category = $model->GetCategoryProperties($post['gname'], $post['cid']);
        return $category;
    }

    /**
     * Get gadget properties
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GetGadget()
    {
        $gadget = jaws()->request->fetch('gname', 'post');
        $model = $this->gadget->model->loadAdmin('Sitemap');
        $properties = $model->GetGadgetProperties($gadget);
        $date = Jaws_Date::getInstance();
        if(!empty($properties['update_time'])) {
            $properties['update_time'] = $date->format($properties['update_time']);
        } else {
            $properties['update_time'] = _t('SITEMAP_NEVER');
        }
        return $properties;
    }

    /**
     * Update a category properties
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UpdateCategory()
    {
        $post = jaws()->request->fetch(array('gname', 'category', 'data:array'), 'post');
        $model = $this->gadget->model->loadAdmin('Sitemap');
        $res = $model->UpdateCategory($post['gname'], $post['category'], $post['data']);
        if (Jaws_Error::IsError($res) || $res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_CANT_UPDATE_CATEGORY_PROPERTIES'),
                RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_CATEGORY_PROPERTIES_UPDATED'),
                RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update a gadget properties
     *
     * @access  public
     * @return  string  XHTML content
     */
    function UpdateGadgetProperties()
    {
        $post = jaws()->request->fetch(array('gname', 'data:array'), 'post');
        $model = $this->gadget->model->loadAdmin('Sitemap');
        $data = $post['data'];
        $data['update_time'] = '';
        $res = $model->UpdateGadgetProperties($post['gname'], $data);
        if (Jaws_Error::IsError($res) || $res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_CANT_UPDATE_GADGET_PROPERTIES'),
                RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_GADGET_PROPERTIES_UPDATED'),
                RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Sync sitemap XML files
     *
     * @access  public
     * @return  string  XHTML content
     */
    function SyncSitemapXML()
    {
        $gadget = jaws()->request->fetch('gname', 'post');
        $model = $this->gadget->model->loadAdmin('Sitemap');
        $res = $model->SyncSitemapXML($gadget);
        if (Jaws_Error::IsError($res) || $res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_CANT_SYNC_XML_FILE'),
                RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_XML_FILE_SYNCED'),
                RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Sync sitemap data (user side HTML sitemap) files
     *
     * @access  public
     * @return  string  XHTML content
     */
    function SyncSitemapData()
    {
        $gadget = jaws()->request->fetch('gname', 'post');
        $model = $this->gadget->model->loadAdmin('Sitemap');
        $res = $model->SyncSitemapData($gadget);
        if (Jaws_Error::IsError($res) || $res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_CANT_SYNC_DATA_FILE'),
                RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_DATA_FILE_SYNCED'),
                RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}