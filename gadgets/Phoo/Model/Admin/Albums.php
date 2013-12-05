<?php
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
class Phoo_Model_Admin_Albums extends Phoo_Model
{
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
        $result = $table->update($data)->where('id', (int)$id)->exec();
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
        // Delete files
        // We do it this way because we don't want to use subqueries(some versions of mysql don't support it)
        $table = Jaws_ORM::getInstance()->table('phoo_image_album');
        $table->select('phoo_image_id');
        $table->where('phoo_album_id', $id);
        $result = $table->fetchAll();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), _t('PHOO_NAME'));
        }

        $imgList = array();
        foreach ($result as $i) {
            $imgList[] = $i['phoo_image_id'];
        }

        $table = Jaws_ORM::getInstance()->table('phoo_image');
        $table->select('id', 'user_id', 'filename', 'phoo_album_id');
        $table->join('phoo_image_album', 'id', 'phoo_image_id');
        if (!empty($imgList)) {
            $table->where('id', $imgList, 'in');
        }
        $table->groupBy('id', 'user_id', 'filename', 'phoo_album_id');
        $table->having('count(phoo_image_id)', 1);
        $result = $table->fetchAll();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), _t('PHOO_NAME'));
        }

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        foreach ($result as $r) {
            Jaws_Utils::delete(JAWS_DATA . 'phoo/' . $r['filename']);
            Jaws_Utils::delete(JAWS_DATA . 'phoo/' . $this->GetMediumPath($r['filename']));
            Jaws_Utils::delete(JAWS_DATA . 'phoo/' . $this->GetThumbPath($r['filename']));
        }

        // Delete images from phoo_image
        if (!empty($imgList)) {
            $table = Jaws_ORM::getInstance()->table('phoo_image');
            $table->delete()->where('id', $imgList, 'in');
            $result = $table->exec();
        }
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), _t('PHOO_NAME'));
        }

        // Delete images from phoo_image_album
        $table = Jaws_ORM::getInstance()->table('phoo_image_album');
        $table->delete()->where('phoo_album_id', $id);
        $result = $table->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('PHOO_ERROR_ALBUM_NOT_DELETED'), _t('PHOO_NAME'));
        }

        // Delete album from phoo_album
        $table = Jaws_ORM::getInstance()->table('phoo_album');
        $table->delete()->where('id', $id);
        $result = $table->exec();
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

}