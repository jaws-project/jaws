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
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Directory.html');
        $tpl->SetBlock('directory');

        $tpl->SetVariable('title', _t('DIRECTORY_NAME'));
        $tpl->SetVariable('lbl_new', _t('GLOBAL_NEW'));
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('imgDeleteFile', STOCK_DELETE);
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $tpl->SetVariable('data_url', $GLOBALS['app']->getDataURL('directory/' . $user));

        // Display probabley responses
        $response = $GLOBALS['app']->Session->PopResponse('Directory');
        if ($response) {
            $tpl->SetVariable('response', $response['text']);
            $tpl->SetVariable('response_type', $response['type']);
        }

        $tpl->ParseBlock('directory');
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
        $parent = jaws()->request->fetch('parent', 'post');
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $res = $model->GetFiles($parent);
        if (Jaws_Error::IsError($res)){
            return array();
        }
        return $res;
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