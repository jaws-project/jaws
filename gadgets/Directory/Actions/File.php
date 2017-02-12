<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 */
class Directory_Actions_File extends Jaws_Gadget_Action
{
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
        $model = $this->gadget->model->load('Files');
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
     * Get a file info
     *
     * @access  public
     * @return  array   Directory hierarchy
     */
    function GetFile()
    {
        $id = (int)jaws()->request->fetch('id');
        return $this->gadget->model->load('Files')->GetFile($id);
    }

    /**
     * Creates a new file
     *
     * @access  public
     * @return  array   Response array
     */
    function SaveFile()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $data = jaws()->request->fetch(
            array('id', 'parent', 'title', 'description', 'public', 'published')
        );

        if (!empty($data['title'])) {
            $result = $this->gadget->model->loadAdmin('Files')->SaveFile($data);
        } else {
            $result = Jaws_Error::raiseError(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'), __FUNCTION__, JAWS_ERROR_NOTICE);
        }
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushResponse(
                $result->getMessage(),
                'Directory.SaveFile',
                RESPONSE_ERROR
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(
                $result,
                'Directory.SaveFile',
                RESPONSE_NOTICE
            );
        }

        $referrer = jaws()->request->fetch('referrer');
        $referrer = parse_url(hex2bin($referrer));
        $redirectURL =
            (array_key_exists('path', $referrer)? $referrer['path'] : '') . 
            (array_key_exists('query', $referrer)? "?{$referrer['query']}" : '') . 
            (array_key_exists('fragment', $referrer)? "#{$referrer['fragment']}" : '');
        Jaws_Header::Location($redirectURL);
    }

    /**
     * Delete a file
     *
     * @access  public
     * @return  mixed   Number of forms or Jaws_Error
     */
    function DeleteFile()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $id = (int)jaws()->request->fetch('fileId', 'post');

        $model = $this->gadget->model->load('Files');
        $fileInfo = $this->gadget->model->load('Files')->GetFile($id);
        if (Jaws_Error::IsError($fileInfo)) {
            return Jaws_HTTPError::Get(500);
        }
        if (empty($fileInfo)) {
            return Jaws_HTTPError::Get(404);
        }
        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
        if ($fileInfo['public'] || $fileInfo['user'] != $currentUser) {
            return Jaws_HTTPError::Get(403);
        }

        $res = $model->DeleteFile($fileInfo);
        if (Jaws_Error::isError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_ITEMS_DELETED'), RESPONSE_NOTICE);
        }
    }

}