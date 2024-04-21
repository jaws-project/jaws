<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright   2004-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Admin_Albums extends Phoo_Model_Common
{
    /**
     * Update the Album information
     *
     * @access  public
     * @param   int      $id                ID of the album
     * @param   string   $name              Name of the album
     * @param   string   $description       Description of the album
     * @param   bool     $comments          If a comments are enabled
     * @param   bool     $published         If the album is visable to users or not
     * @param   string   $meta_keywords     Meta keywords
     * @param   string   $meta_description  Meta description
     * @return  mixed    Returns true if album was updated without problems, Jaws_Error if not.
     */
    function UpdateAlbum($id, $name, $description, $comments, $published, $meta_keywords, $meta_description)
    {
        $data = array();
        $data['name'] = $name;
        $data['description'] = $description;
        $data['meta_keywords'] = $meta_keywords;
        $data['meta_description'] = $meta_description;
        $data['allow_comments'] = $comments;
        $data['published'] = (bool)$published;

        $table = Jaws_ORM::getInstance()->table('phoo_album');
        $result = $table->update($data)->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('ERROR_ALBUM_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_ALBUM_NOT_UPDATED'));
        }

        $this->gadget->session->push($this::t('ALBUM_UPDATED'), RESPONSE_NOTICE);
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
            $this->gadget->session->push($this::t('ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_ALBUM_NOT_DELETED'));
        }

        if (!empty($result)) {
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
                $this->gadget->session->push($this::t('ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error($this::t('ERROR_ALBUM_NOT_DELETED'));
            }

            include_once ROOT_JAWS_PATH . 'include/Jaws/Image.php';
            foreach ($result as $r) {
                if (!empty($r['filename'])) {
                    Jaws_FileManagement_File::delete(ROOT_DATA_PATH . 'phoo/' . $r['filename']);
                    Jaws_FileManagement_File::delete(ROOT_DATA_PATH . 'phoo/' . $this->GetMediumPath($r['filename']));
                    Jaws_FileManagement_File::delete(ROOT_DATA_PATH . 'phoo/' . $this->GetThumbPath($r['filename']));
                }
            }

            // Delete images from phoo_image
            if (!empty($imgList)) {
                $table = Jaws_ORM::getInstance()->table('phoo_image');
                $table->delete()->where('id', $imgList, 'in');
                $result = $table->exec();
            }
            if (Jaws_Error::IsError($result)) {
                $this->gadget->session->push($this::t('ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error($this::t('ERROR_ALBUM_NOT_DELETED'));
            }

            // Delete images from phoo_image_album
            $table = Jaws_ORM::getInstance()->table('phoo_image_album');
            $table->delete()->where('phoo_album_id', $id);
            $result = $table->exec();
            if (Jaws_Error::IsError($result)) {
                $this->gadget->session->push($this::t('ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error($this::t('ERROR_ALBUM_NOT_DELETED'));
            }
        }

        // Delete album from phoo_album
        $table = Jaws_ORM::getInstance()->table('phoo_album');
        $table->delete()->where('id', $id);
        $result = $table->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('ERROR_ALBUM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_ALBUM_NOT_DELETED'));
        }

        $this->gadget->session->push($this::t('ALBUM_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Create a new album
     *
     * @access  public
     * @param   string   $name              Name of the album
     * @param   string   $description       Description of the album
     * @param   bool     $comments          If a comments are enabled
     * @param   bool     $published         If the album is visable to users or not
     * @param   string   $meta_keywords     Meta keywords
     * @param   string   $meta_description  Meta description
     * @return  mixed    Returns the ID of the new album and Jaws_Error on error
     */
    function NewAlbum($name, $description, $comments, $published, $meta_keywords, $meta_description)
    {
        $data = array();
        $data['name'] = $name;
        $data['description'] = $description;
        $data['meta_keywords'] = $meta_keywords;
        $data['meta_description'] = $meta_description;
        $data['allow_comments'] = $comments;
        $data['published'] = (bool)$published;
        $data['createtime'] = Jaws_DB::getInstance()->date();

        $table = Jaws_ORM::getInstance()->table('phoo_album');
        $id = $table->insert($data)->exec();
        if (Jaws_Error::IsError($id)) {
            $this->gadget->session->push($this::t('ERROR_ALBUM_NOT_CREATED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_ALBUM_NOT_CREATED'));
        }

        $this->gadget->session->push($this::t('ALBUM_CREATED'), RESPONSE_NOTICE);
        return $id;
    }

}