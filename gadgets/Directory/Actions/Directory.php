<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Actions_Directory extends Jaws_Gadget_HTML
{
    /**
     * Builds file management UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Directory()
    {
        $GLOBALS['app']->Layout->AddHeadLink('gadgets/Directory/resources/site_style.css');
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Workspace.html');
        $tpl->SetBlock('workspace');

        $tpl->SetVariable('title', _t('DIRECTORY_NAME'));
        $tpl->SetVariable('lbl_new_dir', _t('DIRECTORY_NEW_DIR'));
        $tpl->SetVariable('lbl_new_file', _t('DIRECTORY_NEW_FILE'));
        $tpl->SetVariable('new_dir', 'gadgets/Directory/images/new-dir.png');
        $tpl->SetVariable('new_file', 'gadgets/Directory/images/new-file.png');

        $tpl->SetVariable('lbl_share', _t('DIRECTORY_SHARE'));
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_props', _t('DIRECTORY_PROPERTIES'));
        $tpl->SetVariable('imgDeleteFile', STOCK_DELETE);
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $tpl->SetVariable('UID', $user);
        $tpl->SetVariable('data_url', $GLOBALS['app']->getDataURL('directory/'));

        // File template
        $tpl->SetBlock('workspace/fileTemplate');
        $tpl->SetVariable('id', '{id}');
        $tpl->SetVariable('title', '{title}');
        $tpl->SetVariable('description', '{description}');
        $tpl->SetVariable('type', '{type}');
        $tpl->SetVariable('size', '{size}');
        $tpl->SetVariable('shared', '{shared}');
        $tpl->ParseBlock('workspace/fileTemplate');

        // Status bar
        $tpl->SetBlock('workspace/statusbar');
        $tpl->SetVariable('title', '{title}');
        $tpl->SetVariable('size', '{size}');
        $tpl->SetVariable('created', '{created}');
        $tpl->SetVariable('modified', '{modified}');
        $tpl->ParseBlock('workspace/statusbar');

        // Display probabley responses
        $response = $GLOBALS['app']->Session->PopResponse('Directory');
        if ($response) {
            $tpl->SetVariable('response', $response['text']);
            $tpl->SetVariable('response_type', $response['type']);
        }

        $tpl->ParseBlock('workspace');
        return $tpl->Get();
    }

    /**
     * Fetches list of files
     *
     * @access  public
     * @return  mixed   Array of files or false on error
     */
    function GetFiles()
    {
        $flags = jaws()->request->fetch(array('parent', 'shared', 'shared_for_me'), 'post');
        _log_var_dump($flags);
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $files = $model->GetFiles($user, $flags['parent'], $flags['shared'], $flags['shared_for_me']);
        if (Jaws_Error::IsError($files)){
            return array();
        }
        $objDate = $GLOBALS['app']->loadDate();
        foreach ($files as &$file) {
            $file['created'] = $objDate->Format($file['createtime'], 'n/j/Y g:i a');
            $file['modified'] = $objDate->Format($file['updatetime'], 'n/j/Y g:i a');
            $file['size'] = Jaws_Utils::FormatSize($file['filesize']);
            $file['is_shared'] = $file['shared']? _t('DIRECTORY_IS_SHARED') : _t('DIRECTORY_NOT_SHARED');
        }
        return $files;
    }

    /**
     * Fetches data of a file/directory
     *
     * @access  public
     * @return  mixed   Array of file data or false on error
     */
    function GetFile()
    {
        $id = jaws()->request->fetch('id', 'post');
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $res = $model->GetFile($id);
        if (Jaws_Error::IsError($res)) {
            return array();
        }
        $objDate = $GLOBALS['app']->loadDate();
        $res['createtime'] = $objDate->Format($res['createtime'], 'n/j/Y g:i A');
        $res['updatetime'] = $objDate->Format($res['updatetime'], 'n/j/Y g:i A');
        $res['filesize'] = Jaws_Utils::FormatSize($res['filesize']);
        return $res;
    }

    /**
     * Fetches path of a file/directory
     *
     * @access  public
     * @return  array   Directory hierarchy
     */
    function GetPath()
    {
        $id = jaws()->request->fetch('id', 'post');
        $path = array();
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $model->GetPath($id, $path);
        return $path;
    }

}