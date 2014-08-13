<?php
/**
 * Filebrowser Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Actions_Admin_Files extends Jaws_Gadget_Action
{
    /**
     * Show Admin action
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Files()
    {
        $this->AjaxMe('script.js');

        $dHTML = $this->gadget->action->loadAdmin('Directory');

        $tpl = $this->gadget->template->loadAdmin('FileBrowser.html');
        $tpl->SetBlock('filebrowser');
        $tpl->SetVariable('base_script', BASE_SCRIPT . '?gadget=FileBrowser&action=Files');

        $path = jaws()->request->fetch('path', 'get');
        $path = empty($path)? '/' : $path;
        $tpl->SetVariable('path', $path);

        $tpl->SetVariable('lbl_location', _t('FILEBROWSER_LOCATION'));
        $tpl->SetVariable('location_link', $this->GetLocation($path));

        $orderType =& Piwi::CreateWidget('Combo', 'order_type');
        $orderType->AddOption(_t('GLOBAL_TITLE') . ' &darr;', 'title, false');
        $orderType->AddOption(_t('GLOBAL_TITLE') . ' &uarr;', 'title, true');
        $orderType->AddOption(_t('FILEBROWSER_FILENAME') . ' &darr;', 'filename, false');
        $orderType->AddOption(_t('FILEBROWSER_FILENAME') . ' &uarr;', 'filename, true');
        $orderType->AddOption(_t('GLOBAL_DATE') . ' &darr;', 'date, false');
        $orderType->AddOption(_t('GLOBAL_DATE') . ' &uarr;', 'date, true');
        $orderType->SetDefault('filename, false');
        $orderType->AddEvent(ON_CHANGE, 'javascript: reOrderFiles();');
        $tpl->SetVariable('lbl_order', _t('FILEBROWSER_ORDER_BY'));
        $tpl->SetVariable('order_type', $orderType->Get());

        $tpl->SetVariable('lbl_file',      _t('FILEBROWSER_FILE'));
        $tpl->SetVariable('lbl_directory', _t('FILEBROWSER_DIR'));
        $tpl->SetVariable('fui',  $this->GetFileUI());
        $tpl->SetVariable('dui',  $dHTML->GetDirectoryUI());
        $tpl->SetVariable('grid', $this->Datagrid($path));

        $tpl->SetVariable('incompleteFields', _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmFileDelete', _t('FILEBROWSER_CONFIRM_DELETE_FILE'));
        $tpl->SetVariable('confirmDirDelete',  _t('FILEBROWSER_CONFIRM_DELETE_DIR'));

        $tpl->ParseBlock('filebrowser');
        return $tpl->Get();
    }

   /**
     * Show a form to edit a given file
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetFileUI()
    {
        $tpl = $this->gadget->template->loadAdmin('FileBrowser.html');
        $tpl->SetBlock('file_ui');

        $upload_switch =& Piwi::CreateWidget('CheckButtons', 'upload_switch');
        $upload_switch->AddEvent(ON_CLICK, 'javascript: uploadswitch(this.checked);');
        $upload_switch->AddOption(_t('FILEBROWSER_UPLOAD_FILE'), '0', 'upload_switch', true);
        $tpl->SetVariable('upload_switch', $upload_switch->Get());

        $filename =& Piwi::CreateWidget('Entry', 'filename', '');
        $filename->SetID('filename');
        $filename->SetStyle('width: 270px;');
        $tpl->SetVariable('lbl_filename', _t('FILEBROWSER_FILENAME'));
        $tpl->SetVariable('filename', $filename->Get());

        $uploadfile =& Piwi::CreateWidget('FileEntry', 'uploadfile', '');
        $uploadfile->SetID('uploadfile');
        $uploadfile->SetStyle('width: 270px;');
        $tpl->SetVariable('uploadfile', $uploadfile->Get());

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $title =& Piwi::CreateWidget('Entry', 'file_title', '');
        $title->SetStyle('width: 270px;');
        $tpl->SetVariable('title', $title->Get());

        $desc =& Piwi::CreateWidget('TextArea', 'file_description', '');
        $desc->SetID('file_description');
        $desc->SetRows(5);
        $desc->SetStyle('width: 270px;');
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('description', $desc->Get());

        $tpl->SetVariable('lbl_fast_url', _t('FILEBROWSER_FASTURL'));
        $fasturl =& Piwi::CreateWidget('Entry', 'file_fast_url', '');
        $fasturl->SetStyle('direction:ltr; width:270px;');
        $tpl->SetVariable('fast_url', $fasturl->Get());

        if ($this->gadget->GetPermission('ManageFiles')) {
            $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, "javascript: saveFile();");
            $tpl->SetVariable('btn_save', $btnSave->Get());
        }

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, "javascript: stopAction('file');");
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->ParseBlock('file_ui');
        return $tpl->Get();
    }

    /**
     * Builds the basic datagrid view
     *
     * @access  public
     * @param   string  $path
     * @return  string  XHTML template of datagrid
     */
    function DataGrid($path = '')
    {
        $model = $this->gadget->model->load('Directory');
        $total = $model->GetDirContentsCount($path);

        $grid =& Piwi::CreateWidget('DataGrid', array());
        $grid->TotalRows($total);
        $grid->pageBy(15);
        $grid->SetID('fb_datagrid');
        $column = Piwi::CreateWidget('Column', '');
        $column->SetStyle('width: 1px;');
        $grid->AddColumn($column);
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_TITLE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_NAME')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('FILEBROWSER_SIZE')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('FILEBROWSER_HITS')));
        $grid->AddColumn(Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS')));
        $grid->SetStyle('width: 100%;');

        return $grid->Get();
    }

    /**
     * Creates and returns some data
     *
     * @access  public
     * @param   string  $path   
     * @return  string  location link string   
     */
    function GetLocation($path)
    {
        $model = $this->gadget->model->load('Directory');

        $dir_array = $model->GetCurrentRootDir($path);
        $path_link = '';
        $location_link = '';
        foreach ($dir_array as $d) {
            $path_link .= $d . (($d != '/')? '/' : '');
            $link =& Piwi::CreateWidget('Link', $d, "javascript: cwd('{$path_link}');");
            $location_link .= $link->Get() . '&nbsp;';
        }

        return $location_link;
    }

    /**
     * Uploads a new file
     *
     * @access  public
     */
    function UploadFile()
    {
        $this->gadget->CheckPermission('UploadFiles');

        $fModel = $this->gadget->model->load('Files');
        $fModelAdmin = $this->gadget->model->loadAdmin('Files');
        $post = jaws()->request->fetch(
            array('path', 'file_title', 'file_description', 'file_fast_url', 'oldname', 'extra_params'),
            'post'
        );
        $uploaddir = $fModel->GetFileBrowserRootDir() . $post['path'];

        require_once PEAR_PATH. 'File/Util.php';
        $uploaddir = File_Util::realpath($uploaddir) . DIRECTORY_SEPARATOR;

        if (!File_Util::pathInRoot($uploaddir, $fModel->GetFileBrowserRootDir())) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_UPLOAD'), RESPONSE_ERROR);
        } else {
            $res = Jaws_Utils::UploadFiles($_FILES, $uploaddir, '');
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
            } elseif (empty($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_UPLOAD_4'), RESPONSE_ERROR);
            } else {
                $post['oldname'] = preg_replace('/[^[:alnum:]_\.\-]*/', '', $post['oldname']);
                if (!empty($post['oldname']) && ($res['uploadfile'][0]['host_filename'] != $post['oldname'])) {
                    $fModelAdmin->Delete($post['path'], $post['oldname']);
                }
                $fModelAdmin->UpdateDBFileInfo(
                    $post['path'],
                    $res['uploadfile'][0]['host_filename'],
                    empty($post['file_title'])? $res['uploadfile'][0]['user_filename'] : $post['file_title'],
                    $post['file_description'],
                    $post['file_fast_url'],
                    $post['oldname']
                );
            }
        }

        if (empty($post['extra_params'])) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=FileBrowser&action=Files&path=' . $post['path']);
        } else {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=FileBrowser&action=BrowseFile&path=' . $post['path'] . html_entity_decode($post['extra_params']));
        }
    }


    /**
     * Browses for the files & directories on the server
     *
     * @access  public
     * @return  string  XHTML template content for browing file
     */
    function BrowseFile()
    {
        $path = jaws()->request->fetch('path', 'get');
        $path = empty($path)? '/' : $path;

        $tpl = $this->gadget->template->loadAdmin('BrowseFile.html');
        $tpl->SetBlock('browse');

        $tpl->SetVariable('page-title', $this->gadget->title);
        $tpl->SetVariable('incompleteFields', _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'));
        $tpl->SetVariable('confirmFileDelete', _t('FILEBROWSER_CONFIRM_DELETE_FILE'));
        $tpl->SetVariable('confirmDirDelete', _t('FILEBROWSER_CONFIRM_DELETE_DIR'));

        $dir = _t('GLOBAL_LANG_DIRECTION');
        $tpl->SetVariable('.dir', ($dir == 'rtl')? '.' . $dir : '');

        // TODO set default value for change page address to correct location after uploading file
        $extraParams = '&amp;';
        $editor = $GLOBALS['app']->GetEditor();
        if ($editor === 'TinyMCE') {
            $tpl->SetBlock('browse/script');
            $tpl->ParseBlock('browse/script');
        } elseif ($editor === 'CKEditor') {
            $getParams = jaws()->request->fetch(array('CKEditor', 'CKEditorFuncNum', 'langCode'), 'get');
            $extraParams = '&amp;CKEditor='.$getParams['CKEditor'].
                '&amp;CKEditorFuncNum='.$getParams['CKEditorFuncNum'].
                '&amp;langCode='.$getParams['langCode'];
            $tpl->SetVariable('ckFuncIndex', $getParams['CKEditorFuncNum']);
        }

        if ($this->gadget->GetPermission('UploadFiles')) {
            $tpl->SetBlock("browse/upload_file");

            $tpl->SetVariable('path', $path);
            $tpl->SetVariable('extra_params', $extraParams);
            $tpl->SetVariable('lbl_file_upload', _t('FILEBROWSER_UPLOAD_FILE'));

            $title =& Piwi::CreateWidget('Entry', 'file_title', '');
            $title->SetStyle('width: 200px;');
            $tpl->SetVariable('lbl_file_title', _t('GLOBAL_TITLE'));
            $tpl->SetVariable('file_title', $title->Get());

            $uploadfile =& Piwi::CreateWidget('FileEntry', 'uploadfile', '');
            $uploadfile->SetID('uploadfile');
            $tpl->SetVariable('lbl_filename', _t('FILEBROWSER_FILENAME'));
            $tpl->SetVariable('uploadfile', $uploadfile->Get());

            $btnSave =& Piwi::CreateWidget('Button', 'btn_upload_file', _t('FILEBROWSER_UPLOAD_FILE'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, "javascript: saveFile();");
            $tpl->SetVariable('btn_upload_file', $btnSave->Get());

            $tpl->ParseBlock("browse/upload_file");
        }

        $fModel = $this->gadget->model->load('Files');
        $dModel = $this->gadget->model->load('Directory');
        $pathArr = $dModel->GetCurrentRootDir($path);
        if (!Jaws_Error::IsError($pathArr)) {
            foreach ($pathArr as $_path => $dir)
            {
                if (!empty($dir) && $_path{0} == '/') {
                    $_path = substr($_path, 1);
                }
                $url = BASE_SCRIPT . '?gadget=FileBrowser&action=BrowseFile&path=' . $_path;
                if (empty($_path)) {
                    $link =& Piwi::CreateWidget('Link', _t('FILEBROWSER_ROOT'), $url . '/' . $extraParams);
                    $tpl->SetVariable('root', $link->Get());
                } else {
                    if ($_path == $path) {
                        $link = Piwi::CreateWidget('StaticEntry', $dir);
                    } else {
                        $link = Piwi::CreateWidget('Link', $dir, $url);
                    }
                    $tpl->SetBlock('browse/path');
                    $tpl->SetVariable('directory', $link->Get());
                    $tpl->ParseBlock('browse/path');
                }
            }
        }

        $tpl->SetVariable('lbl_location', _t('FILEBROWSER_LOCATION'));
        $tpl->SetVariable('lbl_file_name', _t('FILEBROWSER_FILENAME'));
        $tpl->SetVariable('lbl_file_size', _t('FILEBROWSER_SIZE'));
        $tpl->SetVariable('lbl_action', _t('GLOBAL_ACTIONS'));

        $files = $dModel->ReadDir($path);
        if (!Jaws_Error::IsError($files)) {
            foreach ($files as $file) {
                $tpl->SetBlock('browse/file');

                // Icon
                $icon =& Piwi::CreateWidget('Image', $file['mini_icon']);
                $icon->SetID('');
                $tpl->SetVariable('icon', $icon->Get());

                // Directory / File
                if ($file['is_dir']) {
                    $url = BASE_SCRIPT. '?gadget=FileBrowser&action=BrowseFile&path='. $file['relative']. $extraParams;
                    $link =& Piwi::CreateWidget('Link', $file['filename'], $url);
                    $link->SetID('');
                    $link->SetTitle($file['title']);
                    $tpl->SetVariable('file_name', $link->Get());

                    if ($this->gadget->GetPermission('ManageDirectories')) {
                        $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                            "javascript: deleteDir('" . $file['filename'] . "');",
                            STOCK_DELETE);
                        $tpl->SetVariable('action', $link->Get());
                    }


                } else {
                    if (empty($file['id'])) {
                        $furl = Jaws_XSS::filter($file['url']);
                    } else {
                        $fid = empty($file['fast_url'])? $file['id'] : Jaws_XSS::filter($file['fast_url']);
                        $furl = $this->gadget->urlMap('Download', array('id' => $fid));
                    }
                    $link =& Piwi::CreateWidget('Link',
                        $file['filename'],
                        "javascript:selectFile('$furl', '{$file['title']}', '$editor')");
                    $tpl->SetVariable('file_name', $link->Get());

                    if ($this->gadget->GetPermission('ManageFiles')) {
                        $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                            "javascript: deleteFile('" . $file['filename'] . "');",
                            STOCK_DELETE);
                        $tpl->SetVariable('action', $link->Get());
                    }

                }

                // File Size
                $tpl->SetVariable('file_size', $file['size']);
                $tpl->ParseBlock('browse/file');
            }
        }

        $tpl->ParseBlock('browse');
        return $tpl->Get();
    }

    /**
     * Delete a file in text editor mode
     *
     * @access  public
     * @return  void
     */
    function DeleteFile()
    {
        $this->gadget->CheckPermission('ManageFiles');

        $model = $this->gadget->model->loadAdmin('Files');
        $post = jaws()->request->fetch(array('path', 'selected_item', 'extra_params'), 'post');

        if ($model->Delete($post['path'], $post['selected_item'])) {
            $model->DeleteDBFileInfo($post['path'], $post['selected_item']);
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=FileBrowser&action=BrowseFile&path=' . $post['path'] . html_entity_decode($post['extra_params']));
    }

}