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
class PhooModel extends Jaws_Gadget_Model
{
    /**
     * Get the thumbnail thumb path of a given filename
     *
     * @access  public
     * @param   string  $file   Name of the file
     * @return  string  The ThumbPath
     */
    function GetThumbPath($file)
    {
        $path = substr($file, 0, strrpos($file, '/'));
        return $path . '/thumb/' . basename($file);
    }

    /**
     * Get the medium path of a given filename
     *
     * @access  public
     * @param   string  $file   Name of the file
     * @return  string  The MediumPath
     */
    function GetMediumPath($file)
    {
        $path = substr($file, 0, strrpos($file, '/'));
        return $path . '/medium/' . basename($file);
    }

    /**
     * Get the original path of a given filename
     *
     * @access  public
     * @param   string  $file   Name of the file
     * @return  string  The original path
     */
    function GetOriginalPath($file)
    {
        $path = substr($file, 0, strrpos($file, '/'));
        return $path . '/' . basename($file);
    }

    /**
     * Get the max date from phoo_image
     *
     * @access  public
     * @return  mixed   Date formatted as MM/DD/YYYY or False on error
     */
    function getMaxDate()
    {
        $sql = 'SELECT MAX([createtime]) FROM [[phoo_image]]';
        $max = $GLOBALS['db']->queryOne($sql);
        if (Jaws_Error::IsError($max)) {
            return false;
        }

        $objDate = $GLOBALS['app']->loadDate();
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
        $sql = 'SELECT MIN([createtime]) FROM [[phoo_image]]';
        $min = $GLOBALS['db']->queryOne($sql);
        if (Jaws_Error::IsError($min)) {
            return false;
        }

        $objDate = $GLOBALS['app']->loadDate();
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
     * Get the correct order type
     *
     * @access  private
     * @param   string  $resource
     * @return  string   The correct (or default) order type
     */
    function GetOrderType($resource)
    {
        $orderType = $this->gadget->GetRegistry($resource);
        if ($resource == 'photos_order_type') {
            if (!in_array($orderType, array('createtime DESC', 'createtime', 'title DESC', 'title', 'id DESC','id' )))
            {
                $orderType = 'title';
            }
        } else {
            if (!in_array($orderType, array('createtime DESC', 'createtime', 'name DESC', 'name', 'id DESC', 'id' )))
            {
                $orderType = 'name';
            }
        }

        if (strpos($orderType,'DESC')) {
                $orderType = '['. trim(substr($orderType, 0, strpos($orderType,'DESC'))). '] DESC';
        } else {
                $orderType = '['.$orderType.']';
        }

        return $orderType;
    }

    /**
     * Do an advanced search
     *
     * @access  public
     * @param   string  $date     Entry date
     * @param   string  $album    Album ID
     * @param   string  $words    Words to search
     * @return  mixed   Get an array of phoo entries that matches a pattern and Jaws_Error on error
     */
    function AdvancedSearch($date, $album, $words = '')
    {
        $params          = array();
        $params['date']  = $date;
        $params['album'] = $album;
        $params['words'] = $words;

        ///FIXME: Add words support
        $sql = '
            SELECT
                [[phoo_image]].[id],
                [filename],
                [title],
                [createtime]
            FROM [[phoo_image]]
            LEFT JOIN [[phoo_image_album]] ON [[phoo_image]].[id] = [[phoo_image_album]].[phoo_image_id]
            WHERE ';

        if (!empty($params['date'])) {
            $params['date'] = str_replace('/', '_', $params['date']);

            $GLOBALS['db']->dbc->loadModule('Function', null, true);
            $substring = $GLOBALS['db']->dbc->function->substring('[[phoo_image]].[filename]', 6, 10);
            $sql .= ' ' . $substring . ' = {date} AND';
        }

        if (!empty($params['album'])) {
            $sql .= ' [[phoo_image_album]].[phoo_album_id] = {album} AND';
        }

        ///FIXME: We have to find a better solution
        if (!empty($params['words'])) {
            $words = explode(' ', $params['words']);
            $i = 0;
            foreach ($words as $word) {
                $sql .= " [[phoo_image]].[title] = LIKE({word_".$i."})";
                $params['word_'.$i] = '%'.$word.'%';
                $i++;
            }
        }

        $sql = substr($sql, 0, -3);
        $sql .= ' ORDER BY [createtime] DESC';

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('PHOO_ERROR_ADVANCEDSEARCH_QUERY'), _t('PHOO_NAME'));
        }

