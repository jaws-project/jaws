<?php
/**
 * Emblems Gadget
 *
 * @category   GadgetAdmin
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_Actions_Admin_Emblems extends Jaws_Gadget_Action
{
    /**
     * Builds emblems administration UI
     *
     * @access  public
     * @return  string   XHTML UI
     */
    function Emblems()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Emblems.html');
        $tpl->SetBlock('emblems');

        $addform =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post', 
            'multipart/form-data', 'frm_emblem');
        $addform->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Emblems'));
        $addform->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'AddEmblem'));

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fs = new Jaws_Widgets_FieldSet(_t('EMBLEMS_ADD_EMBLEM'));
        $fs->SetDirection('vertical');

        $title =& Piwi::CreateWidget('Entry', 'title', '');
        $title->SetTitle(_t('GLOBAL_TITLE'));
        $fs->Add($title);

        $url =& Piwi::CreateWidget('Entry', 'url', 'http://');
        $url->SetTitle(_t('GLOBAL_URL'));
        $fs->Add($url);

        $image =& Piwi::CreateWidget('FileEntry', 'image', '');
        $image->SetTitle(_t('GLOBAL_FILE'));
        $fs->Add($image);

        $type =& Piwi::CreateWidget('Combo', 'type');
        $type->SetTitle(_t('EMBLEMS_TYPE'));
        for ($i = 1; $i <= 15; $i++) {
            $type_str = "EMBLEMS_TYPE_$i";
            if (_t($type_str) != $type_str) {
                $type->AddOption(_t($type_str), $i);
            }
        }
        $fs->Add($type);

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetTitle(_t('GLOBAL_PUBLISHED'));
        $published->AddOption(_t('GLOBAL_YES'), 1);
        $published->AddOption(_t('GLOBAL_NO'), 0);
        $fs->Add($published);

        $addsubmit =& Piwi::CreateWidget('Button', 'submitadd', _t('EMBLEMS_ADD_EMBLEM'), STOCK_NEW);
        $addsubmit->SetSubmit();

        $addform->Add($fs);
        $addform->Add($addsubmit);

        $tpl->SetVariable('form', $addform->Get());

        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $this->gadget->layout->setVariable('confirmDelete', _t('EMBLEMS_CONFIRM_DELETE'));
        $tpl->SetVariable('grid', $this->Datagrid());
        $tpl->ParseBlock('emblems');

        return $tpl->Get();
    }

    /**
     * Builds the datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function Datagrid()
    {
        $model = $this->gadget->model->load();
        $total = $model->TotalOfData('emblem');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->SetID('emblems_datagrid');
        $datagrid->TotalRows($total);

        $typecol =& Piwi::CreateWidget('Column', _t('EMBLEMS_TYPE'));
        $datagrid->AddColumn($typecol);

        $titlecol =& Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'));
        $datagrid->AddColumn($titlecol);

        $urlcol =& Piwi::CreateWidget('Column', _t('GLOBAL_URL'));
        $datagrid->AddColumn($urlcol);

        $imgcol =& Piwi::CreateWidget('Column', _t('EMBLEMS_RESULT'));
        $datagrid->AddColumn($imgcol);

        $statuscol =& Piwi::CreateWidget('Column', _t('GLOBAL_PUBLISHED'));
        $datagrid->AddColumn($statuscol);

        $datagrid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));

        return $datagrid->Get();
    }

    /**
     * Fetches emblems
     *
     * @access  public
     * @param   int     $limit  Data limit
     * @return  array   Array of emblems
     */
    function GetEmblems($limit = 0)
    {
        $model = $this->gadget->model->load('Emblems');
        $rsemblem = $model->GetEmblems(false, $limit);
        $entries_grid = array();
        if (Jaws_Error::IsError($rsemblem)) {
            return $entries_grid;
        }

        $types = array();
        for ($i = 1; $i <= 15; $i++) {
            $type_str = "EMBLEMS_TYPE_$i";
            if (_t($type_str) != $type_str) {
                $types[$i] = _t($type_str);
            }
        }
        $dataURL = $GLOBALS['app']->getDataURL('emblems/');
        foreach ($rsemblem as $e) {
            $item = array();

            $typeCombo =& Piwi::CreateWidget('Combo', 'type');
            $typeCombo->setID('');
            $typeCombo->AddOptions($types);
            $typeCombo->SetStyle('width:100px;');
            $typeCombo->SetDefault($e['type']);
            $item['type'] = $typeCombo->Get();

            $titleEntry =& Piwi::CreateWidget('Entry', 'title', $e['title']);
            $titleEntry->setID('');
            $titleEntry->SetStyle('width:150px;');
            $item['title'] = $titleEntry->Get();

            if (!empty($e['url']) && strpos('&amp;', $e['url']) === false) {
                $e['url'] = htmlentities($e['url'], ENT_QUOTES, 'UTF-8');
            }
            $urlEntry =& Piwi::CreateWidget('Entry', 'url', $e['url']);
            $urlEntry->setID('');
            $urlEntry->SetStyle('direction:ltr; width:150px;');
            $item['url'] = $urlEntry->Get();

            if (empty($e['url'])) {
                $e['url'] = 'javascript:void(0);';
            }
            $link =& Piwi::CreateWidget('Link', $e['title'], $e['url'], $dataURL . $e['image']);
            $item['image'] = $link->Get();

            $published =& Piwi::CreateWidget('CheckButtons', 'published');
            $published->addOption('', '', 'published'.$e['id'], $e['published']);
            $item['status'] = $published->Get();
            
            $actions = '';
            $link =& Piwi::CreateWidget(
                'Link',
                _t('GLOBAL_SAVE'),
                "javascript:updateEmblem({$e['id']}, this);",
                STOCK_SAVE);
            $actions .= $link->Get().'&nbsp;';

            $link =& Piwi::CreateWidget(
                'Link', _t('GLOBAL_DELETE'),
                "javascript:deleteEmblem({$e['id']});",
                STOCK_DELETE);
            $actions .= $link->Get().'&nbsp;';
            $item['actions'] = $actions;
            $entries_grid[] = $item;
        }

        return $entries_grid;
    }

    /**
     * Adds a new emblem
     *
     * @access  public
     * @see     EmblemsModel->AddEmblem()
     */
    function AddEmblem()
    {
        $post = jaws()->request->fetch(array('title', 'url', 'type', 'published'), 'post');
        $post['url'] = Jaws_XSS::defilter($post['url']);
        $res = Jaws_Utils::UploadFiles($_FILES, JAWS_DATA . 'emblems/', 'jpg,gif,swf,png,jpeg,bmp,svg');
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } elseif (empty($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_NO_IMAGE_UPLOADED'), RESPONSE_ERROR);
        } else {
            $post['image'] = $res['image'][0]['host_filename'];
            $post['published'] = (bool)$post['published'];
            $model = $this->gadget->model->loadAdmin('Emblems');
            $res = $model->AddEmblem($post);
            if (Jaws_Error::IsError($res)) {
                Jaws_Utils::delete(JAWS_DATA. 'emblems/'. $post['image']);
                $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_NOT_ADDED'), RESPONSE_ERROR);
            } else {
                $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ADDED'), RESPONSE_NOTICE);
            }
        }

        return Jaws_Header::Location(BASE_SCRIPT . '?gadget=Emblems');
    }
}