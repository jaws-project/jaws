<?php
/**
 * Phoo Gadget
 *
 * @category   Gadget
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_HTML extends Jaws_Gadget_HTML
{
    /**
     * Returns the default action to use if none is specified.
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function DefaultAction()
    {
        return $this->AlbumList();
    }

    /**
     * Displays an index of galleries.
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function AlbumList()
    {
        $this->SetTitle(_t('PHOO_ALBUMS'));
        $layoutGadget = $GLOBALS['app']->LoadGadget('Phoo', 'LayoutHTML');
        return $layoutGadget->AlbumList();
    }

    /**
     * Displays an index of pictures in an album.
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function ViewAlbum()
    {
        return $this->ViewAlbumPage();
    }

    /**
     * Displays a paged index of pictures in an album.
     * TODO: Test it, maybe we need some modifications in ViewImage...
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function ViewAlbumPage()
    {
        $tpl = $this->gadget->loadTemplate('ViewAlbumPage.html');
        $tpl->SetBlock('ViewAlbumPage');

        $request =& Jaws_Request::getInstance();
        $get     = $request->get(array('id', 'page'), 'get');

        $id = !empty($get['id'])? $get['id'] : '0';
        $page = !empty($get['page'])? (int) $get['page'] : 1;

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $album = $model->GetAlbumImages($id, $page);
        if (!Jaws_Error::IsError($album) && !empty($album) && $album['published']) {
            // display album info
            if ($id == '0') {
                $tpl->SetVariable('title', _t('PHOO_UNKNOW_ALBUM'));
                $this->SetTitle(_t('PHOO_UNKNOW_ALBUM'));
                $tpl->SetVariable('description', '');
            } else {
                $tpl->SetVariable('title', $album['name']);
                $this->SetTitle($album['name']);
                $tpl->SetVariable('description', $this->gadget->ParseText($album['description']));
            }

            // display images
            $tpl->SetBlock('ViewAlbumPage/photos');
            if (isset($album['images']) && is_array($album['images'])) {
                require_once JAWS_PATH . 'include/Jaws/Image.php';
                foreach ($album['images'] as $image) {
                    if ($image['published'] === true) {
                        $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $image['thumb']);
                        if (Jaws_Error::IsError($imgData)) {
                            continue;
                        }
                        $tpl->SetBlock('ViewAlbumPage/photos/item');
                        $url = $this->gadget->GetURLFor('ViewImage', array('id' => $image['id'], 'albumid' => $image['albumid']));
                        $tpl->SetVariable('url',      $url);
                        $tpl->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $image['thumb']));
                        $tpl->SetVariable('medium',   $GLOBALS['app']->getDataURL('phoo/' . $image['medium']));
                        $tpl->SetVariable('image',    $GLOBALS['app']->getDataURL('phoo/' . $image['image']));
                        $tpl->SetVariable('name',     $image['name']);
                        $tpl->SetVariable('filename', $image['filename']);
                        $tpl->SetVariable('img_desc', $image['stripped_description']);
                        $tpl->SetVariable('width',    $imgData[0]);
                        $tpl->SetVariable('height',   $imgData[1]);
                        $tpl->ParseBlock('ViewAlbumPage/photos/item');
                    }
                }
            }
            $tpl->ParseBlock('ViewAlbumPage/photos');

            // Pager
            $pager = $model->GetAlbumPagerNumbered($id, $page);

            if (count($pager) > 0) {
                $tpl->SetBlock('ViewAlbumPage/pager');
                $tpl->SetVariable('total', _t('PHOO_PHOTOS_COUNT', $pager['total']));

                $pager_view = '';
                foreach ($pager as $k => $v) {
                    $tpl->SetBlock('ViewAlbumPage/pager/item');
                    if ($k == 'next') {
                        if ($v) {
                            $tpl->SetBlock('ViewAlbumPage/pager/item/next');
                            $tpl->SetVariable('lbl_next', _t('PHOO_NEXT'));
                            $url = $this->gadget->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
                            $tpl->SetVariable('url_next', $url);
                            $tpl->ParseBlock('ViewAlbumPage/pager/item/next');
                        } else {
                            $tpl->SetBlock('ViewAlbumPage/pager/item/no_next');
                            $tpl->SetVariable('lbl_next', _t('PHOO_NEXT'));
                            $tpl->ParseBlock('ViewAlbumPage/pager/item/no_next');
                        }
                    } elseif ($k == 'previous') {
                        if ($v) {
                            $tpl->SetBlock('ViewAlbumPage/pager/item/previous');
                            $tpl->SetVariable('lbl_previous', _t('PHOO_PREVIOUS'));
                            $url = $this->gadget->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
                            $tpl->SetVariable('url_previous', $url);
                            $tpl->ParseBlock('ViewAlbumPage/pager/item/previous');
                        } else {
                            $tpl->SetBlock('ViewAlbumPage/pager/item/no_previous');
                            $tpl->SetVariable('lbl_previous', _t('PHOO_PREVIOUS'));
                            $tpl->ParseBlock('ViewAlbumPage/pager/item/no_previous');
                        }
                    } elseif ($k == 'separator1' || $k == 'separator2') {
                        $tpl->SetBlock('ViewAlbumPage/pager/item/page_separator');
                        $tpl->ParseBlock('ViewAlbumPage/pager/item/page_separator');
                    } elseif ($k == 'current') {
                        $tpl->SetBlock('ViewAlbumPage/pager/item/page_current');
                        $url = $this->gadget->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
                        $tpl->SetVariable('lbl_page', $v);
                        $tpl->SetVariable('url_page', $url);
                        $tpl->ParseBlock('ViewAlbumPage/pager/item/page_current');
                    } elseif ($k != 'total' && $k != 'next' && $k != 'previous') {
                        $tpl->SetBlock('ViewAlbumPage/pager/item/page_number');
                        $url = $this->gadget->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
                        $tpl->SetVariable('lbl_page', $v);
                        $tpl->SetVariable('url_page', $url);
                        $tpl->ParseBlock('ViewAlbumPage/pager/item/page_number');
                    }
                    $tpl->ParseBlock('ViewAlbumPage/pager/item');
                }
                $tpl->ParseBlock('ViewAlbumPage/pager');
            }
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }

        $tpl->ParseBlock('ViewAlbumPage');
        return $tpl->Get();
    }

    /**
     * Displays an individual image.
     *
     * @access  public
     * @param   int     $id                 image ID
     * @param   int     $albumid            album ID
     * @param   bool    $preview_mode       preview mode
     * @return  string   XHTML template content
     */
    function ViewImage($id = null, $albumid = null, $preview_mode = false)
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        $tpl = $this->gadget->loadTemplate('ViewImage.html');

        $request =& Jaws_Request::getInstance();
        $get     = $request->get(array('id', 'albumid'), 'get');
        $id      = !is_null($id)? $id : (!empty($get['id'])? $get['id'] : '0');
        $albumid = !is_null($albumid)? $albumid : (!empty($get['albumid'])? $get['albumid'] : '0');

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $image = $model->GetImage($id, $albumid);
        if (Jaws_Error::IsError($image) || empty($image)) {
            return Jaws_HTTPError::Get(404);
        }

        require_once JAWS_PATH . 'include/Jaws/Image.php';
        $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $image['medium']);
        if (Jaws_Error::IsError($imgData)) {
            return Jaws_HTTPError::Get(404);
        }

        $this->SetTitle($image['name']);
        $tpl->SetBlock('ViewImage');
        $tpl->SetVariable('title',       $image['name']);
        $tpl->SetVariable('posted_by',   _t('PHOO_POSTED_BY'));
        $tpl->SetVariable('img_author',  $image['author']);
        $tpl->SetVariable('name',        $image['name']);
        $tpl->SetVariable('filename',    $image['filename']);
        $tpl->SetVariable('img_desc',    $image['stripped_description']);
        $tpl->SetVariable('albumid',     $albumid);
        $tpl->SetVariable('description', $this->gadget->ParseText($image['description']));
        $tpl->SetVariable('medium',      $GLOBALS['app']->getDataURL('phoo/' . $image['medium']));
        $tpl->SetVariable('image',       $GLOBALS['app']->getDataURL('phoo/' . $image['image']));
        $tpl->SetVariable('width',       $imgData[0]);
        $tpl->SetVariable('height',      $imgData[1]);

        // show if the original was kept
        $settings = $model->GetSettings();
        if ($settings['keep_original'] == 'true') {
            $tpl->SetVariable('url', $GLOBALS['app']->getDataURL('phoo/' . $image['image']));
        } else {
            $tpl->SetVariable('url', 'javascript: void();');
        }

        if (Jaws_Gadget::IsGadgetInstalled('Comments')) {
            $allow_comments_config = $this->gadget->registry->fetch('allow_comments', 'Comments');
            switch ($allow_comments_config) {
                case 'restricted':
                    $allow_comments_config = $GLOBALS['app']->Session->Logged();
                    $restricted = !$allow_comments_config;
                    break;

                default:
                    $restricted = false;
                    $allow_comments_config = $allow_comments_config == 'true';
            }

            $allow_comments = $image['allow_comments'] === true &&
                              $image['album_allow_comments'] === true &&
                              $this->gadget->registry->fetch('allow_comments') == 'true' &&
                              $allow_comments_config;

            $redirect_to = $this->gadget->GetURLFor('ViewImage', array('id' => $image['id'], 'albumid' => $albumid));

            $cHTML = $GLOBALS['app']->LoadGadget('Comments', 'HTML', 'Comments');
            $tpl->SetVariable('comments', $cHTML->ShowComments('Phoo', 'photo', $image['id'],
                            array('action' => 'ViewImage',
                                  'params' => array('albumid' => $albumid, 'id' => $image['id']))));

            if ($allow_comments) {
                if ($preview_mode) {
                    $tpl->SetVariable('preview', $cHTML->ShowPreview());
                }
                $tpl->SetVariable('comment-form', $cHTML->ShowCommentsForm('Phoo', 'photo', $image['id'], $redirect_to));
            } elseif ($restricted) {
                $login_url = $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox');
                $register_url = $GLOBALS['app']->Map->GetURLFor('Users', 'Registration');
                $tpl->SetVariable('comment-form', _t('GLOBAL_COMMENTS_RESTRICTED', $login_url, $register_url));
            }

        }

        // Pager
        $tpl->SetBlock('ViewImage/navigation');
        $tpl->SetVariable('lbl_page_counter', _t('PHOO_PHOTO_COUNTER', $image['pos'], $image['total']));
        $tpl->SetVariable('lbl_thumbs', _t('PHOO_THUMBS'));
        $url = $this->gadget->GetURLFor('ViewAlbum', array('id' => $albumid));
        $tpl->SetVariable('url_thumbs', $url);

        if ($image['first'] != $image['id']) {
            $tpl->SetBlock('ViewImage/navigation/no-first-photo');
            $tpl->SetVariable('lbl_first', _t('PHOO_FIRST'));
            $url = $this->gadget->GetURLFor('ViewImage', array('id' => $image['first'], 'albumid' => $albumid));
            $tpl->SetVariable('url_first', $url);
            $tpl->SetVariable('lbl_prev', _t('PHOO_PREVIOUS'));
            $url = $this->gadget->GetURLFor('ViewImage', array('id' => $image['previous'], 'albumid' => $albumid));
            $tpl->SetVariable('url_prev', $url);
            $tpl->ParseBlock('ViewImage/navigation/no-first-photo');
        } else {
            $tpl->SetBlock('ViewImage/navigation/first-photo');
            $tpl->SetVariable('lbl_first', _t('PHOO_FIRST'));
            $tpl->SetVariable('lbl_prev',  _t('PHOO_PREVIOUS'));
            $tpl->ParseBlock('ViewImage/navigation/first-photo');
        }

        if ($image['last'] != $image['id']) {
            $tpl->SetBlock('ViewImage/navigation/no-last-photo');
            $tpl->SetVariable('lbl_next', _t('PHOO_NEXT'));
            $url = $this->gadget->GetURLFor('ViewImage', array('id' => $image['next'], 'albumid' => $albumid));
            $tpl->SetVariable('url_next', $url);
            $tpl->SetVariable('lbl_last', _t('PHOO_LAST'));
            $url = $this->gadget->GetURLFor('ViewImage', array('id' => $image['last'], 'albumid' => $albumid));
            $tpl->SetVariable('url_last', $url);
            $tpl->ParseBlock('ViewImage/navigation/no-last-photo');
        } else {
            $tpl->SetBlock('ViewImage/navigation/last-photo');
            $tpl->SetVariable('lbl_next', _t('PHOO_NEXT'));
            $tpl->SetVariable('lbl_last', _t('PHOO_LAST'));
            $tpl->ParseBlock('ViewImage/navigation/last-photo');
        }

        $tpl->ParseBlock('ViewImage/navigation');

        // EXIF STUFF
        if ($settings['show_exif_info'] == 'true' && isset($image['exif']) && count($image['exif']) > 0) {
            $datatext = '';
            if (!empty($image['exif']['width'])) {
                $datatext .= _t('PHOO_WIDTH').': '.$image['exif']['width'] . 'px<br />';
                $datatext .= _t('PHOO_HEIGHT').': '.$image['exif']['height'] . 'px<br />';
            }

            if (!empty($image['exif']['filesize'])) {
                $datatext .= _t('PHOO_SIZE') . ': ' . $image['exif']['filesize'] . '<br />';
            }

            if (!empty($image['exif']['datetime'])) {
                $date = $GLOBALS['app']->loadDate();
                $datatext .= _t('GLOBAL_DATE') . ': ' . $date->Format($image['exif']['datetime']) . '<br />';
            }

            if (!empty($image['exif']['aperture'])) {
                $datatext .= _t('PHOO_APERTURE') . ': ' . $image['exif']['aperture'] . '<br />';
            }

            if (!empty($image['exif']['exposure'])) {
                $datatext .= _t('PHOO_EXPOSURE_TIME') . ': ' . $image['exif']['exposure'] . '<br />';
            }

            if (!empty($image['exif']['focallength'])) {
                $datatext .= _t('PHOO_FOCAL_LENGTH') . ': ' . $image['exif']['focallength'];
            }

            $tpl->SetBlock('ViewImage/exif');
            $tpl->SetVariable('exif_info', _t('PHOO_EXIF_INFO'));
            $tpl->SetVariable('cameraimg', $image['exif']['cameraimg']);
            if (!empty($image['exif']['camera']))  {
                $tpl->SetVariable('camera', $image['exif']['camera']);
            } else {
                $tpl->SetVariable('camera', _t('PHOO_UNKNOWN_CAM'));
            }
            $tpl->SetVariable('data', $datatext);
            $tpl->ParseBlock('ViewImage/exif');
        }
        $tpl->ParseBlock('ViewImage');

        return $tpl->Get();
    }

    /**
     * I'm not sure what this does... gets the authors photo maybe?
     *
     * @access  public
     * @see Phoo_Model::GetAsPortrait()
     * @return  string   XHTML template content
     * @todo Better docblock
     */
    function PhotoblogPortrait()
    {
        $request =& Jaws_Request::getInstance();

        $photoid = $request->get('photoid', 'get');
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $entries = $model->GetAsPortrait($photoid);
        if (Jaws_Error::IsError($entries)) {
            return '';
        }

        if (count($entries) <= 0) {
            return '';
        }

        $this->SetTitle(_t('PHOO_PHOTOBLOG'));
        $tpl = $this->gadget->loadTemplate('Photoblog.html');
        $tpl->SetBlock('photoblog_portrait');
        $first = true;
        include_once JAWS_PATH . 'include/Jaws/Image.php';
        $date = $GLOBALS['app']->loadDate();
        foreach ($entries as $entry) {
            if (empty($photoid)) {
                if (!$first) {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['thumb']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $tpl->SetBlock('photoblog_portrait/item');
                    $tpl->SetVariable('thumb', $GLOBALS['app']->getDataURL('phoo/' . $entry['thumb']));
                    $url = $this->gadget->GetURLFor('PhotoblogPortrait', array('photoid' => $entry['id']));
                    $tpl->SetVariable('url', $url);
                    $tpl->SetVariable('title', $entry['name']);
                    $tpl->SetVariable('description', $this->gadget->ParseText($entry['description']));
                    $tpl->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $tpl->SetVariable('width',  $imgData[0]);
                    $tpl->SetVariable('height', $imgData[1]);
                    $tpl->ParseBlock('photoblog_portrait/item');
                } else {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['medium']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $tpl->SetBlock('photoblog_portrait/main');
                    $tpl->SetVariable('medium', $GLOBALS['app']->getDataURL('phoo/' . $entry['medium']));
                    $tpl->SetVariable('url', $GLOBALS['app']->getDataURL('phoo/' . $entry['image']));
                    $tpl->SetVariable('title', $entry['name']);
                    $tpl->SetVariable('description', $this->gadget->ParseText($entry['description']));
                    $tpl->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $tpl->SetVariable('width',  $imgData[0]);
                    $tpl->SetVariable('height', $imgData[1]);
                    $tpl->ParseBlock('photoblog_portrait/main');
                }
                $first = false;
            } else {
                if ($photoid == $entry['id']) {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['medium']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $tpl->SetBlock('photoblog_portrait/main');
                    $tpl->SetVariable('medium', $GLOBALS['app']->getDataURL('phoo/' . $entry['medium']));
                    $tpl->SetVariable('url', $GLOBALS['app']->getDataURL('phoo/' . $entry['image']));
                    $tpl->SetVariable('title', $entry['name']);
                    $tpl->SetVariable('description', $this->gadget->ParseText($entry['description']));
                    $tpl->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $tpl->SetVariable('width',  $imgData[0]);
                    $tpl->SetVariable('height', $imgData[1]);
                    $tpl->ParseBlock('photoblog_portrait/main');
                } else {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['thumb']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $tpl->SetBlock('photoblog_portrait/item');
                    $tpl->SetVariable('thumb', $GLOBALS['app']->getDataURL('phoo/' . $entry['thumb']));
                    $url = $this->gadget->GetURLFor('PhotoblogPortrait', array('photoid' => $entry['id']));
                    $tpl->SetVariable('url', $url);
                    $tpl->SetVariable('title', $entry['name']);
                    $tpl->SetVariable('description', $this->gadget->ParseText($entry['description']));
                    $tpl->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $tpl->SetVariable('width',  $imgData[0]);
                    $tpl->SetVariable('height', $imgData[1]);
                    $tpl->ParseBlock('photoblog_portrait/item');
                }
            }
        }
        $tpl->ParseBlock('photoblog_portrait');
        return $tpl->Get();
    }

    /**
     * Displays a preview of the given phoo comment
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Preview()
    {
        $request =& Jaws_Request::getInstance();
        $names = array(
            'name', 'email', 'url', 'title', 'comments', 'createtime',
            'ip_address', 'reference', 'albumid'
        );
        $post = $request->get($names, 'post');
        $post['parent_id'] = (int)$post['parent_id'];
        $post['albumid']   = (int)$post['albumid'];
        $GLOBALS['app']->Session->PushSimpleResponse($post, 'Phoo_Comment');

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $image = $model->GetImage($post['parent_id'], $post['albumid']);
        if (Jaws_Error::isError($image)) {
            $GLOBALS['app']->Session->PushSimpleResponse($image->getMessage(), 'Phoo');
            Jaws_Header::Location($this->gadget->GetURLFor('DefaultAction'));
        }

        return $this->ViewImage($post['reference'], $post['albumid'], true);
    }

    /**
     * Format a date using Jaws
     *
     * @access  public
     * @param   string  $date   The data to format.
     * @return  string  The formatted date.
     */
    function FormatDate($date)
    {
        $date = $GLOBALS['app']->loadDate();
        return $date->Format($date);
    }

    /**
     * Resize an image on the fly
     * 
     * FIXME: I don't know if is better to get it as a standalone function...
     * 
     * @returns binary Image resized
     */
    function Thumb()
    {
        $request =& Jaws_Request::getInstance();
        $image   = $request->get('image', 'get');

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        Jaws_Image::get_exif_thumbnail(JAWS_DATA . 'phoo/import/' . $image, 'gadgets/Phoo/images/Phoo.png');
    }

}