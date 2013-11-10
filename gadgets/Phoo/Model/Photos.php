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
class Phoo_Model_Photos extends Phoo_Model
{
    /**
     * Get the max date from phoo_image
     *
     * @access  public
     * @return  mixed   Date formatted as MM/DD/YYYY or False on error
     */
    function getMaxDate()
    {
        $table = Jaws_ORM::getInstance()->table('phoo_image');
        $max = $table->select('max(createtime)')->fetchOne();
        if (Jaws_Error::IsError($max)) {
            return false;
        }

        $objDate = Jaws_Date::getInstance();
        return $objDate->Format($max, 'm/d/Y');
    }

    /**
     * Get the min date from phoo_image
     *
     * @access  public
     * @return  mixed    Date formatted as MM/DD/YYYY or false on error
     */
    function GetMinDate()
    {
        $table = Jaws_ORM::getInstance()->table('phoo_image');
        $min = $table->select('min(createtime)')->fetchOne();
        if (Jaws_Error::IsError($min)) {
            return false;
        }

        $objDate = Jaws_Date::getInstance();
        return $objDate->Format($min, 'm/d/Y');
    }

    /**
     * Convert bytes to a nice size format
     *
     * @access  public
     * @param   string  $size   Bytes
     * @return  string  The size with its unit prefix
     */
    function NiceSize($size)
    {
        $prefixes = array('bytes', 'Kb', 'Mb', 'Gb', 'Tb');
        $i = 0;
        while ($size >= 1024) {
            $size = $size/1024;
            $i++;
        }
        $size = round($size, 2);
        return $size.' '.$prefixes[$i];
    }

