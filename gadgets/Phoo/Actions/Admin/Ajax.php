<?php
/**
 * Phoo AJAX API
 *
 * @category   Ajax
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_Ajax extends Jaws_Gadget_Action
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

        $aModel = $this->gadget->model->load('Albums');
        $pModel = $this->gadget->model->loadAdmin('Photos');
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
        @list($id, $title, $desc, $allow_comments, $published, $albums, $meta_keywords, $meta_description, $tags) =
            jaws()->request->fetchAll('post');
        $albums = jaws()->request->fetch('5:array', 'post');
        $desc = jaws()->request->fetch(2, 'post', 'strip_crlf');
        if (!$this->gadget->GetPermission('ManageAlbums')) {
            $albums    = null;
            $published = null;
        }

        $model = $this->gadget->model->loadAdmin('Photos');
        $model->UpdateEntry($id, $title, $desc, $meta_keywords, $meta_description,
            $allow_comments, $published, $albums, $tags);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    function GetAlbums()
    {
        $aModel = $this->gadget->model->load('Albums');
        $albums = $aModel->GetAlbums('createtime', 'ASC');
        $free_photos[] = array('id'         => 0,
            'name'       => _t('PHOO_WITHOUT_ALBUM'),
            'createtime' => date('Y-m-d H:i:s'),
            'howmany'    => 0);
        if (Jaws_Error::IsError($albums) || !is_array($albums)) {
            $albums = $free_photos;
        } else {
            $albums = array_merge($free_photos, $albums);
        }
        return $albums;
    }
}