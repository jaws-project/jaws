<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Phoo/Model.php';

class PhooAdminModel extends PhooModel
{
    /**
     * Install Phoo gadget in Jaws
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'phoo' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('PHOO_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/default_action',    'AlbumList');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/thumbsize',         '133x100');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/mediumsize',        '400x300');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/moblog_album',      '');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/moblog_limit',      '10');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/photoblog_album',   '');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/photoblog_limit',   '5');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/allow_comments',    'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/published',         'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/plugabble',         'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/show_exif_info',    'false');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/keep_original',     'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/thumbnail_limit',   '0');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/use_antispam',      'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/comment_status',    'approved');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/albums_order_type', 'name');
        $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/photos_order_type', 'id');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UninstallGadget()
    {
        $tables = array('phoo_album',
                        'phoo_image',
                        'phoo_image_album');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('PHOO_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/default_action');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/thumbsize');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/mediumsize');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/moblog_album');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/moblog_limit');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/photoblog_album');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/photoblog_limit');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/allow_comments');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/published');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/plugabble');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/show_exif_info');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/keep_original');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/thumbnail_limit');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/use_antispam');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/comment_status');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/albums_order_type');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/photos_order_type');

        // Recent comments
        require_once JAWS_PATH.'include/Jaws/Comment.php';
        $api = new Jaws_Comment($this->_Name);
        $api->DeleteCommentsOfGadget();

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error onFailure
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.8.0', '<')) {
            $result = $this->installSchema('0.8.0.xml', '', "$old.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/image_quality', '75');
        }

        if ($old == '0.7.0') {
            // Update allow_comments and status in all albums.
            $params = array('published' => true, 'allow_comments' => true);
            $sql = "UPDATE [[phoo_album]] SET [published] = {published}, [allow_comments] = {allow_comments}";
            $result   = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_QUERY_FILE', '(Update phoo_album SET published = true, allow_comments = true)'),
                                     _t('PHOO_NAME'));
            }

            $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/comment_status', 'approved');
            $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/order_type','name');
        }

        if (version_compare($old, '0.8.1', '<')) {
            $albums_order_type = $this->GetRegistry('order_type');
            $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/albums_order_type',
                                              Jaws_Error::IsError($albums_order_type)? 'name' : $albums_order_type);
            $GLOBALS['app']->Registry->NewKey('/gadgets/Phoo/photos_order_type', 'id');
            $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/order_type');
        }

        if (version_compare($old, '0.8.2', '<')) {
            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/Phoo/ManagePhotos',  'false');
        }

        if (version_compare($old, '0.8.3', '<')) {
            $base_path = $GLOBALS['app']->getDataURL() . 'phoo/';
            $sql = '
                SELECT [id], [filename]
                FROM [[phoo_image]]';
            $photos = $GLOBALS['db']->queryAll($sql);
            if (!Jaws_Error::IsError($photos)) {
                foreach ($photos as $photo) {
                    if (!empty($photo['filename'])) {
                        if (strpos($photo['filename'], $base_path) !== 0) {
                            continue;
                        }
                        $photo['filename'] = substr($photo['filename'], strlen($base_path));
                        $sql = '
                            UPDATE [[phoo_image]] SET
                                [filename] = {filename}
                            WHERE [id] = {id}';
                        $res = $GLOBALS['db']->query($sql, $photo);
                    }
                }
            }
        }

        if (version_compare($old, '0.8.4', '<')) {
            $result = $this->installSchema('schema.xml', '', "0.8.0.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/resize_method');
            $GLOBALS['app']->Registry->DeleteKey('/gadgets/Phoo/image_quality');
        }

        return true;
    }

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
        $params = array();
        $params['id']             = (int)$id;
        $params['title']          = $title;
        $params['desc']           = $description;
        $params['allow_comments'] = $allow_comments;
        $params['published']      = $published;
        $params['update']         = $GLOBALS['db']->Date();

        if (is_null($published)) {
            $sql = '
                UPDATE [[phoo_image]] SET
                    [title]          = {title},
                    [description]    = {desc},
                    [allow_comments] = {allow_comments},
                    [updatetime]     = {update}
                WHERE [id] = {id}';
        } else {
            $sql = '
                UPDATE [[phoo_image]] SET
                    [title]          = {title},
                    [description]    = {desc},
                    [allow_comments] = {allow_comments},
                    [published]      = {published},
                    [updatetime]     = {update}
                WHERE [id] = {id}';
        }

        $res  = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
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
        $params       = array();
        $params['id'] = $id;

        $sql = 'SELECT [filename] FROM [[phoo_image]] WHERE [id] = {id}';
        $image = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($image)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_IMPOSSIBLE_DELETE_IMAGE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_IMPOSSIBLE_DELETE_IMAGE'), _t('PHOO_NAME'));
        }

        $sql = 'DELETE FROM [[phoo_image]] WHERE [id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_IMPOSSIBLE_DELETE_IMAGE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_IMPOSSIBLE_DELETE_IMAGE'), _t('PHOO_NAME'));
        }

        $sql = 'DELETE FROM [[phoo_image_album]] WHERE [phoo_image_id] = {id}';
        $result = $GLOBALS['db']->query($sql, $params);
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

        $thumbSize  = explode('x', $this->GetRegistry('thumbsize'));
        $mediumSize = explode('x', $this->GetRegistry('mediumsize'));

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

        $params['user_id']     = $user;
        $params['filename']    = date('Y_m_d').'/'.$filename;
        $params['title']       = $title;
        $params['description'] = $description;

        if ($this->GetRegistry('allow_comments') == 'true' &&
            $album['allow_comments'])
        {
            $params['allow_comments'] = true;
        } else {
            $params['allow_comments'] = false;
        }

        if ($this->GetRegistry('published') == 'true' &&
            $album['published'] === true &&
            $GLOBALS['app']->Session->GetPermission('Phoo', 'ManageAlbums'))
        {
            $params['published'] = true;
        } else {
            $params['published'] = false;
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
        $params['createtime'] = $createtime;

        $sql = '
            INSERT INTO [[phoo_image]]
                ([user_id], [filename], [title], [description], [allow_comments], [published], [createtime])
            VALUES
                ({user_id}, {filename}, {title}, {description}, {allow_comments}, {published}, {createtime})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_CANT_UPLOAD_PHOTO'), _t('PHOO_NAME'));
        }

        // Lets remove the original if keep_original = false
        if ($this->GetRegistry('keep_original') == 'false') {
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
        $params          = array();
        $params['id']    = $id;
        $params['album'] = $album;

        $sql = '
            INSERT INTO [[phoo_image_album]]
                ([phoo_image_id], [phoo_album_id])
            VALUES
                ({id}, {album})';

        $result = $GLOBALS['db']->query($sql, $params);
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
        $params           = array();
        $params['id']     = $id;

        // Remove albums
        $sql = 'DELETE FROM [[phoo_image_album]] WHERE [phoo_image_id] = {id}';
        ///FIXME: Check for error but maybe not since it always returns true, foobar stuff
        $GLOBALS['db']->query($sql, $params);

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
        $params = array();
        $params['id']          = (int)$id;
        $params['name']        = $name;
        $params['description'] = $description;
        $params['comments']    = $comments;
        $params['published']   = $published;

        if (!is_bool($params['published'])) {
            $params['published'] = $params['published'] == '1' ? true : false;
        }

        $sql = '
            UPDATE [[phoo_album]] SET
                [name] = {name},
                [description] = {description},
                [allow_comments] = {comments},
                [published] = {published}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
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
        $params = array();
        $params['album']       = $name;
        $params['description'] = $description;
        $params['comments']    = $comments;
        $params['published']   = $published;
        $params['now']         = $GLOBALS['db']->Date();

        if (!is_bool($params['published'])) {
            $params['published'] = $params['published'] == '1' ? true : false;
        }

        $sql = '
            INSERT INTO [[phoo_album]]
                ([name], [description], [allow_comments], [published], [createtime])
            VALUES
                ({album}, {description}, {comments}, {published}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
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
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/default_action',    $default_action);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/published',         $published);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/allow_comments',    $allow_comments);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/moblog_album',      $moblog_album);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/moblog_limit',      $moblog_limit);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/photoblog_album',   $photoblog_album);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/photoblog_limit',   $photoblog_limit);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/show_exif_info',    $show_exif_info);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/keep_original',     $keep_original);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/thumbnail_limit',   $thumb_limit);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/comment_status',    $comment_status);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/albums_order_type', $albums_order_type);
        $rs[] = $GLOBALS['app']->Registry->Set('/gadgets/Phoo/photos_order_type', $photos_order_type);

        foreach ($rs as $r) {
            if (Jaws_Error::IsError($r) || $r === false) {
                $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_UPDATE_SETTINGS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('PHOO_ERROR_CANT_UPDATE_SETTINGS'), _t('PHOO_NAME'));
            }
        }

        $GLOBALS['app']->Registry->Commit('Phoo');
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
     * Mark as different status a comment
     *
     * @access  public
     * @param   array  $ids     Id's of the comments to mark as spam
     * @param   string $status  New status (spam by default)
     * @return  bool    True always
     */
    function MarkCommentsAs($ids, $status = 'spam')
    {
        if (count($ids) == 0 || empty($status)) {
            return true;
        }

        require_once JAWS_PATH.'include/Jaws/Comment.php';
        $api = new Jaws_Comment($this->_Name);
        $api->MarkAs($ids, $status);
        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_COMMENT_MARKED'), RESPONSE_NOTICE);
        return true;
    }


    /**
     * Does a massive comment delete
     *
     * @access  public
     * @param   array   $ids  Ids of comments
     * @return  mixed   True on Success and Jaws_Error on Failure
     */
    function MassiveCommentDelete($ids)
    {
        if (!is_array($ids)) {
            $ids = func_get_args();
        }

        foreach($ids as $id) {
            $res = $this->DeleteComment($id);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_COMMENT_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('PHOO_ERROR_COMMENT_NOT_DELETED'), _t('BLOG_NAME'));
            }
        }

        return true;
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

}