    /**
     * Get a paged thumbnail of a given album
     *
     * @access  public
     * @param   int    $id      ID of the album
     * @param   int    $page    number of the page to show
     * @param   int    $day     Optional, get only photos in this day/month/year plus 30 days
     * @param   int    $month   Optional, get only photos in this month/year plus 30 days
     * @param   int    $year    Optional, get only photos in this month/year plus 30 days
     * @return  mixed  Returns an array with some phoo entries of a certain
     *                 album and Jaws_Error on error.
     */
    function GetAlbumImages($id, $page = null, $day = null, $month = null, $year = null)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_album');
        $table->select('id', 'name', 'description', 'createtime', 'published:boolean');
        $r = $table->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($r)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETALBUM'), _t('PHOO_NAME'));
        }

        // The album does not exist or is hidden
        if ($id != '0' && empty($r)) {
            return array();
        }

        $album = array();
        if ($id == '0') { //UNKNOWN
            $album['id']          = '0';
            $album['name']        = _t('PHOO_WITHOUT_ALBUM');
            $album['description'] = _t('PHOO_WITHOUT_ALBUM_DESCRIPTION');
            $album['createtime']  = date('Y-m-d H:i:s');
            $album['published']   = true;
        } else {
            $album['id']          = $r['id'];
            $album['name']        = $r['name'];
            $album['description'] = $r['description'];
            $album['createtime']  = $r['createtime'];
            $album['published']   = $r['published'];
        }

        if ($id == '0') { //UNKNOWN
            $table = Jaws_ORM::getInstance()->table('phoo_image');
            $table->join('phoo_image_album', 'phoo_image_album.phoo_image_id',
                'phoo_image.id', 'left outer');
            $table->where('phoo_album_id', '', 'is null');
        } else {
            $table = Jaws_ORM::getInstance()->table('phoo_image_album');
            $table->join('phoo_image', 'phoo_image.id',
                'phoo_image_album.phoo_image_id');
            $table->where('phoo_album_id', $id);
            if (checkdate($month, $day, $year)) {
                if (strlen($day) == 1) {
                    $day = '0'.$day;
                }
                if (strlen($month) == 1) {
                    $month = '0'.$month;
                }
                $start = $year.'-'.$month.'-'.$day;
                $end = date('Y-m-d', mktime(0, 0, 0, $month, $day + 30, $year));
                $table->and()->where('phoo_image.createtime', array($start, $end), 'between');
            }
        }
        $table->select('phoo_image.id', 'phoo_album_id', 'filename',
            'phoo_image.title', 'phoo_image.description', 'published:boolean');
        $table->orderBy('phoo_image.' . $this->GetOrderType('photos_order_type'));

        $limit = $this->gadget->registry->fetch('thumbnail_limit');
        if (!empty($page) && !empty($limit)) {
            $table->limit($limit, ($page - 1) * $limit);
        }

        $r2 = $table->fetchAll();
        if (Jaws_Error::IsError($r2)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETALBUM'), _t('PHOO_NAME'));
        }

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        foreach ($r2 as $row) {
            $info = array();

            if ($id == '0') { //UNKNOWN
                $info['albumid'] = '0';
            } else {
                $info['albumid'] = $r['id'];
            }

            $info['id']          = $row['id'];
            $info['thumb']       = $this->GetThumbPath($row['filename']);
            $info['medium']      = $this->GetMediumPath($row['filename']);
            $info['image']       = $this->GetOriginalPath($row['filename']);
            $info['name']        = $row['title'];
            $info['filename']    = $row['filename'];
            $info['description'] = $row['description'];
            $info['published']   = $row['published'];
            $info['stripped_description'] = strip_tags($row['description']);

            $album['images'][]   = $info;
        }

        return $album;
    }


    /**
     * Get information of a given image
     *
     * @access  public
     * @param   int     $id         ID of the image
     * @param   int     $album_id   ID of the album
     * @return  mixed   Returns an array with the information of an image and Jaws_Error on error
     */
    function GetImage($id, $album_id)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_image');
        $table->select(
            'phoo_image.id',
            'phoo_image.title',
            'phoo_image.description',
            'users.nickname',
            'phoo_image.filename',
            'phoo_image.published:boolean',
            'phoo_image.allow_comments',
            'phoo_album.allow_comments as album_allow_comments');
        $table->join('phoo_image_album', 'phoo_image_album.phoo_image_id', 'phoo_image.id', 'left');
        $table->join('phoo_album', 'phoo_album.id', 'phoo_image_album.phoo_album_id', 'left');
        $table->join('users', 'phoo_image.user_id', 'users.id', 'left');
        $table->where('phoo_image.id', $id)->and();
        $table->where('phoo_image.published', true);
        if ($album_id != '0') {  //UNKNOWN
            $table->and()->where('phoo_album.published', true);
        }

        $r = $table->fetchRow();
        if (Jaws_Error::IsError($r)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETIMAGE'), _t('PHOO_NAME'));
        }

        // image does not exist or is hidden
        if ($album_id != '0' && empty($r)) {
            return array();
        }

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        $image = array();
        $image['id']             = $r['id'];
        $image['name']           = $r['title'];
        $image['albumid']        = $album_id;
        $image['description']    = $r['description'];
        $image['filename']       = $r['filename'];
        $image['medium']         = $this->GetMediumPath($r['filename']);
        $image['image']          = $this->GetOriginalPath($r['filename']);
        $image['author']         = $r['nickname'];
        $image['published']      = $r['published'];
        $image['allow_comments'] = $r['allow_comments'];
        $image['album_allow_comments'] = $r['album_allow_comments'];
        $image['stripped_description'] = strip_tags($r['description']);

        // create an array with the gallery elements to find previous and next images
        if ($album_id != '0') {  //UNKNOWN
            $table = Jaws_ORM::getInstance()->table('phoo_image_album');
            $table->select('id');
            $table->join('phoo_image', 'phoo_image.id',
                'phoo_image_album.phoo_image_id');
            $table->where('phoo_album_id', $album_id)->and()
                ->where('phoo_image.published', true);
        } else {
            $table = Jaws_ORM::getInstance()->table('phoo_image');
            $table->select('id');
            $table->join('phoo_image_album', 'phoo_image_album.phoo_image_id',
                'phoo_image.id', 'left outer');
            $table->where('phoo_album_id', '', 'is null')->and()
                ->where('phoo_image.published', true);
        }
        $table->orderBy('phoo_image.' . $this->GetOrderType('photos_order_type'));

        $items = $table->fetchColumn();
        if (Jaws_Error::IsError($items)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETIMAGE'), _t('PHOO_NAME'));
        }

        $image['first']    = 0;
        $image['last']     = 0;
        $image['previous'] = 0;
        $image['next']     = 0;
        $image['pos']      = 0;
        $image['total']    = 0;
        foreach ($items as $row) {
            $image['total']++;
            $image['first'] = $items[0];

            // find previous and next elements
            $current = array_search($id, $items);
            if ($current > 0) {
                $previous = $items[$current - 1];
            }

            if ($current < array_search(end($items), $items)) {
                $next = $items[$current + 1];
            }

            $image['last']     = $items[count($items) - 1];
            $image['previous'] = !empty($previous) ? $previous : 0;
            $image['next']     = !empty($next)     ? $next     : 0;
            if($image['id'] == $row) {
                $image['pos'] = $image['total'];
            }
        }

        // EXIF STUFF
        $show = $this->gadget->registry->fetch('show_exif_info');
        if ($show == 'true' && function_exists('exif_read_data')) {
            if ($data = @exif_read_data(JAWS_DATA . 'phoo/' . $r['filename'], 1, true)) {
                $cameraimg = '';
                if (isset($data['IFD0']['Make'])) {
                    $camera = $data['IFD0']['Make'].' / '.$data['IFD0']['Model'];
                    $image['exif']['camera'] = $camera;
                    $cameraimg = 'gadgets/Phoo/Resources/images/'.str_replace(' ','',$data['IFD0']['Make']).'_'.
                        str_replace(' ', '', $data['IFD0']['Model']).'.jpg';
                    $image['exif']['cameraimg'] = $cameraimg;
                }

                if (!file_exists($cameraimg)) {
                    $image['exif']['cameraimg'] = 'gadgets/Phoo/Resources/images/Camera.png';
                }

                if (!empty($data['COMPUTED']['Width'])) {
                    $image['exif']['width'] = $data['COMPUTED']['Width'];
                    $image['exif']['height'] = $data['COMPUTED']['Height'];
                }

                if (!empty($data['FILE']['FileSize'])) {
                    $image['exif']['filesize'] = $this->NiceSize($data['FILE']['FileSize']);
                }
                if (!empty($data['IFD0']['DateTime'])) {
                    $aux = explode(' ', $data['IFD0']['DateTime']);
                    $auxdate = str_replace(':', '-', $aux[0]);
                    $auxtime = $aux[1];
                    $image['exif']['datetime'] = $auxdate.' '.$auxtime;
                }
                if (!empty($data['COMPUTED']['ApertureFNumber'])) {
                    $image['exif']['aperture'] = $data['COMPUTED']['ApertureFNumber'];
                }
                if (!empty($data['EXIF']['ExposureTime'])) {
                    $image['exif']['exposure'] = $data['EXIF']['ExposureTime'].' Sec';
                }
                if (!empty($data['EXIF']['FocalLength'])) {
                    $image['exif']['focallength'] = $data['EXIF']['FocalLength'].' mm.';
                }
            }
        }

        return $image;
    }

    /**
     * Get an image entry
     *
     * @access  public
     * @param   int     $id     ID of the image
     * @return  mixed   Returns an array with the image entry information and Jaws_Error on error
     */
    function GetImageEntry($id)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_image');
        $table->select(
            'phoo_image.id',
            'filename',
            'phoo_image.description',
            'title',
            'allow_comments:boolean',
            'published:boolean',
            'phoo_image_album.phoo_album_id');
        $table->join('phoo_image_album', 'phoo_image.id',
            'phoo_image_album.phoo_image_id', 'left');
        $table->where('phoo_image.id', $id);
        $rs = $table->fetchAll();
        if (Jaws_Error::IsError($rs)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETIMAGEENTRY'), _t('PHOO_NAME'));
        }

        $entry = array();
        foreach ($rs as $i) {
            if (empty($entry)) {
                $entry['id']             = $i['id'];
                $entry['thumb']          = Phoo_Model::GetThumbPath($i['filename']);
                $entry['medium']         = Phoo_Model::GetMediumPath($i['filename']);
                $entry['image']          = Phoo_Model::GetOriginalPath($i['filename']);
                $entry['description']    = $i['description'];
                $entry['title']          = $i['title'];
                $entry['allow_comments'] = $i['allow_comments'];
                $entry['published']      = $i['published'];
            }

            if (empty($entry['albums']) || !in_array($i['phoo_album_id'], $entry['albums'])) {
                $entry['albums'][] = $i['phoo_album_id'];
            }
        }

        return $entry;
    }

}