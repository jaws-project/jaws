<?php
/**
 * Webcam Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Webcam
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Webcam_Actions_Admin_Webcam extends Jaws_Gadget_Action
{
    /**
     * Callback to display short URLs
     *
     * @access  private
     * @param   string  $url    Original URL
     * @return  string  Short URL
     */
    function ShowShortURL($url)
    {
        if (strlen($url) > 40) {
            return "<a title=\"{$url}\" href=\"{$url}\">" . substr($url, 0, 40) . "...</a>";
        }

        return "<a title=\"{$url}\" href=\"{$url}\">".$url."</a>";
    }

    /**
     * Prepares data for datagrid
     *
     * @access  public
     * @param   int     $limit  Data limit
     * @return  array   Data
     */
    function GetWebCams($limit = 0)
    {
        $model = $this->gadget->model->load('Webcam');
        $webcams = $model->GetWebCams($limit);
        if (Jaws_Error::IsError($webcams)) {
            return array();
        }

        $newData = array();
        foreach ($webcams as $webcam) {
            $webcamData = array();
            $webcamData['title'] = $webcam['title'];
            $webcamData['url']   = $this->ShowShortURL($webcam['url']);
            $actions = '';
            if ($this->gadget->GetPermission('EditWebcam')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                    "javascript:editWebcam('".$webcam['id']."');",
                    STOCK_EDIT);
                $actions.= $link->Get().'&nbsp;';
            }
            if ($this->gadget->GetPermission('DeleteWebcam')) {
                $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                    "javascript:if (confirm('"._t('WEBCAM_CONFIRM_DELETE_WEBCAM')."')) ".
                    "deleteWebcam('".$webcam['id']."');",
                    STOCK_DELETE);
                $actions.= $link->Get().'&nbsp;';
            }
            $webcamData['actions'] = $actions;
            $newData[] = $webcamData;
        }
        return $newData;
    }

    /**
     * Builds the datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function DataGrid()
    {
        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('webcam');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->SetID('webcam_datagrid');
        $datagrid->SetStyle('width: 100%;');
        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_TITLE')));
        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_URL')));
        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));

        return $datagrid->Get();
    }

    /**
     * Builds the admin section
     *
     * @access  public
     * @return  string  XHTML content of Admin
     */
    function ManageWebcams()
    {
        $this->AjaxMe('script.js');

        $tpl = $this->gadget->template->loadAdmin('Webcam.html');
        $tpl->SetBlock('webcam');

        $tpl->SetVariable('grid', $this->DataGrid());

        if ($this->gadget->GetPermission('AddWebcam')) {
            $cam_form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post', '', 'webcam_form');
            $cam_form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Webcam'));
            $cam_form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'AddWebcam'));
            $cam_form->Add(Piwi::CreateWidget('HiddenEntry', 'id', ''));

            $fieldset_webcam = new Jaws_Widgets_FieldSet($this->gadget->title);

            $titleentry =& Piwi::CreateWidget('Entry', 'title', '');
            $titleentry->SetTitle(_t('GLOBAL_TITLE'));
            $fieldset_webcam->Add($titleentry);

            $urlentry =& Piwi::CreateWidget('Entry', 'url', 'http://');
            $urlentry->SetTitle(_t('GLOBAL_URL'));
            $fieldset_webcam->Add($urlentry);

            $refresh =& Piwi::CreateWidget('SpinButton', 'refresh', 60, '', 5);
            $refresh->SetTitle(_t('WEBCAM_REFRESH_TIME'));
            $refresh->SetDefault(10);
            $fieldset_webcam->Add($refresh);

            $buttonbox =& Piwi::CreateWidget('HBox');
            $buttonbox->SetStyle('float: right;'); //hig style
            $submit =& Piwi::CreateWidget('Button', 'addnewwebcam',
                _t('GLOBAL_SAVE', $this->gadget->title), STOCK_SAVE);
            $submit->AddEvent(ON_CLICK, 'javascript:submitForm(this.form);');

            $cancel =& Piwi::CreateWidget('Button', 'cancelform', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
            $cancel->AddEvent(ON_CLICK, 'javascript:cleanForm(this.form);');

            $buttonbox->Add($cancel);
            $buttonbox->Add($submit);

            $cam_form->Add($fieldset_webcam);
            $cam_form->Add($buttonbox);

            $tpl->SetVariable('webcam_form', $cam_form->Get());
        }

        if ($this->gadget->GetPermission('UpdateProperties')) {
            $config_form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'POST');
            $config_form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Webcam'));
            $config_form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'UpdateProperties'));

            $fieldset_config = new Jaws_Widgets_FieldSet(_t('GLOBAL_PROPERTIES'));
            $fieldset_config->SetDirection('vertical');
            $fieldset_config->SetStyle('width: 200px;');


            $limitcombo =& Piwi::CreateWidget('Combo', 'limit_random');
            $limitcombo->SetTitle(_t('WEBCAM_RANDOM_LIMIT'));
            for ($i = 1; $i <= 10; $i++) {
                $limitcombo->AddOption($i, $i);
            }

            $limit = $this->gadget->registry->fetch('limit_random');
            if (!$limit || Jaws_Error::IsError($limit)) {
                $limit = 10;
            }

            $limitcombo->SetDefault($limit);

            $fieldset_config->Add($limitcombo);

            $config_form->Add($fieldset_config);
            $submit_config =& Piwi::CreateWidget('Button', 'saveproperties',
                _t('GLOBAL_UPDATE', _t('GLOBAL_PROPERTIES')), STOCK_SAVE);
            $submit_config->SetStyle('float: right;');
            $submit_config->AddEvent(ON_CLICK, 'javascript:updateProperties(this.form);');

            $config_form->Add($submit_config);
            $tpl->SetVariable('config_form', $config_form->Get());
        }

        $tpl->SetVariable('incompleteWebcamFields', _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'));
        $tpl->ParseBlock('webcam');

        return $tpl->Get();
    }
}