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
class Phoo_Model_Albums extends Phoo_Model
{
    /**
     * Get a list of albums
     *
     * @access  public
     * @param   int|string $gid     Group Id or Fast url
     * @return  array  Returns an array of the dates of the phoo entries and Jaws_Error on error
     */
    function GetAlbumList($gid = 0)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_album');
        $table->select('phoo_album.id:integer', 'phoo_album.name', 'phoo_album.description', 'createtime');
        $table->where('published', true)->orderBy($this->GetOrderType('albums_order_type'));

        if (!empty($gid)) {
            $table->join('phoo_album_group', 'phoo_album.id', 'album', 'left');
            if (is_numeric($gid)) {
                $table->and()->where('group', $gid);
            } else {
                $table->join('phoo_group', 'phoo_group.id', 'phoo_album_group.group');
                $table->and()->where('phoo_group.fast_url', $gid);
            }
        }

        $albums = $table->fetchAll();
        if (Jaws_Error::IsError($albums)) {
            return new Jaws_Error(_t('PHOO_ERROR_ALBUMLIST'));
        }

        // Add unknown photo album to albums list
        array_push($albums, array('id' => 0,
            'name' => _t('PHOO_WITHOUT_ALBUM'),
            'description' => _t('PHOO_WITHOUT_ALBUM_DESCRIPTION'),
            'createtime' => date('Y-m-d H:i:s')));

        for ($i = 0; $i < count($albums); $i++) {
            $id = $albums[$i]['id'];
            if ($id == 0) {
                // orphan photos
                $table = Jaws_ORM::getInstance()->table('phoo_image');
                $table->select('phoo_image.filename', 'phoo_album_id');
                $table->join('phoo_image_album', 'phoo_image_album.phoo_image_id',
                    'phoo_image.id', 'left outer');
                $table->where('phoo_album_id', '', 'is null')->and();
                $table->where('phoo_image.published', true);
            } else {
                $table = Jaws_ORM::getInstance()->table('phoo_image_album');
                $table->select('phoo_image.filename');
                $table->join('phoo_image', 'phoo_image.id',
                    'phoo_image_album.phoo_image_id');
                $table->where('phoo_album_id', $id)->and();
                $table->where('phoo_image.published', true);
                $table->groupBy('phoo_image.filename');
            }
            $table->orderBy($table->random());

            $images = $table->fetchAll();
            if (!Jaws_Error::IsError($images) && !empty($images)) {
                $albums[$i]['qty']      = count($images);
                $albums[$i]['filename'] = $images[0]['filename'];
                $albums[$i]['thumb']    = $this->GetThumbPath($images[0]['filename']);
                unset($images);
            }
        }

