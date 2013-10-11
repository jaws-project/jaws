<?php
/**
 * Phoo AJAX API
 *
 * @category   Ajax
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Import an image located in 'import' folder
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function ImportImage()
    {
        $this->gadget->CheckPermission('Import');
        @list($image, $name, $album) = jaws()->request->fetchAll('post');
        $file = array();
        $file['tmp_name'] = JAWS_DATA . 'phoo/import/' . $image;
        $file['name'] = $image;
        $file['size'] = @filesize($file['tmp_name']);

        $aModel = $GLOBALS['app']->loadGadget('Phoo', 'Model', 'Albums');
        $pModel = $GLOBALS['app']->loadGadget('Phoo', 'AdminModel', 'Photos');
        $album_data = $aModel->getAlbumInfo($album);
        $id = $pModel->NewEntry($GLOBALS['app']->Session->GetAttribute('user'),
                                $file,
                                $name,
                                '',
                                false,
                                $album_data);
        $res = $pModel->AddEntryToAlbum($id, $album);
    }

    /**
     * Update album photo information
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdatePhoto()
    {
        @list($id, $title, $desc, $allow_comments, $published, $albums) = jaws()->request->fetchAll('post');
        $albums = jaws()->request->fetch('5:array', 'post');
        $desc = jaws()->request->fetch(2, 'post', false);
        if (!$this->gadget->GetPermission('ManageAlbums')) {
            $albums    = null;
            $published = null;
        }

        $model = $GLOBALS['app']->loadGadget('Phoo', 'AdminModel', 'Photos');
        $model->UpdateEntry($id, $title, $desc, $allow_comments, $published, $albums);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Add new group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddGroup()
    {
        $rqst = jaws()->request->fetch(array('name', 'description'));
        $rqst['[description]'] = $rqst['description'];
        unset($rqst['description']);
        $model = $this->gadget->load('Model')->load('AdminModel', 'Groups');
        $res = $model->AddGroup($rqst);

        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            $response =  array();
            $response['id']      = $res;
            $response['message'] = _t('PHOO_GROUPS_GROUP_CREATED');

            $GLOBALS['app']->Session->PushLastResponse($response, RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Edit a group
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function EditGroup()
    {
        $rqst = jaws()->request->fetch(array('name', 'description'));
        $gid  = (int) jaws()->request->fetch('id');
        $rqst['[description]'] = $rqst['description'];
        unset($rqst['description']);
        $model = $this->gadget->load('Model')->load('AdminModel', 'Groups');
        $res = $model->EditGroup($gid, $rqst);

        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_GROUPS_GROUP_UPDATED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets data of a group
     *
     * @access  public
     * @return  mixed   Group data array or False on error
     */
    function GetGroup()
    {
        $gid = jaws()->request->fetch('gid');
        $model = $this->gadget->load('Model')->load('Model', 'Groups');
        $group = $model->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            return false; //we need to handle errors on ajax
        }

        return $group;
    }
}