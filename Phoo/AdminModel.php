<?php
require_once JAWS_PATH . 'gadgets/Phoo/Model.php';
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_AdminModel extends Phoo_Model
{
    /**
     * Update the information of an image
     *
     * @access  public
     * @param   int     $id                 ID of the image
     * @param   string  $title              Title of the image
     * @param   string  $description        Description of the image
     * @param   bool    $allow_comments     True is comments allowed, False is not allowed
     * @param   bool    $published          true for Published, false for Hidden
     * @param   array   $albums
     * @return  mixed   True if entry was updated successfully and Jaws_Error if not
     */
    function UpdateEntry($id, $title, $description, $allow_comments, $published, $albums = null)
    {
        $data = array();
        $data['title'] = $title;
        $data['description'] = $description;
        $data['allow_comments'] = $allow_comments;
        $data['updatetime'] = $GLOBALS['db']->Date();

        $table = Jaws_ORM::getInstance()->table('phoo_image');
        if (is_null($published)) {
            $data['published'] = (bool)$published;
        }
        $result = $table->update($data)->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_UPDATE_PHOTO'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_CANT_UPDATE_PHOTO'), _t('PHOO_NAME'));
        }

        if ($albums !== null) {
            $this->SetEntryAlbums($id, $albums);
        }
        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_PHOTO_UPDATED'), RESPONSE_NOTICE);
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
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_IMPOSSIBLE_DELETE_IMAGE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_IMPOSSIBLE_DELETE_IMAGE'), _t('PHOO_NAME'));
        }

        $table->reset();
        $result = $table->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_IMPOSSIBLE_DELETE_IMAGE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_IMPOSSIBLE_DELETE_IMAGE'), _t('PHOO_NAME'));
        }

        $table = Jaws_ORM::getInstance()->table('phoo_image_album');
        $result = $table->delete()->where('phoo_image_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_IMPOSSIBLE_DELETE_IMAGE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_IMPOSSIBLE_DELETE_IMAGE'), _t('PHOO_NAME'));
        }

        @unlink(JAWS_DATA . 'phoo/' . $image['filename']);
        @unlink(JAWS_DATA . 'phoo/' . $this->GetMediumPath($image['filename']));
        @unlink(JAWS_DATA . 'phoo/' . $this->GetThumbPath($image['filename']));

        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_PHOTO_DELETED'), RESPONSE_NOTICE);
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
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO'), _t('PHOO_NAME'));
        }*/

        if (!preg_match("/\.png$|\.jpg$|\.jpeg$|\.gif$/i", $files['name'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO_EXT'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO_EXT'), _t('PHOO_NAME'));
        }

        // Create directories
        $uploaddir = JAWS_DATA . 'phoo/' . date('Y_m_d') . '/';
        if (!is_dir($uploaddir)) {
            if (!Jaws_Utils::is_writable(JAWS_DATA . 'phoo/')) {
                $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO'), RESPONSE_ERROR);
                return new Jaws_Error(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO'), _t('PHOO_NAME'));
            }

            $new_dirs = array();
            $new_dirs[] = $uploaddir;
            $new_dirs[] = $uploaddir . 'thumb';
            $new_dirs[] = $uploaddir . 'medium';
            foreach ($new_dirs as $new_dir) {
                if (!Jaws_Utils::mkdir($new_dir)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO'), RESPONSE_ERROR);
                    return new Jaws_Error(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO'), _t('PHOO_NAME'));
                }
            }
        }

        $filename = $files['name'];
        if (file_exists($uploaddir.$files['name'])) {
            $filename = time() . '_' . $files['name'];
        }

        $res = Jaws_Utils::UploadFiles($files, $uploaddir, 'jpg,gif,png,jpeg', '', false, !$fromControlPanel);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), _t('PHOO_NAME'));
        }
        $filename = $res[0][0]['host_filename'];
        $uploadfile = $uploaddir . $filename;

        // Resize Image
        include_once JAWS_PATH . 'include/Jaws/Image.php';
        $objImage = Jaws_Image::factory();
        if (Jaws_Error::IsError($objImage)) {
            return Jaws_Error::raiseError($objImage->getMessage(), _t('PHOO_NAME'));
        }

        $thumbSize  = explode('x', $this->gadget->registry->fetch('thumbsize'));
        $mediumSize = explode('x', $this->gadget->registry->fetch('mediumsize'));

        $objImage->load($uploadfile);
        $objImage->resize($thumbSize[0], $thumbSize[1]);
        $res = $objImage->save($this->GetThumbPath($uploadfile));
        $objImage->free();
        if (Jaws_Error::IsError($res)) {
            // Return an error if image can't be resized
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_RESIZE_TO_THUMB'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), _t('PHOO_NAME'));
        }

        $objImage->load($uploadfile);
        $objImage->resize($mediumSize[0], $mediumSize[1]);
        $res = $objImage->save($this->GetMediumPath($uploadfile));
        $objImage->free();
        if (Jaws_Error::IsError($res)) {
            // Return an error if image can't be resized
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_CANT_RESIZE_TO_MEDIUM'), _t('PHOO_NAME'));
        }

        $data = array();
        $data['user_id'] = $user;
        $data['filename'] = date('Y_m_d').'/'.$filename;
        $data['title'] = $title;
        $data['description'] = $description;

        if ($this->gadget->registry->fetch('allow_comments') === 'true' &&
            $album['allow_comments'])
        {
            $data['allow_comments'] = true;
        } else {
            $data['allow_comments'] = false;
        }

        if ($this->gadget->registry->fetch('published') === 'true' &&
            $data['published'] === true &&
            $this->gadget->GetPermission('ManageAlbums'))
        {
            $data['published'] = true;
        } else {
            $data['published'] = false;
        }

        $jDate = $GLOBALS['app']->loadDate();
        $createtime = $GLOBALS['db']->Date();
        if (function_exists('exif_read_data') &&
            (preg_match("/\.jpg$|\.jpeg$/i", $files['name'])) &&
            ($data = @exif_read_data($uploadfile, 1, true))
            && !empty($data['IFD0']['DateTime']) && $jDate->ValidDBDate($data['IFD0']['DateTime']))
        {
            $aux        = explode(' ', $data['IFD0']['DateTime']);
            $auxdate    = str_replace(':', '-', $aux[0]);
            $auxtime    = $aux[1];
            $createtime = $auxdate . ' ' . $auxtime;
        }
        $data['createtime'] = $createtime;

        $table = Jaws_ORM::getInstance()->table('phoo_image');
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO'), _t('PHOO_NAME'));
        }

        // Lets remove the original if keep_original = false
        if ($this->gadget->registry->fetch('keep_original') == 'false') {
            @unlink(JAWS_DATA . 'phoo/' . $params['filename']);
        }

        // Get last id...
        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_PHOTO_ADDED'), RESPONSE_NOTICE);
        return $GLOBALS['db']->lastInsertID('phoo_image', 'id');
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

        $table = Jaws_ORM::getInstance()->table('phoo_image_album');
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_ADD_ENTRY_TO_ALBUM'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_CANT_ADD_ENTRY_TO_ALBUM'), _t('PHOO_NAME'));
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

        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ALBUMS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update the Album information
     *
     * @access  public
     * @param   int      $id             ID of the album
     * @param   string   $name           Name of the album
     * @param   string   $description    Description of the album
     * @param   bool     $comments       If a comments are enabled
     * @param   bool     $published      If the album is visable to users or not
     * @return  mixed    Returns true if album was updated without problems, Jaws_Error if not.
     */
    function UpdateAlbum($id, $name, $description, $comments, $published)
    {
        $data = array();
        $data['name'] = $name;
        $data['description'] = $description;
        $data['allow_comments'] = $comments;
        $data['published'] = (bool)$published;

        $table = Jaws_ORM::getInstance()->table('phoo_album');
        $result = $table->update($data)->where('idd', (int)$id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_UPDATED'), _t('PHOO_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ALBUM_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete an album and all its images
     *
     * @access  public
     * @param   int     $id     ID of the album
     * @return  mixed   Returns true if album was deleted without problems, Jaws_Error if not.
     */
    function DeleteAlbum($id)
    {
        $params       = array();
        $params['id'] = $id;

        // Delete files
        // We do it this way because we don't want to use subqueries(some versions of mysql don't support it)
        $imgList = '';
        $sql = '
            SELECT [phoo_image_id]
            FROM [[phoo_image_album]]
            WHERE [phoo_album_id] = {id}';
        $result = $GLOBALS['db']->queryAll($sql, $params);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), _t('PHOO_NAME'));
        }

        foreach ($result as $i) {
            $imgList .= $i['phoo_image_id'].',';
        }
        $imgList = substr($imgList,0,-1);

        if (empty($imgList)) {
            $imgList = '0';
        }

        $sql = "
            SELECT
                [id], [user_id], [filename], [phoo_album_id]
            FROM [[phoo_image]]
            INNER JOIN [[phoo_image_album]] ON [id] = [phoo_image_id]
            WHERE [id] IN({$imgList})
            GROUP BY
                [id], [user_id], [filename], [phoo_album_id]
            HAVING COUNT([phoo_image_id]) = 1";

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), _t('PHOO_NAME'));
        }

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        foreach ($result as $r) {
            @unlink(JAWS_DATA . 'phoo/' . $r['filename']);
            @unlink(JAWS_DATA . 'phoo/' . $this->GetMediumPath($r['filename']));
            @unlink(JAWS_DATA . 'phoo/' . $this->GetThumbPath($r['filename']));
        }

        // Delete images from phoo_image
        $sql    = "DELETE FROM [[phoo_image]] WHERE [id] IN({$imgList})";
        $result = $GLOBALS['db']->query($sql);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), _t('PHOO_NAME'));
        }

        // Delete images from phoo_image_album
        $sql    = 'DELETE FROM [[phoo_image_album]] WHERE [phoo_album_id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);

        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), _t('PHOO_NAME'));
        }

        // Delete album from phoo_album
        $sql    = 'DELETE FROM [[phoo_album]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), _t('PHOO_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ALBUM_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Create a new album
     *
     * @access  public
     * @param   string   $name        Name of the album
     * @param   string   $description Description of the album
     * @param   bool     $comments    If a comments are enabled
     * @param   bool     $published   If the album is visable to users or not
     * @return  mixed    Returns the ID of the new album and Jaws_Error on error
     */
    function NewAlbum($name, $description, $comments, $published)
    {
        $data = array();
        $data['name'] = $name;
        $data['description'] = $description;
        $data['allow_comments'] = $comments;
        $data['published'] = (bool)$published;
        $data['createtime'] = $GLOBALS['db']->Date();

        $table = Jaws_ORM::getInstance()->table('phoo_album');
        $result = $table->insert($data)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_CREATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_CREATED'), _t('PHOO_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ALBUM_CREATED'), RESPONSE_NOTICE);
        return $GLOBALS['db']->lastInsertID('phoo_album', 'id');
    }

    /**
     * Update registry settings for phoo
     *
     * @access  public
     * @param   string  $default_action
     * @param   bool    $published
     * @param   bool    $allow_comments
     * @param   string  $moblog_album
     * @param   string  $moblog_limit
     * @param   string  $photoblog_album
     * @param   string  $photoblog_limit
     * @param   bool    $show_exif_info
     * @param   bool    $keep_original
     * @param   string  $thumb_limit
     * @param   string  $comment_status
     * @param   string  $albums_order_type
     * @param   string  $photos_order_type
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function SaveSettings($default_action, $published, $allow_comments, $moblog_album, $moblog_limit,
                          $photoblog_album, $photoblog_limit, $show_exif_info, $keep_original, $thumb_limit,
                          $comment_status, $albums_order_type, $photos_order_type)
    {
        $rs = array();
        $rs[] = $this->gadget->registry->update('default_action',    $default_action);
        $rs[] = $this->gadget->registry->update('published',         $published);
        $rs[] = $this->gadget->registry->update('allow_comments',    $allow_comments);
        $rs[] = $this->gadget->registry->update('moblog_album',      $moblog_album);
        $rs[] = $this->gadget->registry->update('moblog_limit',      $moblog_limit);
        $rs[] = $this->gadget->registry->update('photoblog_album',   $photoblog_album);
        $rs[] = $this->gadget->registry->update('photoblog_limit',   $photoblog_limit);
        $rs[] = $this->gadget->registry->update('show_exif_info',    $show_exif_info);
        $rs[] = $this->gadget->registry->update('keep_original',     $keep_original);
        $rs[] = $this->gadget->registry->update('thumbnail_limit',   $thumb_limit);
        $rs[] = $this->gadget->registry->update('comment_status',    $comment_status);
        $rs[] = $this->gadget->registry->update('albums_order_type', $albums_order_type);
        $rs[] = $this->gadget->registry->update('photos_order_type', $photos_order_type);

        foreach ($rs as $r) {
            if (Jaws_Error::IsError($r) || $r === false) {
                $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_UPDATE_SETTINGS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('PHOO_ERROR_CANT_UPDATE_SETTINGS'), _t('PHOO_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_SETTINGS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Items to import, looking in 'data/phoo/import' folder
     * 
     * @return  array    Items to import
     */
    function GetItemsToImport()
    {
        $items = array();
        $path =  JAWS_DATA . 'phoo/import';
        if (is_dir($path)) {
            ///FIXME use scandir
            $d = dir($path);
            while (false !== ($file = $d->read())) {
                if (!is_dir($file) && (preg_match("/\.png$|\.jpg$|\.jpeg$|\.gif$/i", $file)))
                {
                    $items[] = $file;
                }
            }
            $d->close();
            return $items;
        }

        return array();
    }

    /**
     * Check if input (an array of $_FILES) are .tar or .zip files, if they
     * are then these get unpacked and returns an managed as $_FILES (returning
     * an array with the same structure $_FILES uses and move pics to /tmp)
     *
     * @access  public
     * @param   array   $files   $_FILES
     * @return  array   $_FILES format
     */
    function UnpackFiles($files)
    {
        if (!is_array($files)) {
            return array();
        }

        $cleanFiles = array();
        $tmpDir     = sys_get_temp_dir();
        $counter    = 1;
        require_once PEAR_PATH. 'File/Archive.php';
        foreach($files as $key => $file) {
            if (empty($file['tmp_name'])) {
                continue;
            }
            $ext = end(explode('.', $file['name']));
            if (File_Archive::isKnownExtension($ext)) {
                $tmpArchiveName = $tmpDir . DIRECTORY_SEPARATOR . $file['name'];
                if (!move_uploaded_file($file['tmp_name'], $tmpArchiveName)) {
                    continue;
                }
                $source = File_Archive::readArchive($ext, File_Archive::read($tmpArchiveName));
                if (!PEAR::isError($source)) {
                    while ($source->next()) {
                        $destFile   = $tmpDir . DIRECTORY_SEPARATOR . basename($source->getFilename());
                        $sourceFile = $tmpArchiveName . '/' . $source->getFilename();
                        $extract    = File_Archive::extract($sourceFile, $tmpDir);
                        if (PEAR::IsError($extract)) {
                            continue;
                        }
                        $cleanFiles['photo'.$counter] = array('name'     => basename($source->getFilename()),
                                                              'type'     => $source->getMime(),
                                                              'tmp_name' => $destFile,
                                                              'size'     => filesize($destFile),
                                                              'error'    => 0,
                                                              );
                        $counter++;
                    }
                }
            } else {
                $cleanFiles['photo'.$counter] = $file;
                $counter++;
            }
        }
        return $cleanFiles;
    }

    /**
     * Update an image comments count
     *
     * @access  public
     * @param   int     $id              Image id.
     * @param   int     $commentCount    How Many comment?
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function UpdateImageCommentsCount($id, $commentCount)
    {
        $phooTable = Jaws_ORM::getInstance()->table('phoo_image');
        return $phooTable->update(array('comments'=>$commentCount))->where('id', $id)->exec();
    }

}