        return $albums;
    }

    /**
     * Get info of a given album
     *
     * @access  public
     * @param   int    $id      Album Id
     * @return  mixed  The properties of an album and Jaws_Error on error
     */
    function GetAlbumInfo($id)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_album');
        $table->select('id', 'name', 'description', 'allow_comments:boolean',
            'published:boolean', 'createtime');
        $table->where('id', $id);
        return $table->fetchRow();
    }

    /**
     * Get a list of ordered albums
     * the order depends on what's passed to the function
     *
     * @access  public
     * @param   string  $by order by
     * @param   string  $direction order direction
     * @param   int     $group
     * @param   bool    $published
     * @return  mixed   A list of available albums and Jaws_Error on error
     */
    function GetAlbums($by = 'name', $direction = 'asc', $group = 0, $published = null)
    {
        $directions = array('asc', 'desc');
        $direction = strtolower($direction);
        if (!in_array($direction, $directions)) {
            $direction = 'asc';
        }

        $fields = array('id', 'name', 'description', 'createtime');
        $by = strtolower($by);
        if (!in_array($by, $fields)) {
            $by = 'name';
        }

        $table = Jaws_ORM::getInstance()->table('phoo_album');
        $table->select('phoo_album.id', 'name', 'count(phoo_image_id) as howmany',
            'published:boolean', 'createtime');
        $table->join('phoo_image_album', 'phoo_album.id', 'phoo_album_id', 'left');
        $table->groupBy('phoo_album.id', 'name', 'published', 'createtime');
        $table->orderBy("$by $direction");

        if (!empty($group) && $group != 0) {
            $table->join('phoo_album_group', 'phoo_album.id', 'album', 'left');
            $table->where('group', $group);
        }

        if (!empty($published)) {
            $table->and()->where('published', $published);
        }

        $rows = $table->fetchAll();
        if (Jaws_Error::IsError($rows)) {
            return new Jaws_Error(_t('PHOO_ERROR_ALBUMS', $by));
        }

        $ret = array();
        if (count($rows) > 0) {
            foreach ($rows as $r) {
                $r['createtime'] = $r['createtime'];
                $ret[] = $r;
            }
        }

        return $ret;
    }

    /**
     * Get number of images on a given album
     *
     * @access  public
     * @param   int     $id     ID of the album
     * @return  int     number of images on the album, 0 if album doesn't exist
     */
    function GetAlbumCount($id)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_image');
        $table->select('count(id)');
        if ($id == '0') { //UNKNOWN
            $join_type = 'left outer';
            $table->where('phoo_album_id', '', 'is null');
        } else {
            $join_type = 'inner';
            $table->where('phoo_album_id', $id);
        }
        $table->join('phoo_image_album', 'phoo_image_album.phoo_image_id',
            'phoo_image.id', $join_type);
        $res = $table->fetchOne();
        if (Jaws_Error::IsError($res)) {
            return 0;
        }

        return($res != null ? $res : 0);
    }

    /**
     * Get album pager links
     *
     * @access  public
     * @param   int     $id     ID of the album
     * @param   int     $page
     * @return  array   array with numbers of the first, previous, next and last pages
     */
    function GetAlbumPager($id, $page)
    {
        $count = $this->GetAlbumCount($id);
        $limit = $this->gadget->registry->fetch('thumbnail_limit');
        $pager = array();
        if ($limit != 0) {
            $pager['first'] = 1;
            $pager['last']  = ceil($count / $limit);
            $pager['prev']  = $page > 1 ? $page - 1 : '';
            $pager['next']  = $page < $pager['last'] ? $page + 1 : '';
        }

        return $pager;
    }

    /**
     * Get album pager numbered links
     *
     * @access  public
     * @param   int     $id     ID of the album
     * @param   int     $page   page number
     * @return  array   array with numbers of pages
     */
    function GetAlbumPagerNumbered($id, $page)
    {
        $paginator_size = 4;
        $tail = 1;
        $total = $this->GetAlbumCount($id);
        $page_size = $this->gadget->registry->fetch('thumbnail_limit');
        $pages = array();
        if ($page_size == 0) {
            return $pages;
        }

        $npages = ceil($total / $page_size);

        if ($npages < 2) {
            return $pages;
        }

        // Previous
        if ($page == 1) {
            $pages['previous'] = false;
        } else {
            $pages['previous'] = $page - 1;
        }

        if ($npages <= ($paginator_size + $tail)) {
            for ($i = 1; $i <= $npages; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }
        } elseif ($page < $paginator_size) {
            for ($i = 1; $i <= $paginator_size; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator2'] = true;

            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }

        } elseif ($page > ($npages - $paginator_size + $tail)) {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator1'] = true;

            for ($i = $npages - $paginator_size + ($tail - 1); $i <= $npages; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }
        } else {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator1'] = true;

            $start = floor(($paginator_size - $tail)/2);
            $end = ($paginator_size - $tail) - $start;
            for ($i = $page - $start; $i < $page + $end; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator2'] = true;

            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }

        }

        // Next
        if ($page == $npages) {
            $pages['next'] = false;
        } else {
            $pages['next'] = $page + 1;
        }

        $pages['total'] = $total;

        return $pages;
    }
}