        return $result;
    }

    /**
     * Get entries as Moblog
     *
     * @access  public
     * @return  mixed   Returns an array of phoo entries in moblog format and Jaws_Error on error
     */
    function GetMoblog()
    {
        $params = array();
        $params['published'] = true;

        $sql = '
            SELECT
                [phoo_album_id],
                [filename],
                [[phoo_image]].[id],
                [[phoo_image]].[title],
                [[phoo_image]].[description],
                [[phoo_image]].[createtime]
            FROM [[phoo_image_album]]
            INNER JOIN [[phoo_image]] ON [[phoo_image]].[id] = [[phoo_image_album]].[phoo_image_id]
            INNER JOIN [[phoo_album]] ON [[phoo_album]].[id] = [[phoo_image_album]].[phoo_album_id]
            WHERE
                [[phoo_image]].[published] = {published}
              AND (';

        $album = $this->gadget->GetRegistry('moblog_album');
        if (Jaws_Error::isError($album)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETMOBLOG'), _t('PHOO_NAME'));
        }

        ///FIXME: We have to find a better solution, implode maybe
        foreach (explode(',', $album) as $v) {
            $sql .= "([[phoo_album]].[name] = '".$v."') OR ";
        }
        $sql  = substr($sql, 0, -3);
        $sql .= ') ORDER BY [[phoo_image]].[createtime] DESC';

        $limit = $this->gadget->GetRegistry('moblog_limit');
        if (Jaws_Error::isError($limit)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETMOBLOG'), _t('PHOO_NAME'));
        }

        $result = $GLOBALS['db']->setLimit($limit);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETMOBLOG'), _t('PHOO_NAME'));
        }

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETMOBLOG'), _t('PHOO_NAME'));
        }

        foreach ($result as $key => $image) {
            $result[$key]['name']   = $image['title'];
            $result[$key]['thumb']  = $this->GetThumbPath($image['filename']);
            $result[$key]['medium'] = $this->GetMediumPath($image['filename']);
            $result[$key]['image']  = $this->GetOriginalPath($image['filename']);
            $result[$key]['stripped_description'] = $image['description'];
        }

        return $result;
    }

    /**
     * Get a list of albums
     *
     * @access  public
     * @return  array  Returns an array of the dates of the phoo entries and Jaws_Error on error
     */
    function GetAlbumList()
    {
        $params = array();
        $params['published'] = true;

        $sql = '
            SELECT
                [id], [name], [description], [createtime]
            FROM [[phoo_album]]
            WHERE [published] = {published}
            ORDER BY '. $this->GetOrderType('albums_order_type');

        $albums = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($albums)) {
            return new Jaws_Error(_t('PHOO_ERROR_ALBUMLIST'), _t('PHOO_NAME'));
        }

        //Add unknown photo album to albums list
        array_push($albums, array('id'          => 0,
                                  'name'        => _t('PHOO_WITHOUT_ALBUM'),
                                  'description' => _t('PHOO_WITHOUT_ALBUM_DESCRIPTION'),
                                  'createtime'  => date('Y-m-d H:i:s')));

        $GLOBALS['db']->dbc->loadModule('Function', null, true);
        $rand = $GLOBALS['db']->dbc->function->random();

        for ($i = 0; $i < count($albums); $i++) {
            $params['id'] = $albums[$i]['id'];
            if ($params['id'] == 0) {
                // orphan photos
                $sql = '
                    SELECT
                        [[phoo_image]].[filename], [phoo_album_id]
                    FROM [[phoo_image]]
                    LEFT OUTER JOIN [[phoo_image_album]] ON [[phoo_image_album]].[phoo_image_id] = [[phoo_image]].[id]
                    WHERE [phoo_album_id] IS NULL AND [[phoo_image]].[published] = {published}
                    ORDER BY ' . $rand;
            } else {
                $sql = '
                    SELECT
                        [[phoo_image]].[filename]
                    FROM [[phoo_image_album]]
                    INNER JOIN [[phoo_image]] ON [[phoo_image]].[id] = [[phoo_image_album]].[phoo_image_id]
                    WHERE
                        [phoo_album_id] = {id} AND [[phoo_image]].[published] = {published}
                    GROUP BY
                        [[phoo_image]].[filename]
                    ORDER BY ' . $rand;
            }

            $images = $GLOBALS['db']->queryAll($sql, $params);
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
     * Get a random image
     *
     * @access  public
     * @param   int     $albumid    album ID
     * @return  array  The properties of a random image and Jaws_Error on error
     */
    function GetRandomImage($albumid = null)
    {
        $GLOBALS['db']->dbc->loadModule('Function', null, true);
        $rand = $GLOBALS['db']->dbc->function->random();

        $params = array();
        $params['album']     = (int)$albumid;
        $params['published'] = true;

        $sql = '
            SELECT
                [phoo_album_id],
                [filename],
                [[phoo_image]].[id],
                [[phoo_image]].[title],
                [[phoo_image]].[description]
            FROM [[phoo_image_album]]
            INNER JOIN [[phoo_image]] ON [[phoo_image]].[id] = [[phoo_image_album]].[phoo_image_id]
            WHERE [[phoo_image]].[published] = {published}';

        if (is_numeric($albumid)) {
            $sql .= ' AND [[phoo_image_album]].[phoo_album_id] = {album}';
        }
        $sql .= ' ORDER BY ' . $rand;

        $result = $GLOBALS['db']->setLimit('1');
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('PHOO_ERROR_RANDOMIMAGE'), _t('PHOO_NAME'));
        }

        $row = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($row) || !isset($row['filename'])) {
            return new Jaws_Error(_t('PHOO_ERROR_RANDOMIMAGE'), _t('PHOO_NAME'));
        }

        $row['name']   = $row['title'];
        $row['thumb']  = $this->GetThumbPath($row['filename']);
        $row['medium'] = $this->GetMediumPath($row['filename']);
        $row['image']  = $this->GetOriginalPath($row['filename']);
        $row['stripped_description'] = strip_tags($row['description']);

        return $row;
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
        $params       = array();
        $params['id'] = $id;

        $sql = '
            SELECT
                [id],
                [name],
                [description],
                [allow_comments],
                [published],
                [createtime]
            FROM [[phoo_album]]
            WHERE [id] = {id}';

        $types = array('integer', 'text', 'text', 'boolean', 'boolean', 'date');
        $row = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error(_t('PHOO_ERROR_ALBUMINFO'), _t('PHOO_NAME'));
        }

        return $row;
    }

    /**
     * Get a list of ordered albums
     * the order depends on what's passed to the function
     *
     * @access  public
     * @param   string  $by         order by
     * @param   string  $direction  order direction
     * @return  mixed   A list of available albums and Jaws_Error on error
     */
    function GetAlbums($by = 'name', $direction = 'ASC')
    {
        $directions = array('ASC', 'DESC');
        $direction  = strtoupper($direction);
        if (!in_array($direction, $directions)) {
            $direction = 'ASC';
        }

        $fields = array('id', 'name', 'description', 'createtime');
        $by     = strtolower($by);
        if (!in_array($by, $fields)) {
            $by = 'name';
        }

        $sql = "
            SELECT
                [id], [name], [published], [createtime], COUNT([phoo_image_id]) AS howmany
            FROM [[phoo_album]]
            LEFT JOIN [[phoo_image_album]] ON [id] = [phoo_album_id]
            GROUP BY
                [id], [name], [published], [createtime]
            ORDER BY [$by] $direction";

        $types = array('integer', 'text', 'boolean', 'timestamp', 'integer');
        $rows = $GLOBALS['db']->queryAll($sql, null, $types);
        if (Jaws_Error::IsError($rows)) {
            return new Jaws_Error(_t('PHOO_ERROR_ALBUMS', $by), _t('PHOO_NAME'));
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
        $params        = array();
        $params['id']  = $id;

         if ($id == '0') { //UNKNOWN
            $sql = '
                SELECT COUNT([id])
                FROM [[phoo_image]]
                LEFT OUTER JOIN [[phoo_image_album]] ON [[phoo_image_album]].[phoo_image_id] = [[phoo_image]].[id]
                WHERE [phoo_album_id] IS NULL';
         } else {
            $sql = '
                SELECT COUNT([id])
                FROM [[phoo_image]]
                INNER JOIN [[phoo_image_album]] ON [[phoo_image_album]].[phoo_image_id] = [[phoo_image]].[id]
                WHERE [phoo_album_id] = {id}';
        }

        $r = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($r)) {
            return 0;
        }

        return($r != null ? $r : 0);
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
        $limit = $this->gadget->GetRegistry('thumbnail_limit');
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
        $page_size = $this->gadget->GetRegistry('thumbnail_limit');
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
        $params = array();
        $params['id']    = $id;
        $params['month'] = $month;
        $params['year']  = $year;

        $sql = '
            SELECT
                [id], [name], [description], [createtime], [published]
            FROM [[phoo_album]]
            WHERE [id] = {id}';

        $types = array('integer', 'text', 'text', 'timestamp', 'boolean');
        $r = $GLOBALS['db']->queryRow($sql, $params, $types);
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
            $sql2 = '
                SELECT
                    [[phoo_image]].[id], [phoo_album_id], [filename], [[phoo_image]].[title],
                    [[phoo_image]].[description], [published]
                FROM [[phoo_image]]
                LEFT OUTER JOIN [[phoo_image_album]] ON [[phoo_image_album]].[phoo_image_id] = [[phoo_image]].[id]
                WHERE [phoo_album_id] IS NULL
                ORDER BY [[phoo_image]].'. $this->GetOrderType('photos_order_type');
        } else {
            $params['id'] = $id;
            $sql2 = '
                SELECT
                    [[phoo_image]].[id], [phoo_album_id], [filename], [[phoo_image]].[title],
                    [[phoo_image]].[description], [published]
                FROM [[phoo_image_album]]
                INNER JOIN [[phoo_image]] ON [[phoo_image]].[id] = [[phoo_image_album]].[phoo_image_id]
                WHERE [phoo_album_id] = {id}';
            if (checkdate($month, $day, $year)) {
                if (strlen($day) == 1) {
                    $day = '0'.$day;
                }
                if (strlen($month) == 1) {
                    $month = '0'.$month;
                }
                $params['start'] = $year.'-'.$month.'-'.$day;
                $params['end'] = date('Y-m-d', mktime(0, 0, 0, $month, $day + 30, $year));
                $sql2 .= ' AND [[phoo_image]].[createtime] BETWEEN {start} AND {end} ';
            }
            $sql2 .= ' ORDER BY [[phoo_image]].'. $this->GetOrderType('photos_order_type');
        }

        $limit = $this->gadget->GetRegistry('thumbnail_limit');
        if (!empty($page) && !empty($limit)) {
            $starting_image = ($page - 1) * $limit;
            $result = $GLOBALS['db']->setLimit($limit, $starting_image);
            if (Jaws_Error::IsError($result)) {
                return new Jaws_Error(_t('PHOO_ERROR_GETALBUM'), _t('PHOO_NAME'));
            }
        }

        $types = array('integer', 'integer', 'text', 'text', 'text', 'boolean');
        $r2 = $GLOBALS['db']->queryAll($sql2, $params, $types);
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
        $params              = array();
        $params['id']        = $id;
        $params['album_id']  = $album_id;
        $params['published'] = true;

        if ($album_id != '0') {  //UNKNOWN
            $sql = '
                SELECT
                    [[phoo_image]].[id],
                    [[phoo_image]].[title],
                    [[phoo_image]].[description],
                    [[users]].[nickname],
                    [[phoo_image]].[filename],
                    [[phoo_image]].[published],
                    [[phoo_image]].[allow_comments],
                    [[phoo_album]].[allow_comments] as album_allow_comments
                FROM [[phoo_image]]
                LEFT JOIN [[phoo_image_album]] ON [[phoo_image_album]].[phoo_image_id] = [[phoo_image]].[id]
                LEFT JOIN [[phoo_album]] ON [[phoo_album]].[id] = [[phoo_image_album]].[phoo_album_id]
                LEFT JOIN [[users]] ON [[phoo_image]].[user_id] = [[users]].[id]
                WHERE
                    [[phoo_image]].[id] = {id}
                  AND
                    [[phoo_image]].[published] = {published}
                  AND
                    [[phoo_album]].[published] = {published}';
        } else {
            $sql = '
                SELECT
                    [[phoo_image]].[id],
                    [[phoo_image]].[title],
                    [[phoo_image]].[description],
                    [[users]].[nickname],
                    [[phoo_image]].[filename],
                    [[phoo_image]].[published],
                    [[phoo_image]].[allow_comments],
                    [[phoo_album]].[allow_comments] as album_allow_comments
                FROM [[phoo_image]]
                LEFT JOIN [[phoo_image_album]] ON [[phoo_image_album]].[phoo_image_id] = [[phoo_image]].[id]
                LEFT JOIN [[phoo_album]] ON [[phoo_album]].[id] = [[phoo_image_album]].[phoo_album_id]
                LEFT JOIN [[users]] ON [[phoo_image]].[user_id] = [[users]].[id]
                WHERE
                    [[phoo_image]].[id] = {id}
                  AND
                    [[phoo_image]].[published] = {published}';
        }

        $types = array('integer', 'text', 'text', 'text', 'text', 'boolean', 'boolean', 'boolean');
        $r = $GLOBALS['db']->queryRow($sql, $params, $types);
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
            $sql = '
                SELECT
                    [id]
                FROM [[phoo_image_album]]
                INNER JOIN [[phoo_image]] ON [[phoo_image]].[id] = [[phoo_image_album]].[phoo_image_id]
                WHERE [phoo_album_id] = {album_id} AND [[phoo_image]].[published] = {published}
                ORDER BY [[phoo_image]].'. $this->GetOrderType('photos_order_type');
        } else {
            $sql = '
                SELECT
                    [id]
                FROM [[phoo_image]]
                LEFT OUTER JOIN [[phoo_image_album]] ON [[phoo_image_album]].[phoo_image_id] = [[phoo_image]].[id]
                WHERE [phoo_album_id] IS NULL AND [[phoo_image]].[published] = {published}
                ORDER BY [[phoo_image]].'. $this->GetOrderType('photos_order_type');
        }

        $items = $GLOBALS['db']->queryCol($sql, $params);
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
        $show = $this->gadget->GetRegistry('show_exif_info');
        if ($show == 'true' && function_exists('exif_read_data')) {
            if ($data = @exif_read_data(JAWS_DATA . 'phoo/' . $r['filename'], 1, true)) {
                $cameraimg = '';
                if (isset($data['IFD0']['Make'])) {
                    $camera = $data['IFD0']['Make'].' / '.$data['IFD0']['Model'];
                    $image['exif']['camera'] = $camera;
                    $cameraimg = 'gadgets/Phoo/images/'.str_replace(' ','',$data['IFD0']['Make']).'_'.
                        str_replace(' ', '', $data['IFD0']['Model']).'.jpg';
                    $image['exif']['cameraimg'] = $cameraimg;
                }

                if (!file_exists($cameraimg)) {
                    $image['exif']['cameraimg'] = 'gadgets/Phoo/images/Camera.png';
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
        $params       = array();
        $params['id'] = $id;

        $sql = '
            SELECT
                [[phoo_image]].[id],
                [filename],
                [[phoo_image]].[description],
                [title],
                [allow_comments],
                [published],
                [[phoo_image_album]].[phoo_album_id]
            FROM [[phoo_image]]
            LEFT JOIN [[phoo_image_album]] ON [[phoo_image]].[id] = [[phoo_image_album]].[phoo_image_id]
            WHERE [[phoo_image]].[id] = {id}';

        $types = array('integer', 'text', 'text', 'text', 'boolean', 'boolean', 'integer');
        $rs = $GLOBALS['db']->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($rs)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETIMAGEENTRY'), _t('PHOO_NAME'));
        }

        $entry = array();
        foreach ($rs as $i) {
            if (empty($entry)) {
                $entry['id']             = $i['id'];
                $entry['thumb']          = PhooModel::GetThumbPath($i['filename']);
                $entry['medium']         = PhooModel::GetMediumPath($i['filename']);
                $entry['image']          = PhooModel::GetOriginalPath($i['filename']);
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

    /**
     * Get a portrait of an image
     *
     * @access  public
     * @param   string  $id     ID of the image
     * @return  mixed   An array with the images with a portrait look&feel and Jaws_Error on error
     */
    function GetAsPortrait($id = '')
    {
        $params = array();
        $params['id'] = (int) $id;
        $params['published'] = true;

        $sql = '
            SELECT
                [filename],
                [[phoo_image]].[id],
                [[phoo_image]].[title],
                [[phoo_image]].[description],
                [[phoo_image]].[createtime]
            FROM [[phoo_image_album]]
            INNER JOIN [[phoo_image]] ON [[phoo_image]].[id] = [[phoo_image_album]].[phoo_image_id]
            INNER JOIN [[phoo_album]] ON [[phoo_album]].[id] = [[phoo_image_album]].[phoo_album_id]
            WHERE [[phoo_image]].[published] = {published} AND (';

        $album = $this->gadget->GetRegistry('photoblog_album');
        foreach (explode(',', $album) as $v) {
            $sql .= "([[phoo_album]].[name] = '".$v."') OR ";
        }
        $sql  = substr($sql, 0, -3);
        $sql .= ') ';
        $sql .= ' ORDER BY [[phoo_image]].[id] DESC';

        $limit = $this->gadget->GetRegistry('photoblog_limit');
        $result = $GLOBALS['db']->setLimit($limit);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETASPORTRAIT'), _t('PHOO_NAME'));
        }

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETASPORTRAIT'), _t('PHOO_NAME'));
        }

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        $portrait = array();
        foreach ($result as $r) {
            $info = array();

            $info['id']          = $r['id'];
            $info['name']        = $r['title'];
            $info['filename']    = $r['filename'];
            $info['description'] = $r['description'];
            $info['createtime']  = $r['createtime'];
            $info['thumb']       = $this->GetThumbPath($r['filename']);
            $info['medium']      = $this->GetMediumPath($r['filename']);
            $info['image']       = $this->GetOriginalPath($r['filename']);
            $info['stripped_description'] = strip_tags($r['description']);

            $portrait[] = $info;
        }

        return $portrait;
    }

    /**
     * Get a list of comments
     *
     * @access  public
     * @param   int     $id     ID of the comment
     * @param   int     $parent ID of the parent comment
     * @return  mixed   A list of comments and Jaws_Error on error
     */
    function GetComments($id, $parent)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $comments = $cModel->GetComments($this->gadget->name, $id, $parent, true, false, false, true);
        if (Jaws_Error::IsError($comments)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETCOMMENTS'), _t('PHOO_NAME'));
        }

        $this->_AdditionalCommentsData($comments);
        return $comments;
    }

    /**
     * Puts avatar and format time for given comments
     * 
     * @access  private
     * @param   array   $comments   comments array reference
     */
    function _AdditionalCommentsData(&$comments)
    {
        require_once JAWS_PATH.'include/Jaws/Gravatar.php';
        foreach ($comments as $k => $v) {
            $comments[$k]['avatar_source'] = Jaws_Gravatar::GetGravatar($v['email']);
            $comments[$k]['createtime']    = $v['createtime'];
            if (count($comments[$k]['childs']) > 0) {
                $this->_AdditionalCommentsData($comments[$k]['childs']);
            }
        }
    }

    /**
     * Get last comments
     *
     * @access  public
     * @return  mixed   Returns a list of recent comments and Jaws_Error on error
     */
    function GetRecentComments()
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $comments = $cModel->GetRecentComments($this->gadget->name, 10);
        if (Jaws_Error::IsError($comments)) {
            return new Jaws_Error(_t('PHOO_ERROR_RECENTCOMMENTS'), _t('PHOO_NAME'));
        }

        return $comments;
    }

    /**
     * Get a list of comments
     *
     * @access  public
     * @param   string  $filterby   Filter to use(postid, author, email, url, title, comment)
     * @param   string  $filter     Filter data
     * @return  mixed   Returns a list of comments and Jaws_Error on error
     */
    function GetCommentsFiltered($filterby, $filter)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $filterMode = '';
        switch($filterby) {
        case 'postid':
            $filterMode = COMMENT_FILTERBY_REFERENCE;
            break;
        case 'name':
            $filterMode = COMMENT_FILTERBY_NAME;
            break;
        case 'email':
            $filterMode = COMMENT_FILTERBY_EMAIL;
            break;
        case 'url':
            $filterMode = COMMENT_FILTERBY_URL;
            break;
        case 'title':
            $filterMode = COMMENT_FILTERBY_TITLE;
            break;
        case 'ip':
            $filterMode = COMMENT_FILTERBY_IP;
            break;
        case 'comment':
            $filterMode = COMMENT_FILTERBY_MESSAGE;
            break;
        case 'various':
            $filterMode = COMMENT_FILTERBY_VARIOUS;
            break;
        default:
            $filterMode = null;
            break;
        }

        $comments = $cModel->GetFilteredComments($this->gadget->name, $filterMode, $filter);
        if (Jaws_Error::IsError($comments)) {
            return new Jaws_Error(_t('PHOO_ERROR_FILETEREDCOMMENTS'), _t('PHOO_NAME'));
        }

        $commentsGravatar = array();
        require_once JAWS_PATH . 'include/Jaws/Gravatar.php';
        foreach ($comments as $r) {
            $r['avatar_source'] = Jaws_Gravatar::GetGravatar($r['email']);
            $r['createtime'] = $r['createtime'];
            $commentsGravatar[] = $r;
        }

        return $commentsGravatar;
    }

    /**
     * Get a comment
     *
     * @access  public
     * @param   int     $id  ID of the comment
     * @return  mixed   Properties of a comment and Jaws_Error on error
     */
    function GetComment($id)
    {
        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $comment = $cModel->GetComment($this->gadget->name, $id);
        if (Jaws_Error::IsError($comment)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETCOMMENT'), _t('PHOO_NAME'));
        }

        require_once JAWS_PATH . 'include/Jaws/Gravatar.php';
        if ($comment) {
            $comment['avatar_source'] = Jaws_Gravatar::GetGravatar($comment['email']);
            $comment['createtime']    = $comment['createtime'];
            $comment['comments']      = $comment['msg_txt'];
        }

        return $comment;
    }

    /**
     * This function mails the comments to the owner
     *
     * @access  public
     * @param   int    $link       The permanent link
     * @param   string $title      The email title
     * @param   string $from_email The email to sendto
     * @param   string $comment    The body of the email (The actual comment)
     * @param   string $url        The url of the blog id
     */
    function MailComment($link, $title, $from_email, $comment, $url)
    {
        require_once JAWS_PATH . '/include/Jaws/Mail.php';
        $mail = new Jaws_Mail;

        $subject   = $title;

        $comment .= "<br /><br />";
        $comment .= _t("PHOO_COMMENT_MAIL_VISIT_URL", $GLOBALS['app']->getSiteURL('/'). $link, $title);

        $mail->SetFrom($from_email);
        $mail->AddRecipient('');
        $mail->SetSubject($subject);
        $mail->SetBody($comment, 'html');
        $result = $mail->send();
    }

    /**
     * Create a new Comment
     *
     * @access  public
     * @param   string  $name         Name of the author
     * @param   string  $title        Title of the comment
     * @param   string  $url          Url of the author
     * @param   string  $email        Email of the author
     * @param   string  $comments     Text of the comment
     * @param   int     $parent       ID of the parent comment
     * @param   int     $parent_entry ID of the entry
     * @param   string  $permalink    Permalink of the image
     * @param   string  $ip           IP of the author
     * @param   bool    $set_cookie   Create a cookie
     * @return  mixed   True if comment was added, and Jaws_Error if not.
     */
    function NewComment($name, $title, $url, $email, $comments, $parent, $parent_entry, $permalink, $ip = '', $set_cookie = true)
    {
        $params = array();
        $params['parent_id'] = $parent_entry;
        $params['allow_c']   = true;

        $sql = 'SELECT [id] FROM [[phoo_image]] WHERE [id] = {parent_id} AND [allow_comments] = {allow_c}';
        $id = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($id)) {
            return new Jaws_Error(_t('PHOO_ERROR_CANT_ADD_COMMENT'), _t('PHOO_NAME'));
        }

        if (empty($id)) {
            return false;
        }

        ///FIXME: Lets get a better ip detection ;)
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (!$parent) {
            $parent = 0;
        }

        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $status = $this->gadget->GetRegistry('comment_status');
        if ($GLOBALS['app']->Session->GetPermission('Phoo', 'ManageComments')) {
            $status = COMMENT_STATUS_APPROVED;
        }

        $res = $cModel->NewComment(
            $this->gadget->name, $parent_entry,
            $name, $email, $url, $title, $comments,
            $ip, $permalink, $parent, $status
        );
        if (Jaws_Error::isError($res)) {
            return new Jaws_Error($res->getMessage(), _t('PHOO_NAME'));
        }

        //Send an email to website owner
        $this->MailComment($permalink, $title, $email, $comments, $url);
        if ($res == COMMENT_STATUS_APPROVED) {
            $params = array();
            $params['id'] = $id;
            $howmany = $cModel->HowManyFilteredComments(
                $this->gadget->name,
                'gadget_reference',
                $id,
                'approved'
            );
            if (!Jaws_Error::IsError($howmany)) {
                $params['comments'] = $howmany;
                $sql = 'UPDATE [[phoo_image]] SET [comments] = {comments} WHERE [id] = {id}';
                $result = $GLOBALS['db']->query($sql, $params);
                if (Jaws_Error::IsError($result)) {
                    return new Jaws_Error(_t('PHOO_ERROR_CANT_ADD_COMMENT'), _t('PHOO_NAME'));
                }
            }
        }

        if ($set_cookie) {
            $GLOBALS['app']->Session->SetCookie('visitor_name',  $name,  60*24*150);
            $GLOBALS['app']->Session->SetCookie('visitor_email', $email, 60*24*150);
            $GLOBALS['app']->Session->SetCookie('visitor_url',   $url,   60*24*150);
        }

        return true;
    }

    /**
     * Get registry settings for Phoo
     *
     * @access  public
     * @return  mixed    array with the settings or Jaws_Error on error
     */
    function GetSettings()
    {
        $ret = array();
        $ret['default_action']    = $this->gadget->GetRegistry('default_action');
        $ret['resize_method']     = $this->gadget->GetRegistry('resize_method');
        $ret['moblog_album']      = $this->gadget->GetRegistry('moblog_album');
        $ret['moblog_limit']      = $this->gadget->GetRegistry('moblog_limit');
        $ret['photoblog_album']   = $this->gadget->GetRegistry('photoblog_album');
        $ret['photoblog_limit']   = $this->gadget->GetRegistry('photoblog_limit');
        $ret['allow_comments']    = $this->gadget->GetRegistry('allow_comments');
        $ret['published']         = $this->gadget->GetRegistry('published');
        $ret['show_exif_info']    = $this->gadget->GetRegistry('show_exif_info');
        $ret['keep_original']     = $this->gadget->GetRegistry('keep_original');
        $ret['thumbnail_limit']   = $this->gadget->GetRegistry('thumbnail_limit');
        $ret['comment_status']    = $this->gadget->GetRegistry('comment_status');
        $ret['use_antispam']      = $this->gadget->GetRegistry('use_antispam');
        $ret['albums_order_type'] = $this->gadget->GetRegistry('albums_order_type');
        $ret['photos_order_type'] = $this->gadget->GetRegistry('photos_order_type');

        foreach ($ret as $r) {
            if (Jaws_Error::IsError($r)) {
                if (isset($GLOBALS['app']->Session)) {
                    $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_ERROR_CANT_FETCH_SETTINGS'), RESPONSE_ERROR);
                }
                return new Jaws_Error(_t('PHOO_ERROR_CANT_FETCH_SETTINGS'), _t('PHOO_NAME'));
            }
        }

        return $ret;
    }
    
    /**
     * Return the album id for the album name
     *
     * @access  public
     * @param   string  $albumname  AlbumName string
     * @return  mixed   An array contains the Album info and False on errors
     */
    function GetFastURL($albumname)
    {
        $params = array();
        $params['album_name'] = $albumname;

        $sql = '
            SELECT
                [id] as fast_url, [name] as title
            FROM [[phoo_album]]
            WHERE [name] = {album_name}';
        $res = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return $res;
    }
    
    /**
     * Returns the first album of a given image id
     * 
     * @access  public
     * @param   int     $id     Image id.
     * @return  mixed   result array or false on error
     */
    function GetImageAlbum($id) 
    {
        $sql = 'SELECT [phoo_album_id]
                FROM [[phoo_image_album]] 
                WHERE [phoo_image_id] = {id}
                ORDER BY [phoo_album_id]';
        $result = $GLOBALS['db']->setLimit('1');

        $res = $GLOBALS['db']->queryOne($sql, array('id' => $id));
        if (Jaws_Error::IsError($res)) {
            return false;
        }   
        return $res;
    }

}