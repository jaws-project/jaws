<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Actions_Directory extends Jaws_Gadget_Action
{
    /**
     * Builds directory and file navigation UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Directory()
    {
        $tpl = $this->gadget->template->load('Directory.html');
        $tpl->SetBlock('directory');

        $id = (int)jaws()->request->fetch('id');
        if ($id == 0) {
            $tpl->SetVariable('content', $this->ListFiles());
        } else {
            $model = $this->gadget->model->loadAdmin('Files');
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($file) || empty($file)) {
                require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
                return Jaws_HTTPError::Get(404);
            }
            if ($file['is_dir']) {
                $tpl->SetVariable('content', $this->ListFiles($id));
            } else {
                $tpl->SetVariable('content', $this->ViewFile($file));
            }
            $tpl->SetVariable('root', _t('DIRECTORY_HOME'));
            $tpl->SetVariable('root_url', $this->gadget->urlMap('Directory'));
            $tpl->SetVariable('path', $this->GetPath($id));
        }

        $tpl->ParseBlock('directory');
        return $tpl->Get();
    }

    /**
     * Fetches and displays list of dirs/files
     *
     * @access  public
     * @return  string  HTML content
     */
    function ListFiles($parent = 0)
    {
        $model = $this->gadget->model->loadAdmin('Files');
        $files = $model->GetFiles(array('parent' => $parent));
        if (Jaws_Error::IsError($files)){
            return '';
        }
        if (empty($files)){
            return _t('DIRECTORY_INFO_NO_FILES');
        }

        $tpl = $this->gadget->template->load('Directory.html');
        $tpl->SetBlock('files');

        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
        $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
        $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
        $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));

        $tpl->SetVariable('site_url', $GLOBALS['app']->getSiteURL('/'));
        $theme = $GLOBALS['app']->GetTheme();
        $iconUrl = is_dir($theme['url'] . 'mimetypes')?
            $theme['url'] . 'mimetypes/' : 'images/mimetypes/';

        $objDate = Jaws_Date::getInstance();
        foreach ($files as $file) {
            $url = $this->gadget->urlMap('Directory', array('id' => $file['id']));
            $tpl->SetBlock('files/file');
            $tpl->SetVariable('url', $url);
            $tpl->SetVariable('title', $file['title']);
            $tpl->SetVariable('type', empty($file['filetype'])? '-' : $file['filetype']);
            $tpl->SetVariable('size', Jaws_Utils::FormatSize($file['filesize']));
            $tpl->SetVariable('created', $objDate->Format($file['createtime'], 'n/j/Y g:i a'));
            $tpl->SetVariable('modified', $objDate->Format($file['updatetime'], 'n/j/Y g:i a'));
            if ($file['is_dir']) {
                $tpl->SetVariable('icon', $iconUrl . 'folder.png');
            } else {
                if (!empty($file['filetype'])) {
                    $tpl->SetVariable('icon', $iconUrl . $file['type']);
                } else {
                    $tpl->SetVariable('icon', $iconUrl . 'file-generic.png');
                }
            }
            $tpl->ParseBlock('files/file');
        }

        $tpl->ParseBlock('files');
        return $tpl->Get();
    }

    /**
     * Displays file properties
     *
     * @access  public
     * @return  string  HTML content
     */
    function ViewFile($file)
    {
        $tpl = $this->gadget->template->load('Directory.html');
        $tpl->SetBlock('file');

        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_FILE_DESC'));
        $tpl->SetVariable('lbl_filename', _t('DIRECTORY_FILE_FILENAME'));
        $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
        $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));
        $tpl->SetVariable('lbl_bytes', _t('DIRECTORY_BYTES'));
        $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
        $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
        $tpl->SetVariable('lbl_download', _t('DIRECTORY_DOWNLOAD'));

        $objDate = Jaws_Date::getInstance();
        $file['created'] = $objDate->Format($file['createtime'], 'n/j/Y g:i a');
        $file['modified'] = $objDate->Format($file['updatetime'], 'n/j/Y g:i a');
        $file['type'] = empty($file['filetype'])? '-' : $file['filetype'];
        $file['size'] = Jaws_Utils::FormatSize($file['filesize']);
        $file['download'] = $this->gadget->urlMap('Download', array('id' => $file['id']));
        foreach ($file as $key => $value) {
            $tpl->SetVariable($key, $value);
        }

        // display file
        $fileInfo = pathinfo($file['host_filename']);
        if (isset($fileInfo['extension'])) {
            $ext = $fileInfo['extension'];
            $type = '';
            if ($ext === 'txt') {
                $type = 'text';
            } else if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'svg'))) {
                $type = 'image';
            } else if (in_array($ext, array('wav', 'mp3'))) {
                $type = 'audio';
            } else if (in_array($ext, array('webm', 'mp4', 'ogg'))) {
                $type = 'video';
            }
            if ($type != '') {
                $action = $this->gadget->action->load('File');
                $tpl->SetVariable('preview', $this->PlayMedia($file, $type));
            }
        }

        $tpl->ParseBlock('file');
        return $tpl->Get();
    }

    /**
     * Fetches path of a file/directory
     *
     * @access  public
     * @return  array   Directory hierarchy
     */
    function GetPath($id)
    {
        $path = '';
        $pathArr = array();
        $model = $this->gadget->model->loadAdmin('Files');
        $model->GetPath($id, $pathArr);
        foreach(array_reverse($pathArr) as $i => $p) {
            $url = $this->gadget->urlMap('Directory', array('id' => $p['id']));
            $path .= ($i == count($pathArr) - 1)?
                ' > ' . $p['title'] :
                " > <a href='$url'>" . $p['title'] . '</a>';
        }
        return $path;
    }

    /**
     * Displays file
     *
     * @access  public
     * @return  array   Response array
     */
    function PlayMedia($file, $type)
    {
        $tpl = $this->gadget->template->loadAdmin('Media.html');
        $tpl->SetBlock($type);
        if ($type === 'text') {
            $filename = $GLOBALS['app']->getDataURL('directory/') . $file['host_filename'];
            if (file_exists($filename)) {
                $tpl->SetVariable('text', file_get_contents($filename));
            }
        } else {
            $tpl->SetVariable('url', $this->gadget->urlMap('Download', array('id' => $file['id'])));
        }
        $tpl->ParseBlock($type);

        return $this->gadget->ParseText($tpl->get(), 'Directory', 'index');
    }

    /**
     * Downloads(streams) file
     *
     * @access  public
     * @return  mixed   File data or Jaws_Error
     */
    function Download()
    {
        $id = jaws()->request->fetch('id');
        if (is_null($id)) {
            return Jaws_HTTPError::Get(500);
        }
        $id = (int)$id;
        $model = $this->gadget->model->loadAdmin('Files');

        // Validate file
        $file = $model->GetFile($id);
        if (Jaws_Error::IsError($file)) {
            return Jaws_HTTPError::Get(500);
        }
        if (empty($file) || empty($file['user_filename'])) {
            return Jaws_HTTPError::Get(404);
        }

        // Check for file existence
        $filename = $GLOBALS['app']->getDataURL("directory/") . $file['host_filename'];
        if (!file_exists($filename)) {
            return Jaws_HTTPError::Get(404);
        }

        // Stream file
        if (!Jaws_Utils::Download($filename, $file['user_filename'], $file['filetype'])) {
            return Jaws_HTTPError::Get(500);
        }

        return true;
    }
}