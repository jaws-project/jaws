<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 */
class Directory_Actions_Admin_Files extends Jaws_Gadget_Action
{
    /**
     * Determines file type according to the file extension
     */
    function getFileType($filename)
    {
        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
        if (empty($fileExt)) {
            return Directory_Info::FILE_TYPE_UNKNOWN;
        }
        $FileTypes = array(
            Directory_Info::FILE_TYPE_TEXT    => array('txt', 'doc', 'xml', 'html', 'htm', 'css', 'js', 'php', 'sh'),
            Directory_Info::FILE_TYPE_IMAGE   => array('gif', 'png', 'jpg', 'jpeg', 'raw', 'bmp', 'tiff', 'svg'),
            Directory_Info::FILE_TYPE_AUDIO   => array('wav', 'mp3', 'm4v', 'ogg'),
            Directory_Info::FILE_TYPE_VIDEO   => array('mpg', 'mpeg', 'avi', 'wma', 'rm', 'asf', 'flv', 'mov', 'mp4'),
            Directory_Info::FILE_TYPE_ARCHIVE => array('zip', 'rar', 'tar', 'gz', 'tgz', 'bz2', '7z', '7zip')
        );
        foreach ($FileTypes as $type => $exts) {
            foreach ($exts as $ext) {
                if ($fileExt == $ext) {
                    return $type;
                }
            }
        }
        return Directory_Info::FILE_TYPE_UNKNOWN;
    }

    /**
     * Builds the file management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function FileForm()
    {
        $mode = jaws()->request->fetch('mode');
        if ($mode === null) $mode = 'view';
        $tpl = $this->gadget->template->loadAdmin('File.html');
        $tpl->SetBlock($mode);
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_FILE_DESC'));
        $tpl->SetVariable('lbl_tags', _t('DIRECTORY_FILE_TAGS'));
        $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
        $tpl->SetVariable('lbl_url', _t('DIRECTORY_FILE_URL'));
        $tpl->SetVariable('lbl_thumbnail', _t('DIRECTORY_THUMBNAIL'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        if ($mode === 'edit') {
            $editor =& $GLOBALS['app']->LoadEditor('Directory', 'description');
            $editor->TextArea->SetStyle('width:100%; height:60px;');
            $tpl->SetVariable('description', $editor->get());
            $tpl->SetVariable('lbl_file', _t('DIRECTORY_FILE'));
            $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        } else {
            $tpl->SetVariable('lbl_filename', _t('DIRECTORY_FILE_FILENAME'));
            $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
            $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));
            $tpl->SetVariable('lbl_bytes', _t('DIRECTORY_BYTES'));
            $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
            $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
            $tpl->SetVariable('title', '{title}');
            $tpl->SetVariable('desc', '{description}');
            $tpl->SetVariable('tags', '{tags}');
            $tpl->SetVariable('user_filename', '{user_filename}');
            $tpl->SetVariable('type', '{type}');
            $tpl->SetVariable('mime_type', '{mime_type}');
            $tpl->SetVariable('size', '{size}');
            $tpl->SetVariable('file_size', '{file_size}');
            $tpl->SetVariable('link', '{link}');
            $tpl->SetVariable('create_time', '{create_time}');
            $tpl->SetVariable('update_time', '{update_time}');
            $tpl->SetVariable('created', '{created}');
            $tpl->SetVariable('modified', '{modified}');
        }

        $tpl->ParseBlock($mode);
        return $tpl->Get();
    }

    /**
     * Updates file
     *
     * @access  public
     * @return  array   Response array
     */
    function SaveFile()
    {
        $data = jaws()->request->fetch(
            array(
                'id', 'parent', 'title', 'description', 'public', 'published',
                'user_filename', 'host_filename', 'mime_type', 'file_size', 'thumbnail'
            )
        );

        if (!empty($data['title'])) {
            $result = $this->gadget->model->loadAdmin('Files')->SaveFile($data);
        } else {
            $result = Jaws_Error::raiseError(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'), __FUNCTION__);
        }

        if (Jaws_Error::IsError($result)) {
            return $GLOBALS['app']->Session->GetResponse(
                $result->getMessage(),
                RESPONSE_ERROR
            );
        } else {
            return $GLOBALS['app']->Session->GetResponse(
                $result,
                RESPONSE_NOTICE
            );
        }
    }

    /**
     * Generates file download URL
     *
     * @access  public
     * @return  string  Related URL
     */
    function GetDownloadURL($id = null)
    {
        if ($id === null) {
            $id = (int)jaws()->request->fetch('id');
        }
        return $this->gadget->urlMap('Download', array('id' => $id), true);
    }

    /**
     * Reads text file content
     *
     * @access  public
     * @return  string  Textual content
     */
    function GetFileContent($id)
    {
        $file = $this->gadget->model->load('Files')->GetFile($id);
        if (Jaws_Error::IsError($file) || empty($file) || empty($file['host_filename'])) {
            return;
        }
        $filename = JAWS_DATA . 'directory/' . $file['host_filename'];
        if (!file_exists($filename)) {
            return;
        }
        return file_get_contents($filename);
    }

    /**
     * Builds HTML5 audio/video tags for the file
     *
     * @access  public
     * @return  array   Response array
     */
    function PlayMedia()
    {
        $id = (int)jaws()->request->fetch('id');
        $type = jaws()->request->fetch('type');

        $tpl = $this->gadget->template->loadAdmin('Media.html');
        $tpl->SetBlock($type);
        if ($type === 'text') {
            $tpl->SetVariable('text', $this->GetFileContent($id));
        } else {
            $tpl->SetVariable('url', $this->GetDownloadURL($id));
        }
        $tpl->ParseBlock($type);

        return $this->gadget->plugin->parse($tpl->get());
    }

    /**
     * Uploads file to system temp directory
     *
     * @access  public
     * @return  string  JavaScript snippet
     */
    function UploadFile()
    {
        $response = array();
        $type = jaws()->request->fetch('type', 'post');
        $dirPath = JAWS_DATA . 'directory';
        if (!is_dir($dirPath)) {
            if (!Jaws_Utils::mkdir($dirPath)) {
                $response = array(
                    'type' => 'error',
                    'message' =>_t('DIRECTORY_ERROR_FILE_UPLOAD')
                );
            }
        }

        if (empty($response)) {
            $res = Jaws_Utils::UploadFiles($_FILES, $dirPath, '', null);
            if (Jaws_Error::IsError($res)) {
                $response = array('type' => 'error',
                                  'message' => $res->getMessage());
            } else {
                $response = array('type' => 'notice',
                                  'user_filename' => $res['file'][0]['user_filename'],
                                  'host_filename' => $res['file'][0]['host_filename'],
                                  'mime_type' => $res['file'][0]['host_filetype'],
                                  'file_size' => $res['file'][0]['host_filesize'],
                                  'upload_type' => $type);
            }
        }

        $response = Jaws_UTF8::json_encode($response);
        return "<script>parent.onUpload($response);</script>";
    }
}