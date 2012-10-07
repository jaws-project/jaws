<?php
/**
 * Filebrowser Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowserAdminHTML extends Jaws_GadgetHTML
{
    /**
     * Builds the basic datagrid view
     *
     * @access  private
     * @return  string   XHTML of datagrid
     */
    function DataGrid($path = '')
    {
        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'AdminModel');
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
     * @param   string  $dir
     * @param   int     $offset
     * @return  array
     */
    function GetDirectory($dir, $offset, $order)
    {
        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'AdminModel');
        $files = $model->ReadDir($dir, 15, $offset, $order);
        if (Jaws_Error::IsError($files)) {
            return array();
            //Jaws_Error::Fatal($files->getMessage(), __FILE__, __LINE__);
        }

        $tree = array();
        $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        foreach ($files as $file) {
            $item = array();

            //Icon
            $link =& Piwi::CreateWidget('Image', $file['mini_icon']);
            $item['image'] = $link->Get();

            //Title
            $item['title'] = $file['title'];

            $actions = '';
            if ($file['is_dir']) {
                $link =& Piwi::CreateWidget('Link', $file['filename'], "javascript: cwd('{$file['relative']}');");
                $link->setStyle('float: left;');
                $item['name'] = $link->Get();

                if ($this->GetPermission('ManageDirectories')) {
                    //edit directory properties
                    $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                                "javascript: editDir(this, '{$file['filename']}');",
                                                STOCK_EDIT);
                    $actions.= $link->Get().'&nbsp;';

                    //delete directory
                    $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                                "javascript: delDir(this, '{$file['filename']}');",
                                                STOCK_DELETE);
                    $actions.= $link->Get().'&nbsp;';
                }
            } else {
                if (empty($file['id'])) {
                    $furl = $xss->filter($file['url']);
                } else {
                    $fid = empty($file['fast_url'])? $file['id'] : $xss->filter($file['fast_url']);
                    $furl = $this->GetURLFor('Download', array('id' => $fid), false);
                }

                $link =& Piwi::CreateWidget('Link', $file['filename'], $furl);
                $link->setStyle('float: left;');
                $item['name'] = $link->Get();

                if ($this->GetPermission('ManageFiles')) {
                   //edit file properties
                    $link =& Piwi::CreateWidget('Link', _t('GLOBAL_EDIT'),
                                                "javascript: editFile(this, '{$file['filename']}');",
                                                STOCK_EDIT);
                    $actions.= $link->Get().'&nbsp;';

                    //delete file
                    $link =& Piwi::CreateWidget('Link', _t('GLOBAL_DELETE'),
                                                "javascript: delFile(this, '{$file['filename']}');",
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
     * Creates and returns some data
     *
     * @access  public
     * @param   string  $dir
     * @param   int     $offset
     * @return  array
     */
    function GetLocation($path)
    {
        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'AdminModel');

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
     * Prints the admin section
     *
     * @access  public
     * @return  string  HTML content of administration
     */
    function Admin()
    {
        $this->AjaxMe('script.js');

        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'AdminModel');
        $request =& Jaws_Request::getInstance();

        $tpl = new Jaws_Template('gadgets/FileBrowser/templates/');
        $tpl->Load('AdminFileBrowser.html');
        $tpl->SetBlock('filebrowser');
        $tpl->SetVariable('base_script', BASE_SCRIPT . '?gadget=FileBrowser&action=Admin');

        $request =& Jaws_Request::getInstance();
        $path = $request->get('path', 'get');
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
        $tpl->SetVariable('dui',  $this->GetDirectoryUI());
        $tpl->SetVariable('grid', $this->Datagrid($path));

        $tpl->SetVariable('confirmFileDelete', _t('FILEBROWSER_CONFIRM_DELETE_FILE'));
        $tpl->SetVariable('confirmDirDelete',  _t('FILEBROWSER_CONFIRM_DELETE_DIR'));

        $tpl->ParseBlock('filebrowser');
        return $tpl->Get();
    }

    /**
     * Show a form to edit a given file
     *
     * @access  public
     * @return  string HTML content
     */
    function GetFileUI()
    {
        $tpl = new Jaws_Template('gadgets/FileBrowser/templates/');
        $tpl->Load('AdminFileBrowser.html');
        $tpl->SetBlock('file_ui');

        $upload_switch =& Piwi::CreateWidget('CheckButtons', 'upload_switch');
        $upload_switch->AddEvent(ON_CLICK, 'javascript: uploadswitch(this.checked);');
        $upload_switch->AddOption(_t('FILEBROWSER_UPLOAD_FILE'), '0', 'upload_switch', true);
        $tpl->SetVariable('upload_switch', $upload_switch->Get());

        $filename =& Piwi::CreateWidget('Entry', 'filename', '');
        $filename->SetID('filename');
        $filename->SetStyle('width: 200px;');
        $tpl->SetVariable('lbl_filename', _t('FILEBROWSER_FILENAME'));
        $tpl->SetVariable('filename', $filename->Get());

        $uploadfile =& Piwi::CreateWidget('FileEntry', 'uploadfile', '');
        $uploadfile->SetID('uploadfile');
        $uploadfile->SetStyle('width: 208px;');
        $tpl->SetVariable('uploadfile', $uploadfile->Get());

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $title =& Piwi::CreateWidget('Entry', 'file_title', '');
        $title->SetStyle('width: 200px;');
        $tpl->SetVariable('title', $title->Get());

        $desc =& Piwi::CreateWidget('TextArea', 'file_description', '');
        $desc->SetID('file_description');
        $desc->SetRows(5);
        $desc->SetStyle('width: 200px;');
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('description', $desc->Get());

        $tpl->SetVariable('lbl_fast_url', _t('FILEBROWSER_FASTURL'));
        $fasturl =& Piwi::CreateWidget('Entry', 'file_fast_url', '');
        $fasturl->SetStyle('direction:ltr; width:200px;');
        $tpl->SetVariable('fast_url', $fasturl->Get());

        if ($this->GetPermission('ManageFiles')) {
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
     * Show a form to edit a given directory
     *
     * @access  public
     * @return  string HTML content
     */
    function GetDirectoryUI()
    {
        $tpl = new Jaws_Template('gadgets/FileBrowser/templates/');
        $tpl->Load('AdminFileBrowser.html');
        $tpl->SetBlock('dir_ui');

        $dirname =& Piwi::CreateWidget('Entry', 'dirname', '');
        $dirname->SetID('dirname');
        $dirname->SetStyle('width: 200px;');
        $tpl->SetVariable('lbl_dirname', _t('FILEBROWSER_DIR_NAME'));
        $tpl->SetVariable('dirname', $dirname->Get());

        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $title =& Piwi::CreateWidget('Entry', 'dir_title', '');
        $title->SetStyle('width: 200px;');
        $tpl->SetVariable('title', $title->Get());

        $desc =& Piwi::CreateWidget('TextArea', 'dir_description', '');
        $desc->SetID('dir_description');
        $desc->SetRows(5);
        $desc->SetStyle('width: 200px;');
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('description', $desc->Get());

        $tpl->SetVariable('lbl_fast_url', _t('FILEBROWSER_FASTURL'));
        $fasturl =& Piwi::CreateWidget('Entry', 'dir_fast_url', '');
        $fasturl->SetStyle('direction:ltr; width:200px;');
        $tpl->SetVariable('fast_url', $fasturl->Get());

        if ($this->GetPermission('ManageDirectories')) {
            $btnSave =& Piwi::CreateWidget('Button', 'btn_save', _t('GLOBAL_SAVE'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, "javascript: saveDir();");
            $tpl->SetVariable('btn_save', $btnSave->Get());
        }

        $btnCancel =& Piwi::CreateWidget('Button', 'btn_cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $btnCancel->AddEvent(ON_CLICK, "javascript: stopAction('dir');");
        $tpl->SetVariable('btn_cancel', $btnCancel->Get());

        $tpl->ParseBlock('dir_ui');
        return $tpl->Get();
    }

    /**
     * Uploads a new file
     *
     * @access       public
     */
    function UploadFile()
    {
        $this->CheckPermission('UploadFiles');

        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'AdminModel');
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('path', 'file_title', 'file_description', 'file_fast_url', 'oldname'), 'post');
        $uploaddir = $model->GetFileBrowserRootDir() . $post['path'];

        require_once 'File/Util.php';
        $uploaddir = File_Util::realpath($uploaddir) . DIRECTORY_SEPARATOR;

        if (!File_Util::pathInRoot($uploaddir, $model->GetFileBrowserRootDir())) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_UPLOAD'), RESPONSE_ERROR);
        } else {
            $res = Jaws_Utils::UploadFiles($_FILES,
                                           $uploaddir,
                                           '',
                                           $GLOBALS['app']->Registry->Get('/gadgets/FileBrowser/black_list'));
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
            } else {
                if (!empty($post['oldname']) && ($res['uploadfile'][0] != $post['oldname'])) {
                    $model->Delete($post['path'], $post['oldname']);
                }
                $model->UpdateDBFileInfo($post['path'],
                                         $res['uploadfile'][0],
                                         $post['file_title'],
                                         $post['file_description'],
                                         $post['file_fast_url'],
                                         $post['oldname']);
            }
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=FileBrowser&action=Admin&path=' . $post['path']);
    }


    /**
     * Browses for the files & directories on the server
     *
     * @access       public
     */
    function BrowseFile()
    {
        $request =& Jaws_Request::getInstance();
        $path = $request->get('path', 'get');
        $path = empty($path)? '/' : $path;

        $tpl = new Jaws_Template('gadgets/FileBrowser/templates/');
        $tpl->Load('BrowseFile.html');
        $tpl->SetBlock('browse');

        $tpl->SetVariable('page-title', _t('FILEBROWSER_NAME'));

        $dir = _t('GLOBAL_LANG_DIRECTION');
        $tpl->SetVariable('.dir', ($dir == 'rtl')? '.' . $dir : '');

        $extraParams = '';
        $editor = $GLOBALS['app']->GetEditor();
        if ($editor === 'TinyMCE') {
            $tpl->SetBlock('browse/script');
            $tpl->ParseBlock('browse/script');
        } elseif ($editor === 'CKEditor') {
            $getParams = $request->get(array('CKEditor', 'CKEditorFuncNum', 'langCode'), 'get');
            $extraParams = '&amp;CKEditor='.$getParams['CKEditor'].
                           '&amp;CKEditorFuncNum='.$getParams['CKEditorFuncNum'].
                           '&amp;langCode='.$getParams['langCode'];
            $tpl->SetVariable('ckFuncIndex', $getParams['CKEditorFuncNum']);
        }

        $model = $GLOBALS['app']->LoadGadget('FileBrowser', 'AdminModel');
        $pathArr = $model->GetCurrentRootDir($path);
        if (!Jaws_Error::IsError($pathArr)) {
            foreach ($pathArr as $_path => $dir)
            {
                if (!empty($dir) && $_path{0} == '/') {
                    $_path = substr($_path, 1);
                }
                $url = BASE_SCRIPT . '?gadget=FileBrowser&action=BrowseFile&path=' . $_path;
                if (empty($_path)) {
                    $link =& Piwi::CreateWidget('Link', _t('FILEBROWSER_ROOT'), $url . '/');
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

        $files = $model->ReadDir($path);
        if (!Jaws_Error::IsError($files)) {
            $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
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
                } else {
                    if (empty($file['id'])) {
                        $furl = $xss->filter($file['url']);
                    } else {
                        $fid = empty($file['fast_url'])? $file['id'] : $xss->filter($file['fast_url']);
                        $furl = $this->GetURLFor('Download', array('id' => $fid), false);
                    }
                    $link =& Piwi::CreateWidget('Link',
                                                $file['filename'],
                                                "javascript:selectFile('$furl', '{$file['title']}', '$editor')");
                    $tpl->SetVariable('file_name', $link->Get());
                }

                // File Size
                $tpl->SetVariable('file_size', $file['size']);
                $tpl->ParseBlock('browse/file');
            }
        }

        $tpl->ParseBlock('browse');
        return $tpl->Get();
    }

}