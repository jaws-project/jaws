<?php
/**
 * Tags Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Tags
 */
class Tags_Actions_Admin_Tags extends Tags_Actions_Admin_Default
{
    /**
     * Show tags list
     *
     * @access  public
     * @param   string $gadget     Gadget name
     * @param   string $url        Gadget manage tags URL
     * @return  string XHTML template content
     */
    function Tags($gadget='', $url='')
    {
        $this->AjaxMe('script.js');
        $this->gadget->export('incompleteTagFields',   Jaws::t('ERROR_INCOMPLETE_FIELDS'));
        $this->gadget->export('confirmTagDelete',      $this::t('CONFIRM_DELETE'));
        $this->gadget->export('selectMoreThanOneTags', $this::t('SELECT_MORE_THAN_ONE_TAG_FOR_MERGE'));
        $this->gadget->export('addTagTitle',           $this::t('ADD_TAG'));
        $this->gadget->export('editTagTitle',          $this::t('EDIT_TAG'));

        $tpl = $this->gadget->template->loadAdmin('Tags.html');
        $tpl->SetBlock('tags');

        //Menu bar
        if (!empty($url)) {
            $tpl->SetVariable('menubar', $url);
        } else {
            $tpl->SetVariable('menubar', $this->MenuBar('Tags'));
        }

        //load other gadget translations
        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');

        if (empty($gadget)) {
            $tpl->SetBlock('tags/gadgets_filter');
            //Gadgets filter
            $model = $this->gadget->model->load('Tags');
            $gadgets = $model->GetTagableGadgets();

            $gadgetsCombo =& Piwi::CreateWidget('Combo', 'gadgets_filter');
            $gadgetsCombo->SetID('gadgets_filter');
            $gadgetsCombo->setStyle('width: 150px;');
            $gadgetsCombo->AddEvent(ON_CHANGE, "searchTags()");
            $gadgetsCombo->AddOption(Jaws::t('ALL'), '');
            foreach($gadgets as $gadget => $title) {
                $gadgetsCombo->AddOption($title, $gadget);
            }
            $gadgetsCombo->SetDefault('');
            $tpl->SetVariable('lbl_gadgets_filter', $this::t('GADGET'));
            $tpl->SetVariable('gadgets_filter', $gadgetsCombo->Get());
            $tpl->ParseBlock('tags/gadgets_filter');
        } else {
            $gadgets_filter =& Piwi::CreateWidget('HiddenEntry', 'gadgets_filter', $gadget);
            $gadgets_filter->SetID('gadgets_filter');
            $tpl->SetVariable('gadgets_filter', $gadgets_filter->Get());
        }

        // filter
        $filterData = $this->gadget->request->fetch('filter', 'get');
        $filterEntry =& Piwi::CreateWidget('Entry', 'filter', is_null($filterData)? '' : $filterData);
        $filterEntry->setSize(20);
        $tpl->SetVariable('filter', $filterEntry->Get());
        $filterButton =& Piwi::CreateWidget('Button', 'filter_button',
            Jaws::t('SEARCH'), STOCK_SEARCH);
        $filterButton->AddEvent(ON_CLICK, 'javascript:searchTags();');

        $tpl->SetVariable('filter_button', $filterButton->Get());

        //DataGrid
        $tpl->SetVariable('grid', $this->GetDataGrid());

        //TagUI
        $tpl->SetVariable('tag_ui', $this->TagUI());

        // Actions
        $actions =& Piwi::CreateWidget('Combo', 'tags_actions_combo');
        $actions->SetID('tags_actions_combo');
        $actions->SetTitle(Jaws::t('ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        $actions->AddOption(Jaws::t('DELETE'), 'delete');
        $actions->AddOption($this::t('MERGE'), 'merge');
        $tpl->SetVariable('actions_combo', $actions->Get());

        $btnExecute =& Piwi::CreateWidget('Button', 'executeTagAction', '', STOCK_YES);
        $btnExecute->AddEvent(ON_CLICK, "javascript:tagsDGAction($('#tags_actions_combo'));");
        $tpl->SetVariable('btn_execute', $btnExecute->Get());

        if ($this->gadget->GetPermission('ManageTags')) {
            $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
            $btnCancel->AddEvent(ON_CLICK, 'stopTagAction();');
            $btnCancel->SetStyle('display: none;');
            $tpl->SetVariable('btn_cancel', $btnCancel->Get());

            $btnSave =& Piwi::CreateWidget('Button', 'btn_save', Jaws::t('SAVE'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, "updateTag();");
            $tpl->SetVariable('btn_save', $btnSave->Get());
        }

        $tpl->ParseBlock('tags');
        return $tpl->Get();
    }

    /**
     * Show a form to show/edit a tag
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function TagUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Tags.html');
        $tpl->SetBlock('tagUI');

        // name
        $nameEntry =& Piwi::CreateWidget('Entry', 'name', '');
        $tpl->SetVariable('lbl_name', Jaws::t('NAME'));
        $tpl->SetVariable('name', $nameEntry->Get());

        // title
        $titleEntry =& Piwi::CreateWidget('Entry', 'title', '');
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('title', $titleEntry->Get());

        // description
        $entry =& Piwi::CreateWidget('TextArea', 'description', '');
        $entry->SetId('description');
        $entry->SetRows(4);
        $entry->SetColumns(30);
        $entry->SetStyle('width: 99%; direction: ltr; white-space: nowrap;');
        $tpl->SetVariable('lbl_description', Jaws::t('DESCRIPTION'));
        $tpl->SetVariable('description', $entry->Get());


        // meta_keywords
        $entry =& Piwi::CreateWidget('Entry', 'meta_keywords', '');
        $tpl->SetVariable('lbl_meta_keywords', Jaws::t('META_KEYWORDS'));
        $tpl->SetVariable('meta_keywords', $entry->Get());

        // meta_description
        $entry =& Piwi::CreateWidget('Entry', 'meta_description', '');
        $tpl->SetVariable('lbl_meta_description', Jaws::t('META_DESCRIPTION'));
        $tpl->SetVariable('meta_description', $entry->Get());

        $tpl->ParseBlock('tagUI');
        return $tpl->Get();
    }

    /**
     * Build a new array with filtered data
     *
     * @access  public
     * @param   string  $editAction Edit action
     * @param   array   $filters    Search terms
     * @param   mixed   $offset     Data offset (numeric/boolean)
     * @return  array   Filtered Comments
     */
    function GetDataAsArray($editAction, $filters, $offset)
    {
        $cModel = $this->gadget->model->loadAdmin('Tags');
        $tags = $cModel->GetTags($filters, 15, $offset);
        if (Jaws_Error::IsError($tags)) {
            return array();
        }

        $data = array();
        foreach ($tags as $row) {
            $newRow = array();
            $newRow['__KEY__']      = $row['id'];

            $newRow['name']         = $row['name'];
            $newRow['title']         = $row['title'];
            $newRow['usage_count']  = $row['usage_count'];

            if (!empty($editAction)) {
                $edit_url = str_replace('{id}', $row['id'], $editAction);
            }

            $link =& Piwi::CreateWidget('Link', Jaws::t('EDIT'), $edit_url, STOCK_EDIT);
            $actions= $link->Get().'&nbsp;';

            $link =& Piwi::CreateWidget('Link', Jaws::t('DELETE'),
                "javascript:deleteTag('".$row['id']."');",
                STOCK_DELETE);
            $actions.= $link->Get().'&nbsp;';
            $newRow['actions'] = $actions;

            $data[] = $newRow;
        }
        return $data;
    }

    /**
     * Builds and returns the GetDataGrid UI
     *
     * @access  public
     * @return  string  UI XHTML
     */
    function GetDataGrid()
    {
        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->SetID('tags_datagrid');
        $grid->SetStyle('width: 100%;');
        $grid->useMultipleSelection();
        $grid->pageBy(15);
        $grid->AddColumn(Piwi::CreateWidget('Column', $this::t('TAG_NAME')));
        $grid->AddColumn(Piwi::CreateWidget('Column', $this::t('TAG_TITLE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', $this::t('TAG_USAGE_COUNT')));
        $grid->AddColumn(Piwi::CreateWidget('Column', Jaws::t('ACTIONS')));
        return $grid->Get();
    }

}