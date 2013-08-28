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
     * @param   string  $image  Image file
     * @param   string  $name   Name of the image
     * @param   string  $album  In which album the image will be imported
     */
    function ImportImage($image, $name, $album)
    {
        $this->gadget->CheckPermission('Import');
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
     * @param   int     $id             Photo Id
     * @param   string  $title          Photo title
     * @param   string  $desc           Photo description
     * @param   bool    $allow_comments Comment status
     * @param   bool     $published      Publish status
     * @param    array    $albums
     * @return  array   Response array (notice or error)
     */
    function UpdatePhoto($id, $title, $desc, $allow_comments, $published, $albums = null)
    {
        if (!$this->gadget->GetPermission('ManageAlbums')) {
            $albums    = null;
            $published = null;
        }

        $request =& Jaws_Request::getInstance();
        $desc = $request->get(2, 'post', false);
        $model = $GLOBALS['app']->loadGadget('Phoo', 'AdminModel', 'Photos');
        $model->UpdateEntry($id, $title, $desc, $allow_comments, $published, $albums);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}