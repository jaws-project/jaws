<?php
/**
 * Filebrowser Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Actions_Admin_Directory extends Jaws_Gadget_Action
{
    /**
     * Creates and returns some data
     *
     * @access  public
     * @param   string  $dir
     * @param   int     $offset
     * @param   int     $order
     * @return  array   directory tree array
     */
    function GetDirectory($dir, $offset, $order)
    {
        $model = $this->gadget->model->load('Directory');
        $files = $model->ReadDir($dir, 15, $offset, $order);
        if (Jaws_Error::IsError($files)) {
            return array();
            //Jaws_Error::Fatal($files->getMessage(), __FILE__, __LINE__);
        }

        $tree = array();
        foreach ($files as $file) {
            $item = array();

            //Icon
            $link =& Piwi::CreateWidget('Image', $file['mini_icon']);
            $item['image'] = $link->Get();

            //Title
            $item['title'] = $file['title'];

            $actions = '';
            if ($file['is_dir']) {
                $link =& Piwi::CreateWidget('Link', $file['filename'], "javascript:cwd('{$file['relative']}');");
                $link->setStyle('float: left;');
                $item['name'] = $link->Get();

                if ($this->gadget->GetPermission('ManageDirectories')) {
                    //edit directory properties
                    $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                        "javascript:editDir(this, '{$file['filename']}');",
                        STOCK_EDIT);
                    $actions.= $link->Get().'&nbsp;';

                    //delete directory
                    $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                        "javascript:delDir(this, '{$file['filename']}');",
                        STOCK_DELETE);
                    $actions.= $link->Get().'&nbsp;';
                }
            } else {
                if (empty($file['id'])) {
                    $furl = Jaws_XSS::filter($file['url']);
                } else {
                    $fid = empty($file['fast_url'])? $file['id'] : Jaws_XSS::filter($file['fast_url']);
                    $furl = $this->gadget->urlMap('Download', array('id' => $fid));
                }

                $link =& Piwi::CreateWidget('Link', $file['filename'], $furl);
                $link->setStyle('float: left;');
                $item['name'] = $link->Get();

                if ($this->gadget->GetPermission('ManageFiles')) {
                    //edit file properties
                    $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                        "javascript:editFile(this, '{$file['filename']}');",
                        STOCK_EDIT);
                    $actions.= $link->Get().'&nbsp;';

                    //delete file
                    $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                        "javascript:delFile(this, '{$file['filename']}');",
                        STOCK_DELETE);
                    $actions.= $link->Get().'&nbsp;';
                }
            }

            $item['size']    = $file['size'];
            $item['hits']    = $file['hits'];
            $item['actions'] = $actions;

            $tree[] = $item;
        }

        return $tree;
    }


    /**
     * Show a form to edit a given directory
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetDirectoryUI()
    {
        $tpl = $this->gadget->template->loadAdmin('FileBrowser.html');
        $tpl->SetBlock('dir_ui');

        $dirname =& Piwi::CreateWidget('Entry', 'dirname', '');
        $dirname->SetID('dirname');
        $dirname->SetStyle('width: 270px;');
        $tpl->SetVariable('lbl_dirname', _t('FILEBROWSER_DIR_NAME'));
        $tpl->SetVariable('dirname', $dirname->Get());

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $title =& Piwi::CreateWidget('Entry', 'dir_title', '');
        $title->SetStyle('width: 270px;');
        $tpl->SetVariable('title', $title->Get());

        $desc =& Piwi::CreateWidget('TextArea', 'dir_description', '');
        $desc->SetID('dir_description');
        $desc->SetRows(5);
        $desc->SetStyle('width: 270px;');
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('description', $desc->Get());

        $tpl->SetVariable('lbl_fast_url', _t('FILEBROWSER_FASTURL'));
        $fasturl =& Piwi::CreateWidget('Entry', 'dir_fast_url', '');
        $fasturl->SetStyle('direction:ltr; width:270px;');
        $tpl->SetVariable('fast_url', $fasturl->Get());

        if ($this->gadget->GetPermission('ManageDirectories')) {
            $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, "javascript:saveDir();");
            $tpl->SetVariable('btn_save', $btnSave->Get());
        }

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, "javascript:stopAction('dir');");
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->ParseBlock('dir_ui');
        return $tpl->Get();
    }

    /**
     * Delete a directory in text editor mode
     *
     * @access  public
     * @return  void
     */
    function DeleteDir()
    {
        $this->gadget->CheckPermission('ManageDirectories');

        $model = $this->gadget->model->loadAdmin('Files');
        $post = jaws()->request->fetch(array('path', 'selected_item', 'extra_params'), 'post');

        if ($model->Delete($post['path'], $post['selected_item'])) {
            $model->DeleteDBFileInfo($post['path'], $post['selected_item']);
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=FileBrowser&action=BrowseFile&path=' . $post['path'] . html_entity_decode($post['extra_params']));
    }

}