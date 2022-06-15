<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_Import extends Phoo_Actions_Admin_Default
{
    /**
     * Import pictures in 'import' folder
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function Import()
    {
        $this->gadget->CheckPermission('Import');
        $tpl = $this->gadget->template->loadAdmin('Import.html');
        $tpl->SetBlock('import');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('menubar', $this->MenuBar('Import'));
        $iModel = $this->gadget->model->loadAdmin('Import');
        $aModel = $this->gadget->model->load('Albums');
        $items = $iModel->GetItemsToImport();
        if (count($items) > 0) {
            $tpl->SetBlock('import/pictures');
            $tpl->SetVariable('ready_to_import', $this::t('READY_TO_IMPORT', count($items)));
            $gadget =& Piwi::CreateWidget('HiddenEntry', 'reqGadget', 'Phoo');
            $tpl->SetVariable ('gadget_hidden', $gadget->Get());
            $action =& Piwi::CreateWidget('HiddenEntry', 'reqAction', 'FinishImport');
            $tpl->SetVariable ('action_hidden', $action->Get());
            $tpl->SetVariable ('import_message', $this::t('IMPORT_MESSAGE'));
            $albumcombo =& Piwi::CreateWidget('Combo', 'album', $this::t('ALBUM'));
            $first = false;
            $albums = $aModel->GetAlbums('name', 'ASC');
            if (!Jaws_Error::IsError($albums) && !empty($albums)) {
                foreach ($albums as $a) {
                    if (!$first) {
                        $first = $a['id'];
                    }
                    $albumcombo->AddOption($a['name'], $a['id']);
                }
            }
            $albumcombo->SetDefault($first);
            $tpl->SetVariable ('albums_combo', $albumcombo->Get());
            $b =& Piwi::CreateWidget('Button', 'import_button', $this::t('IMPORT'), STOCK_DOWN);
            $b->SetSubmit(true);
            $tpl->SetVariable ('import_button', $b->Get());
            $counter = 0;
            include_once ROOT_JAWS_PATH . 'include/Jaws/Image.php';
            foreach ($items as $i) {
                $tpl->SetBlock('import/pictures/item');
                $tpl->SetVariable('thumb', BASE_SCRIPT . '?reqGadget=Phoo&amp;reqAction=Thumb&amp;image='.$i);
                $tpl->SetVariable('filename', $i);
                $tpl->SetVariable('entryname', md5($i));
                $tpl->SetVariable('counter',(string)$counter);
                $tpl->ParseBlock('import/pictures/item');
                $counter++;
            }
            $tpl->ParseBlock('import/pictures');
        } else {
            $tpl->SetBlock('import/noitems');
            $tpl->SetVariable('no_items_to_import', $this::t('NO_IMAGES_TO_IMPORT'));
            $tpl->SetVariable('message', $this::t('IMPORT_INSTRUCTIONS'));
            $tpl->ParseBlock('import/noitems');
        }
        $tpl->ParseBlock('import');
        return $tpl->Get();
    }

    /**
     * Import selected images
     *
     * @access  public
     * @return  string   XHTML with the results of the importation
     */
    function FinishImport()
    {
        $this->gadget->CheckPermission('Import');
        $this->AjaxMe('script.js');

        $post = $this->gadget->request->fetch(array('album', 'images:array'), 'post');

        $tpl = $this->gadget->template->loadAdmin('FinishImport.html');
        $tpl->SetBlock('finishimport');
        $tpl->SetVariable('menubar', $this->MenuBar('Import'));
        $tpl->SetVariable('importing', $this::t('IMPORTING'));
        $tpl->SetVariable('album', $post['album']);
        $tpl->SetVariable('howmany', (string)count($post['images']));
        $tpl->SetVariable('indicator_image', 'gadgets/ControlPanel/Resources/images/indicator.gif');
        $tpl->SetVariable('ok_image', STOCK_OK);
        $tpl->SetVariable('finished', $this::t('FINISHED'));
        $tpl->SetVariable('import_warning', $this::t('IMPORTING_WARNING'));
        $counter = 0;
        foreach ($post['images'] as $image) {
            $tpl->SetBlock('finishimport/items');
            $tpl->SetVariable('counter', (string)$counter);
            $tpl->SetVariable('image', $image);
            $tpl->SetVariable('name',  md5($image));
            $tpl->ParseBlock('finishimport/items');
            $counter++;
        }
        $tpl->ParseBlock('finishimport');
        return $tpl->Get();
    }
}