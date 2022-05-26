<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Admin_Photos extends Phoo_Model_Common
{
    /**
     * Update the information of an image
     *
     * @access  public
     * @param   int     $id                 ID of the image
     * @param   string  $title              Title of the image
     * @param   string  $description        Description of the image
     * @param   string  $meta_keywords      Meta keywords
     * @param   string  $meta_description   Meta description
     * @param   bool    $allow_comments     True is comments allowed, False is not allowed
     * @param   bool    $published          true for Published, false for Hidden
     * @param   array   $albums
     * @param   string  $tags
     * @return  mixed   True if entry was updated successfully and Jaws_Error if not
     */
    function UpdateEntry($id, $title, $description, $meta_keywords, $meta_description, $allow_comments, $published, $albums = null, $tags = '')
    {
        $data = array();
        $data['title'] = $title;
        $data['description'] = $description;
        $data['meta_keywords'] = $meta_keywords;
        $data['meta_description'] = $meta_description;
        $data['allow_comments'] = $allow_comments;
        $data['updatetime'] = Jaws_DB::getInstance()->date();
        $data['published'] = (bool)$published;

        $table = Jaws_ORM::getInstance()->table('phoo_image');
        $result = $table->update($data)->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('ERROR_CANT_UPDATE_PHOTO'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_CANT_UPDATE_PHOTO'));
        }

        if ($albums !== null) {
            $this->SetEntryAlbums($id, $albums);
        }

        // Insert Tags
        if (Jaws_Gadget::IsGadgetInstalled('Tags') && !empty($tags)) {
            $tModel = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            $tModel->InsertReferenceTags('Phoo', 'image', $id, $data['published'], time(), $tags);
        }

        $this->gadget->session->push($this::t('PHOTO_UPDATED'), RESPONSE_NOTICE);
        return true;
    }


    /**
     * Delete an image
     *
     * @access  public
     * @param   int     $id     ID of the image
     * @return  mixed   True if entry was deleted successfully and Jaws_Error if not
     */
    function DeletePhoto($id)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_image');
        $image = $table->select('filename')->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($image)) {
            $this->gadget->session->push($this::t('IMPOSSIBLE_DELETE_IMAGE'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('IMPOSSIBLE_DELETE_IMAGE'));
        }

        $table->reset();
        $result = $table->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('IMPOSSIBLE_DELETE_IMAGE'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('IMPOSSIBLE_DELETE_IMAGE'));
        }

        $table = Jaws_ORM::getInstance()->table('phoo_image_album');
        $result = $table->delete()->where('phoo_image_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('IMPOSSIBLE_DELETE_IMAGE'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('IMPOSSIBLE_DELETE_IMAGE'));
        }

        if (!empty($image['filename'])) {
            Jaws_FileManagement_File::delete(ROOT_DATA_PATH . 'phoo/' . $image['filename']);
            Jaws_FileManagement_File::delete(ROOT_DATA_PATH . 'phoo/' . $this->GetMediumPath($image['filename']));
            Jaws_FileManagement_File::delete(ROOT_DATA_PATH . 'phoo/' . $this->GetThumbPath($image['filename']));
        }

        $this->gadget->session->push($this::t('PHOTO_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Add a new entry
     *
     * @access  public
     * @param   string  $user             User who is adding the photo
     * @param   array   $files            info like original name, tmp name and size
     * @param   string  $title            Title of the image
     * @param   string  $description      Description of the image
     * @param   bool    $fromControlPanel Is it called from ControlPanel?
     * @param   array   $album            Array containing the required info about the album
     * @return  mixed   Returns the ID of the new entry and Jaws_Error on error
     */
    function NewEntry($user, $files, $title, $description, $fromControlPanel = true, $album)
    {
        // check if it's really a uploaded file.
        /*if (is_uploaded_file($files['tmp_name'])) {
            $this->gadget->session->push($this::t('ERROR_CANT_UPLOAD_PHOTO'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_CANT_UPLOAD_PHOTO'));
        }*/

        if (!preg_match("/\.png$|\.jpg$|\.jpeg$|\.gif$/i", $files['name'])) {
            $this->gadget->session->push($this::t('ERROR_CANT_UPLOAD_PHOTO_EXT'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_CANT_UPLOAD_PHOTO_EXT'));
        }

        // Create directories
        $uploaddir = ROOT_DATA_PATH . 'phoo/' . date('Y_m_d') . '/';
        if (!Jaws_FileManagement_File::is_dir($uploaddir)) {
            if (!Jaws_FileManagement_File::is_writable(ROOT_DATA_PATH . 'phoo/')) {
                $this->gadget->session->push($this::t('ERROR_CANT_UPLOAD_PHOTO'), RESPONSE_ERROR);
                return new Jaws_Error($this::t('ERROR_CANT_UPLOAD_PHOTO'));
            }

            $new_dirs = array();
            $new_dirs[] = $uploaddir;
            $new_dirs[] = $uploaddir . 'thumb';
            $new_dirs[] = $uploaddir . 'medium';
            foreach ($new_dirs as $new_dir) {
                if (!Jaws_FileManagement_File::mkdir($new_dir)) {
                    $this->gadget->session->push($this::t('ERROR_CANT_UPLOAD_PHOTO'), RESPONSE_ERROR);
                    return new Jaws_Error($this::t('ERROR_CANT_UPLOAD_PHOTO'));
                }
            }
        }

        $filename = $files['name'];
        if (Jaws_FileManagement_File::file_exists($uploaddir.$files['name'])) {
            $filename = time() . '_' . $files['name'];
        }

        $res = Jaws_FileManagement_File::uploadFiles($files, $uploaddir, 'jpg,gif,png,jpeg', false, !$fromControlPanel);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage());
        } elseif (empty($res)) {
            $this->gadget->session->push(Jaws::t('ERROR_UPLOAD_4'), RESPONSE_ERROR);
            return new Jaws_Error(Jaws::t('ERROR_UPLOAD_4'));
        }
        $filename = $res[0][0]['host_filename'];
        $uploadfile = $uploaddir . $filename;

        // Resize Image
        include_once ROOT_JAWS_PATH . 'include/Jaws/Image.php';
        $objImage = Jaws_Image::factory();
        if (Jaws_Error::IsError($objImage)) {
            return Jaws_Error::raiseError($objImage->getMessage());
        }

        $thumbSize  = explode('x', $this->gadget->registry->fetch('thumbsize'));
        $mediumSize = explode('x', $this->gadget->registry->fetch('mediumsize'));

        $objImage->load($uploadfile);
        $objImage->resize($thumbSize[0], $thumbSize[1]);
        $res = $objImage->save($this->GetThumbPath($uploadfile));
        $objImage->free();
        if (Jaws_Error::IsError($res)) {
            // Return an error if image can't be resized
            $this->gadget->session->push($this::t('ERROR_CANT_RESIZE_TO_THUMB'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage());
        }

        $objImage->load($uploadfile);
        $objImage->resize($mediumSize[0], $mediumSize[1]);
        $res = $objImage->save($this->GetMediumPath($uploadfile));
        $objImage->free();
        if (Jaws_Error::IsError($res)) {
            // Return an error if image can't be resized
            $this->gadget->session->push($res->getMessage(), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_CANT_RESIZE_TO_MEDIUM'));
        }

        $data = array();
        $data['user_id']        = $user;
        $data['filename']       = date('Y_m_d').'/'.$filename;
        $data['title']          = $title;
        $data['description']    = $description;

        if ($this->gadget->registry->fetch('allow_comments') === 'true' &&
            $album['allow_comments'])
        {
            $data['allow_comments'] = true;
        } else {
            $data['allow_comments'] = false;
        }

        if ($this->gadget->registry->fetch('published') === 'true' &&
            $this->gadget->GetPermission('ManageAlbums'))
        {
            $data['published'] = true;
        } else {
            $data['published'] = false;
        }

        $jDate = Jaws_Date::getInstance();
        $createtime = Jaws_DB::getInstance()->date();
        if (function_exists('exif_read_data') &&
            (preg_match("/\.jpg$|\.jpeg$/i", $files['name'])) &&
            ($exifData = Jaws_FileManagement_File::exif_read_data($uploadfile, 1, true))
            && !empty($exifData['IFD0']['DateTime']) && $jDate->ValidDBDate($exifData['IFD0']['DateTime']))
        {
            $aux        = explode(' ', $exifData['IFD0']['DateTime']);
            $auxdate    = str_replace(':', '-', $aux[0]);
            $auxtime    = $aux[1];
            $createtime = $auxdate . ' ' . $auxtime;
        }
        $data['createtime'] = $createtime;

        $table = Jaws_ORM::getInstance()->table('phoo_image');
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('ERROR_CANT_UPLOAD_PHOTO'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_CANT_UPLOAD_PHOTO'));
        }

        // Lets remove the original if keep_original = false
        if ($this->gadget->registry->fetch('keep_original') == 'false') {
            if (!empty($data['filename'])) {
                Jaws_FileManagement_File::delete(ROOT_DATA_PATH . 'phoo/' . $data['filename']);
            }
        }

        // shout Activities event
        $saParams = array();
        $saParams['action'] = 'Photo';
        $this->gadget->event->shout('Activities', $saParams);

        $this->gadget->session->push($this::t('PHOTO_ADDED'), RESPONSE_NOTICE);
        return $result;
    }

    /**
     * Add entry to an existing album
     *
     * @access  public
     * @param   int     $id    Entry Id
     * @param   int     $album Album Id
     * @return  mixed   Returns true if entry was added without problems, Jaws_Error if not.
     */
    function AddEntryToAlbum($id, $album)
    {
        $data = array();
        $data['phoo_image_id'] = $id;
        $data['phoo_album_id'] = $album;

        $table = Jaws_ORM::getInstance()->table('phoo_image_album', '', '');
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('ERROR_CANT_ADD_ENTRY_TO_ALBUM'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_CANT_ADD_ENTRY_TO_ALBUM'));
        }

        return true;
    }

    /**
     * Set entry albums
     *
     * @access  public
     * @param   int     $id     Entry Id
     * @param   array   $albums Array with albums id's
     * @return  mixed   Returns true, or Jaws_Error on error
     */
    function SetEntryAlbums($id, $albums)
    {
        // Remove albums
        $table = Jaws_ORM::getInstance()->table('phoo_image_album');
        // FIXME: Check for error but maybe not since it always returns true, foobar stuff
        $table->delete()->where('phoo_image_id', (int)$id)->exec();

        if (is_array($albums) && !empty($albums)) {
            foreach ($albums as $album) {
                $rs = $this->AddEntryToAlbum($id, $album);
                if (Jaws_Error::IsError($rs)) {
                    return $rs;
                }
            }
        }

        $this->gadget->session->push($this::t('ALBUMS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}