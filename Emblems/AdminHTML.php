<?php
/**
 * Emblems Gadget
 *
 * @category   GadgetAdmin
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class EmblemsAdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Get emblems
     *
     * @access  public
     * @param   int     $limit  Limit of data
     * @return  array   Emblems Data
     */
    function GetEmblems($limit = 0)
    {
        $model    = $GLOBALS['app']->LoadGadget('Emblems', 'AdminModel');
        $rsemblem = $model->GetEmblems(false, $limit);
        $entries_grid = array();
        if (Jaws_Error::IsError($rsemblem)) {
            return $entries_grid;
        }

        foreach ($rsemblem as $e) {
            $item = array();

            $titleentry =& Piwi::CreateWidget(
                                              'Entry', 'title'.$e['id'], $e['title']);
            $titleentry->SetStyle('width: 148px;');
            $item['title'] = $titleentry->Get();

            if (!empty($e['url']) && strpos('&amp;', $e['url']) === false) {
                $e['url'] = htmlentities($e['url'], ENT_QUOTES, 'UTF-8');
            } else {
                $e['url'] = $e['url'];
            }
            $urlentry =& Piwi::CreateWidget('Entry', 'url'.$e['id'], $e['url']);
            $urlentry->SetStyle('direction: ltr; width: 148px;');

            $item['url'] = $urlentry->Get();

            $typecombo =& Piwi::CreateWidget('Combo', 'type' . $e['id']);
            $typecombo->SetTitle(_t('EMBLEMS_TYPE'));
            $typecombo->AddOption(_t('EMBLEMS_LICENSED_UNDER'), 'L');
            $typecombo->AddOption(_t('EMBLEMS_POWERED_BY'), 'P');
            $typecombo->AddOption(_t('EMBLEMS_SUPPORTS'), 'S');
            $typecombo->AddOption(_t('EMBLEMS_BEST_VIEW'), 'B');
            $typecombo->AddOption(_t('EMBLEMS_IS_VALID'), 'V');
            $typecombo->SetDefault($e['emblem_type']);

            $item['type'] = $typecombo->Get();

            $item['src'] = '<img src="' . $GLOBALS['app']->getDataURL('emblems/' . $e['src']).
                           '" alt="'. $e['title'] . '" width="80" height="15" />';
            $hiddensrc =& Piwi::CreateWidget('HiddenEntry', 'src'.$e['id'], $e['src']);
            $item['src'] .= $hiddensrc->Get();

            $statuscombo =& Piwi::CreateWidget('Combo', 'status' . $e['id']);
            $statuscombo->SetTitle(_t('EMBLEMS_STATUS'));
            $statuscombo->AddOption(_t('EMBLEMS_ACTIVE'), '1');
            $statuscombo->AddOption(_t('EMBLEMS_INACTIVE'), '0');
            $statuscombo->SetDefault($e['enabled'] === true ? '1' : '0');
            $item['status'] = $statuscombo->Get();

            $actions = '';
            $link =& Piwi::CreateWidget(
                                        'Link', $e['title'],
                                        $e['url'],
                                        STOCK_HOME);
            $actions.= $link->Get().'&nbsp;';

            if ($this->gadget->GetPermission('EditEmblem')) {
                $link =& Piwi::CreateWidget(
                                            'Link', _t('GLOBAL_SAVE'),
                                            "javascript: editEmblem('".$e['id']."');",
                                            STOCK_SAVE);
                $actions.= $link->Get().'&nbsp;';
            }
            if ($this->gadget->GetPermission('DeleteEmblem')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                            "javascript: deleteEmblem('".$e['id']."', '" . _t('EMBLEMS_CONFIRM_DELETE') . "');",
                                            STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $item['actions'] = $actions;
            $entries_grid[] = $item;
        }

        return $entries_grid;
    }

    /**
     * Build the datagrid
     *
     * @access  public
     * @return  string  XHTML template Datagrid
     */
    function Datagrid()
    {
        $model    = $GLOBALS['app']->LoadGadget('Emblems', 'AdminModel');
        $total    = $model->TotalOfData('emblem');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->SetStyle('width: 980px;');
        $datagrid->SetID('emblems_datagrid');
        $datagrid->TotalRows($total);

        $titlecol =& Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'));
        $titlecol->SetStyle('vertical-align: middle; text-align: center;');
        $datagrid->AddColumn($titlecol);

        $urlcol =& Piwi::CreateWidget('Column', _t('GLOBAL_URL'));
        $urlcol->SetStyle('vertical-align: middle; text-align: center;');
        $datagrid->AddColumn($urlcol);

        $typecol =& Piwi::CreateWidget('Column', _t('EMBLEMS_TYPE'));
        $typecol->SetStyle('vertical-align: middle; text-align: center;');
        $datagrid->AddColumn($typecol);

        $srccol =& Piwi::CreateWidget('Column', _t('EMBLEMS_SRC'));
        $srccol->SetStyle('vertical-align: middle; text-align: center;');
        $datagrid->AddColumn($srccol);

        $statuscol =& Piwi::CreateWidget('Column', _t('EMBLEMS_STATUS'));
        $statuscol->SetStyle('vertical-align: middle; text-align: center;');
        $datagrid->AddColumn($statuscol);

        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));

        return $datagrid->Get();
    }

    /**
     * Admin gadget display
     *
     * @access  public
     * @return  string   XHTML template
     */
    function Admin()
    {
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Emblems/templates/');
        $tpl->Load('AdminEmblems.html');
        $tpl->SetBlock('emblems');

        if ($this->gadget->GetPermission('UpdateProperties')) {
            $tpl->SetBlock('emblems/properties');
            $propsform =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
            $propsform->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Emblems'));
            $propsform->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'UpdateProperties'));

            include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
            $propsfieldset = new Jaws_Widgets_FieldSet(_t('EMBLEMS_SETTINGS'));
            $propsfieldset->SetDirection('vertical');

            $rowscombo =& Piwi::CreateWidget('Combo', 'rows_combo');
            $rowscombo->SetTitle(_t('EMBLEMS_ROWS_LIMIT'));
            for ($i = 1; $i <= 20; $i++) {
                $rowscombo->AddOption($i, $i);
            }
            $rowscombo->SetDefault($this->gadget->GetRegistry('rows'));
            $propsfieldset->Add($rowscombo);
            $urlradio =& Piwi::CreateWidget('RadioButtons', 'allow_url');
            $urlradio->SetTitle(_t('EMBLEMS_ALLOW_URL'));
            $urlradio->AddOption(_t('GLOBAL_YES'), 'true');
            $urlradio->AddOption(_t('GLOBAL_NO'), 'false');
            if ($this->gadget->GetRegistry('allow_url') == 'true') {
                $urlradio->SetDefault('true');
            } else {
                $urlradio->SetDefault('false');
            }
            $propsfieldset->Add($urlradio);
            $propssubmit = Piwi::CreateWidget('Button', 'submitprops',
                                              _t('GLOBAL_UPDATE', _t('GLOBAL_PROPERTIES')), STOCK_SAVE);
            $propssubmit->AddEvent(ON_CLICK, 'javascript: updateProperties(this.form);');

            $propsform->Add($propsfieldset);
            $propsform->Add($propssubmit);

            $tpl->SetVariable('props', $propsform->Get());
            $tpl->ParseBlock('emblems/properties');
        }

        if ($this->gadget->GetPermission('AddEmblem')) {
            $tpl->SetBlock('emblems/addemblem');
            $addform =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post', 'multipart/form-data');
            $addform->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Emblems'));
            $addform->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'AddEmblem'));


            include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
            $addfieldset = new Jaws_Widgets_FieldSet(_t('EMBLEMS_ADD_EMBLEM'));
            $addfieldset->SetDirection('vertical');

            $title =& Piwi::CreateWidget('Entry', 'title', '');
            $title->SetTitle(_t('GLOBAL_TITLE'));
            $addfieldset->Add($title);

            $url =& Piwi::CreateWidget('Entry', 'url', 'http://');
            $url->setStyle('direction: ltr; width: 250px;');
            $url->SetTitle(_t('GLOBAL_URL'));
            $addfieldset->Add($url);

            $src =& Piwi::CreateWidget('FileEntry', 'src', '');
            $src->SetTitle(_t('GLOBAL_FILE'));
            $addfieldset->Add($src);
            $addsubmit =& Piwi::CreateWidget('Button', 'submitadd', _t('EMBLEMS_ADD_EMBLEM'), STOCK_NEW);
            $addsubmit->SetSubmit();

            $addform->Add($addfieldset);
            $addform->Add($addsubmit);
            $propsform->Add($addform);

            $tpl->SetVariable('add', $addform->Get());
            $tpl->ParseBlock('emblems/addemblem');
        }
        $tpl->SetBlock('emblems/emblemlist');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('grid', $this->Datagrid());
        $tpl->ParseBlock('emblems/emblemlist');
        $tpl->ParseBlock('emblems');
        return $tpl->Get();
    }

    /**
     * Edit emblem info
     *
     * @access  public
     * @return  void
     */
    function EditEmblem()
    {
        $request =& Jaws_Request::getInstance();
        $id      = (int)$request->get('id', 'get');
        $post    = $request->get(array('title', 'url', 'status', 'type'), 'post');

        $title  = $post['title' . $id];
        $url    = $post['url' . $id];
        $status = $post['status' . $id];
        $type   = $post['type' . $id];
        $model  = $GLOBALS['app']->LoadGadget('Emblems', 'AdminModel');
        $model->UpdateEmblem($id, $title, $url, $type, $status);
        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Emblems&action=Admin');
    }

    /**
     * Adds a new emblem
     *
     * @access  public
     * @see    EmblemsModel->AddEmblem()
     */
    function AddEmblem()
    {
        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('title', 'url'), 'post');

        $res = Jaws_Utils::UploadFiles($_FILES, JAWS_DATA . 'emblems/', 'jpg,gif,swf,png,jpeg,bmp,svg');
        if (!Jaws_Error::IsError($res)) {
            $filename = $res['src'][0]['host_filename'];
            $model = $GLOBALS['app']->LoadGadget('Emblems', 'AdminModel');
            $model->AddEmblem($post['title'], $post['url'], $filename);
        } else {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Emblems&action=Admin');
    }

}