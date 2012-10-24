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
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PhooHTML extends Jaws_Gadget_HTML
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
        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('ViewAlbumPage.html');
        $t->SetBlock('ViewAlbumPage');

        $request =& Jaws_Request::getInstance();
        $get     = $request->get(array('id', 'page'), 'get');

        $id = !empty($get['id'])? $get['id'] : '0';
        $page = !empty($get['page'])? (int) $get['page'] : 1;

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $album = $model->GetAlbumImages($id, $page);
        if (!Jaws_Error::IsError($album) && !empty($album) && $album['published']) {
            // display album info
            if ($id == '0') {
                $t->SetVariable('title', _t('PHOO_UNKNOW_ALBUM'));
                $this->SetTitle(_t('PHOO_UNKNOW_ALBUM'));
                $t->SetVariable('description', '');
            } else {
                $t->SetVariable('title', $album['name']);
                $this->SetTitle($album['name']);
                $t->SetVariable('description', $this->ParseText($album['description'], 'Phoo'));
            }

            // display images
            $t->SetBlock('ViewAlbumPage/photos');
            if (isset($album['images']) && is_array($album['images'])) {
                require_once JAWS_PATH . 'include/Jaws/Image.php';
                foreach ($album['images'] as $image) {
                    if ($image['published'] === true) {
                        $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $image['thumb']);
                        if (Jaws_Error::IsError($imgData)) {
                            continue;
                        }
                        $t->SetBlock('ViewAlbumPage/photos/item');
                        $url = $this->GetURLFor('ViewImage', array('id' => $image['id'], 'albumid' => $image['albumid']));
                        $t->SetVariable('url',      $url);
                        $t->SetVariable('thumb',    $GLOBALS['app']->getDataURL('phoo/' . $image['thumb']));
                        $t->SetVariable('medium',   $GLOBALS['app']->getDataURL('phoo/' . $image['medium']));
                        $t->SetVariable('image',    $GLOBALS['app']->getDataURL('phoo/' . $image['image']));
                        $t->SetVariable('name',     $image['name']);
                        $t->SetVariable('filename', $image['filename']);
                        $t->SetVariable('img_desc', $image['stripped_description']);
                        $t->SetVariable('width',    $imgData[0]);
                        $t->SetVariable('height',   $imgData[1]);
                        $t->ParseBlock('ViewAlbumPage/photos/item');
                    }
                }
            }
            $t->ParseBlock('ViewAlbumPage/photos');

            // Pager
            $pager = $model->GetAlbumPagerNumbered($id, $page);

            if (count($pager) > 0) {
                $t->SetBlock('ViewAlbumPage/pager');
                $t->SetVariable('total', _t('PHOO_PHOTOS_COUNT', $pager['total']));

                $pager_view = '';
                foreach ($pager as $k => $v) {
                    $t->SetBlock('ViewAlbumPage/pager/item');
                    if ($k == 'next') {
                        if ($v) {
                            $t->SetBlock('ViewAlbumPage/pager/item/next');
                            $t->SetVariable('lbl_next', _t('PHOO_NEXT'));
                            $url = $this->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
                            $t->SetVariable('url_next', $url);
                            $t->ParseBlock('ViewAlbumPage/pager/item/next');
                        } else {
                            $t->SetBlock('ViewAlbumPage/pager/item/no_next');
                            $t->SetVariable('lbl_next', _t('PHOO_NEXT'));
                            $t->ParseBlock('ViewAlbumPage/pager/item/no_next');
                        }
                    } elseif ($k == 'previous') {
                        if ($v) {
                            $t->SetBlock('ViewAlbumPage/pager/item/previous');
                            $t->SetVariable('lbl_previous', _t('PHOO_PREVIOUS'));
                            $url = $this->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
                            $t->SetVariable('url_previous', $url);
                            $t->ParseBlock('ViewAlbumPage/pager/item/previous');
                        } else {
                            $t->SetBlock('ViewAlbumPage/pager/item/no_previous');
                            $t->SetVariable('lbl_previous', _t('PHOO_PREVIOUS'));
                            $t->ParseBlock('ViewAlbumPage/pager/item/no_previous');
                        }
                    } elseif ($k == 'separator1' || $k == 'separator2') {
                        $t->SetBlock('ViewAlbumPage/pager/item/page_separator');
                        $t->ParseBlock('ViewAlbumPage/pager/item/page_separator');
                    } elseif ($k == 'current') {
                        $t->SetBlock('ViewAlbumPage/pager/item/page_current');
                        $url = $this->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
                        $t->SetVariable('lbl_page', $v);
                        $t->SetVariable('url_page', $url);
                        $t->ParseBlock('ViewAlbumPage/pager/item/page_current');
                    } elseif ($k != 'total' && $k != 'next' && $k != 'previous') {
                        $t->SetBlock('ViewAlbumPage/pager/item/page_number');
                        $url = $this->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
                        $t->SetVariable('lbl_page', $v);
                        $t->SetVariable('url_page', $url);
                        $t->ParseBlock('ViewAlbumPage/pager/item/page_number');
                    }
                    $t->ParseBlock('ViewAlbumPage/pager/item');
                }
                $t->ParseBlock('ViewAlbumPage/pager');
            }
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }

        $t->ParseBlock('ViewAlbumPage');
        return $t->Get();
    }

    /**
     * Displays an individual image.
     *
     * @access  public
     * @param   int     $id                 image ID
     * @param   int     $albumid            album ID
     * @param   bool    $preview_mode       preview mode
     * @param   string  $reply_to_comment   
     * @return  string   XHTML template content
     */
    function ViewImage($id = null, $albumid = null, $preview_mode = false, $reply_to_comment = '')
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('ViewImage.html');

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
        $t->SetBlock('ViewImage');
        $t->SetVariable('title',       $image['name']);
        $t->SetVariable('posted_by',   _t('PHOO_POSTED_BY'));
        $t->SetVariable('img_author',  $image['author']);
        $t->SetVariable('name',        $image['name']);
        $t->SetVariable('filename',    $image['filename']);
        $t->SetVariable('img_desc',    $image['stripped_description']);
        $t->SetVariable('albumid',     $albumid);
        $t->SetVariable('description', $this->ParseText($image['description'], 'Phoo'));
        $t->SetVariable('medium',      $GLOBALS['app']->getDataURL('phoo/' . $image['medium']));
        $t->SetVariable('image',       $GLOBALS['app']->getDataURL('phoo/' . $image['image']));
        $t->SetVariable('width',       $imgData[0]);
        $t->SetVariable('height',      $imgData[1]);

        // show if the original was kept
        $settings = $model->GetSettings();
        if ($settings['keep_original'] == 'true') {
            $t->SetVariable('url', $GLOBALS['app']->getDataURL('phoo/' . $image['image']));
        } else {
            $t->SetVariable('url', 'javascript: void();');
        }

        $allow_comments_config = $GLOBALS['app']->Registry->Get('/config/allow_comments');
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
                          $GLOBALS['app']->Registry->Get('/gadgets/Phoo/allow_comments') == 'true' &&
                          $allow_comments_config;

        if (empty($reply_to_comment)) {
            $t->SetVariable('comments', $this->ShowComments($image['id'], $albumid, 0, 0, 1, 1));
            if ($allow_comments) {
                if ($preview_mode) {
                    $t->SetVariable('preview', $this->ShowPreview());
                }
                $t->SetVariable('comment-form', $this->DisplayCommentForm($image['id'], $albumid, 0, _t('GLOBAL_RE').$image['name']));
            } elseif ($restricted) {
                $login_url    = $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox');
                $register_url = $GLOBALS['app']->Map->GetURLFor('Users', 'Registration');
                $t->SetVariable('comment-form', _t('GLOBAL_COMMENTS_RESTRICTED', $login_url, $register_url));
            }
        } else {
            $t->SetVariable('comments', $this->ShowSingleComment($reply_to_comment));
            if ($allow_comments) {
                if ($preview_mode) {
                    $t->SetVariable('preview', $this->ShowPreview());
                }
                $title  = $image['name'];
                $comment = $model->GetComment($reply_to_comment);
                if (!Jaws_Error::IsError($comment)) {
                    $title  = $comment['title'];
                }
                $t->SetVariable('comment-form', $this->DisplayCommentForm($image['id'], $albumid, $reply_to_comment, _t('GLOBAL_RE'). $title));
            } elseif ($restricted) {
                $login_url    = $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox');
                $register_url = $GLOBALS['app']->Map->GetURLFor('Users', 'Registration');
                $t->SetVariable('comment-form', _t('GLOBAL_COMMENTS_RESTRICTED', $login_url, $register_url));
            }
        }

        // Pager
        $t->SetBlock('ViewImage/navigation');
        $t->SetVariable('lbl_page_counter', _t('PHOO_PHOTO_COUNTER', $image['pos'], $image['total']));
        $t->SetVariable('lbl_thumbs', _t('PHOO_THUMBS'));
        $url = $this->GetURLFor('ViewAlbum', array('id' => $albumid));
        $t->SetVariable('url_thumbs', $url);

        if ($image['first'] != $image['id']) {
            $t->SetBlock('ViewImage/navigation/no-first-photo');
            $t->SetVariable('lbl_first', _t('PHOO_FIRST'));
            $url = $this->GetURLFor('ViewImage', array('id' => $image['first'], 'albumid' => $albumid));
            $t->SetVariable('url_first', $url);
            $t->SetVariable('lbl_prev', _t('PHOO_PREVIOUS'));
            $url = $this->GetURLFor('ViewImage', array('id' => $image['previous'], 'albumid' => $albumid));
            $t->SetVariable('url_prev', $url);
            $t->ParseBlock('ViewImage/navigation/no-first-photo');
        } else {
            $t->SetBlock('ViewImage/navigation/first-photo');
            $t->SetVariable('lbl_first', _t('PHOO_FIRST'));
            $t->SetVariable('lbl_prev',  _t('PHOO_PREVIOUS'));
            $t->ParseBlock('ViewImage/navigation/first-photo');
        }

        if ($image['last'] != $image['id']) {
            $t->SetBlock('ViewImage/navigation/no-last-photo');
            $t->SetVariable('lbl_next', _t('PHOO_NEXT'));
            $url = $this->GetURLFor('ViewImage', array('id' => $image['next'], 'albumid' => $albumid));
            $t->SetVariable('url_next', $url);
            $t->SetVariable('lbl_last', _t('PHOO_LAST'));
            $url = $this->GetURLFor('ViewImage', array('id' => $image['last'], 'albumid' => $albumid));
            $t->SetVariable('url_last', $url);
            $t->ParseBlock('ViewImage/navigation/no-last-photo');
        } else {
            $t->SetBlock('ViewImage/navigation/last-photo');
            $t->SetVariable('lbl_next', _t('PHOO_NEXT'));
            $t->SetVariable('lbl_last', _t('PHOO_LAST'));
            $t->ParseBlock('ViewImage/navigation/last-photo');
        }

        $t->ParseBlock('ViewImage/navigation');

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

            $t->SetBlock('ViewImage/exif');
            $t->SetVariable('exif_info', _t('PHOO_EXIF_INFO'));
            $t->SetVariable('cameraimg', $image['exif']['cameraimg']);
            if (!empty($image['exif']['camera']))  {
                $t->SetVariable('camera', $image['exif']['camera']);
            } else {
                $t->SetVariable('camera', _t('PHOO_UNKNOWN_CAM'));
            }
            $t->SetVariable('data', $datatext);
            $t->ParseBlock('ViewImage/exif');
        }
        $t->ParseBlock('ViewImage');

        return $t->Get();
    }

    /**
     * I'm not sure what this does... gets the authors photo maybe?
     *
     * @access  public
     * @see PhooModel::GetAsPortrait()
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
        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('Photoblog.html');
        $t->SetBlock('photoblog_portrait');
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

                    $t->SetBlock('photoblog_portrait/item');
                    $t->SetVariable('thumb', $GLOBALS['app']->getDataURL('phoo/' . $entry['thumb']));
                    $url = $this->GetURLFor('PhotoblogPortrait', array('photoid' => $entry['id']));
                    $t->SetVariable('url', $url);
                    $t->SetVariable('title', $entry['name']);
                    $t->SetVariable('description', $this->ParseText($entry['description'], 'Phoo'));
                    $t->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $t->SetVariable('width',  $imgData[0]);
                    $t->SetVariable('height', $imgData[1]);
                    $t->ParseBlock('photoblog_portrait/item');
                } else {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['medium']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $t->SetBlock('photoblog_portrait/main');
                    $t->SetVariable('medium', $GLOBALS['app']->getDataURL('phoo/' . $entry['medium']));
                    $t->SetVariable('url', $GLOBALS['app']->getDataURL('phoo/' . $entry['image']));
                    $t->SetVariable('title', $entry['name']);
                    $t->SetVariable('description', $this->ParseText($entry['description'], 'Phoo'));
                    $t->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $t->SetVariable('width',  $imgData[0]);
                    $t->SetVariable('height', $imgData[1]);
                    $t->ParseBlock('photoblog_portrait/main');
                }
                $first = false;
            } else {
                if ($photoid == $entry['id']) {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['medium']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $t->SetBlock('photoblog_portrait/main');
                    $t->SetVariable('medium', $GLOBALS['app']->getDataURL('phoo/' . $entry['medium']));
                    $t->SetVariable('url', $GLOBALS['app']->getDataURL('phoo/' . $entry['image']));
                    $t->SetVariable('title', $entry['name']);
                    $t->SetVariable('description', $this->ParseText($entry['description'], 'Phoo'));
                    $t->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $t->SetVariable('width',  $imgData[0]);
                    $t->SetVariable('height', $imgData[1]);
                    $t->ParseBlock('photoblog_portrait/main');
                } else {
                    $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $entry['thumb']);
                    if (Jaws_Error::IsError($imgData)) {
                        continue;
                    }

                    $t->SetBlock('photoblog_portrait/item');
                    $t->SetVariable('thumb', $GLOBALS['app']->getDataURL('phoo/' . $entry['thumb']));
                    $url = $this->GetURLFor('PhotoblogPortrait', array('photoid' => $entry['id']));
                    $t->SetVariable('url', $url);
                    $t->SetVariable('title', $entry['name']);
                    $t->SetVariable('description', $this->ParseText($entry['description'], 'Phoo'));
                    $t->SetVariable('createtime',  $date->Format($entry['createtime']));
                    $t->SetVariable('width',  $imgData[0]);
                    $t->SetVariable('height', $imgData[1]);
                    $t->ParseBlock('photoblog_portrait/item');
                }
            }
        }
        $t->ParseBlock('photoblog_portrait');
        return $t->Get();
    }

    /**
     * Recursively displays comments of a given image according to several parameters
     *
     * @access  public
     * @param   int     $id             image id
     * @param   int     $albumid        album id
     * @param   int     $parent         parent comment id
     * @param   int     $level          deep level on thread
     * @param   int     $thread         1 to show full thread
     * @param   int     $reply_link     1 to show reply-to link
     * @param   array   $data           Array with comments data if null it's loaded from model.
     * @return  string  XHTML template content
     */
    function ShowComments($id, $albumid, $parent, $level, $thread, $reply_link, $data = null)
    {
        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('Comment.html');
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        if (is_null($data)) {
            $comments = $model->GetComments($id, null);
        } else {
            $comments = $data;
        }

        if (!Jaws_Error::IsError($comments)) {

            $date = $GLOBALS['app']->loadDate();
            $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            foreach ($comments as $c) {
                $tpl->SetBlock('comment');
                $tpl->SetVariable('id', $c['id']);
                $tpl->SetVariable('parent_id', $c['gadget_reference']);
                $tpl->SetVariable('name', $xss->filter($c['name']));
                $email = $xss->filter($c['email']);

                $GLOBALS['app']->Registry->LoadFile('Policy');
                $_obfuscator = $GLOBALS['app']->Registry->Get('/gadgets/Policy/obfuscator');
                if (($_obfuscator != 'DISABLED') && (!empty($email))){
                    require_once JAWS_PATH . 'gadgets/Policy/obfuscators/' . $_obfuscator . '.php';
                    $obf = new $_obfuscator();
                    $tpl->SetVariable('email', $obf->Get($email, _t('GLOBAL_EMAIL')));
                } elseif (empty($email)) {
                    $tpl->SetVariable('email', '');
                } else {
                    $tpl->SetVariable('email', '<a href="mailto:' . $email . '">' . _t('GLOBAL_EMAIL') . '</a>');
                }
                $tpl->SetVariable('url', $xss->filter($c['url']));
                $tpl->SetVariable('ip_address', '127.0.0.1');
                $tpl->SetVariable('avatar_source', $c['avatar_source']);
                $tpl->SetVariable('title', $xss->filter($c['title']));
                $tpl->SetVariable('replies', $c['replies']);
                $tpl->SetVariable('commentname', 'comment'.$c['id']);
                $commentsText = $this->ParseText($c['msg_txt']);
                $tpl->SetVariable('comments', $commentsText);
                $tpl->SetVariable('createtime',           $date->Format($c['createtime']));
                $tpl->SetVariable('createtime-monthname', $date->Format($c['createtime'], 'MN'));
                $tpl->SetVariable('createtime-month',     $date->Format($c['createtime'], 'm'));
                $tpl->SetVariable('createtime-day',       $date->Format($c['createtime'], 'd'));
                $tpl->SetVariable('createtime-year',      $date->Format($c['createtime'], 'Y'));
                $tpl->SetVariable('createtime-time',      $date->Format($c['createtime'], 'g:ia'));
                if ($c['status'] == 'spam') {
                    $tpl->SetVariable('status_message', _t('PHOO_COMMENT_IS_SPAM'));
                } elseif ($c['status'] == 'waiting') {
                    $tpl->SetVariable('status_message', _t('PHOO_COMMENT_IS_WAITING'));
                } else {
                    $tpl->SetVariable('status_message', '&nbsp;');
                }
                $tpl->SetVariable('level', $level);
                if ($reply_link === 1) {
                    $tpl->SetBlock('comment/reply-link');
                    $tpl->SetVariablesArray($c);
                    $tpl->SetVariable('reply-link', '<a href="'.
                                      $this->GetURLFor('Reply', array('id' => $c['id'],
                                                                      'photoid' => $c['gadget_reference'],
                                                                      'albumid' => $albumid)).'">'.
                                      _t('PHOO_REPLY').'</a>');

                    $tpl->ParseBlock('comment/reply-link');
                }

                if (count($c['childs']) > 0) {
                    $tpl->SetBlock('comment/thread');
                    $tpl->SetVariable('thread', $this->ShowComments($id, $albumid, $c['id'], $level + 1, $thread, $reply_link, $c['childs']));
                    $tpl->ParseBlock('comment/thread');
                }
                $tpl->ParseBlock('comment');
            }
        }

        return $tpl->Get();
    }


    /**
     * Displays a given phoo comment
     *
     * @access  public
     * @param   int     $id     comment id
     * @return  string  XHTML template content
     */
    function ShowSingleComment($id)
    {
        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('Comment.html');
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $comment = $model->GetComment($id);
        if (!Jaws_Error::IsError($comment)) {
            $date = $GLOBALS['app']->loadDate();
            $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            $tpl->SetBlock('comment');
            $tpl->SetVariable('id', $comment['id']);
            $tpl->SetVariable('parent_id', $comment['gadget_reference']);
            $tpl->SetVariable('name',  $xss->filter($comment['name']));
            $tpl->SetVariable('email', $xss->filter($comment['email']));
            $tpl->SetVariable('url',   $xss->filter($comment['url']));
            $tpl->SetVariable('title', $xss->filter($comment['title']));
            $tpl->SetVariable('ip_address', '127.0.0.1');
            $tpl->SetVariable('status_message', '&nbsp;');
            $tpl->SetVariable('avatar_source', $comment['avatar_source']);
            $tpl->SetVariable('replies', $comment['replies']);
            $tpl->SetVariable('commentname', 'comment' . $comment['id']);
            $commentsText = $this->ParseText($comment['msg_txt']);
            $tpl->SetVariable('comments', $commentsText);
            $tpl->SetVariable('createtime',           $date->Format($comment['createtime']));
            $tpl->SetVariable('createtime-monthname', $date->Format($comment['createtime'], 'MN'));
            $tpl->SetVariable('createtime-month',     $date->Format($comment['createtime'], 'm'));
            $tpl->SetVariable('createtime-day',       $date->Format($comment['createtime'], 'd'));
            $tpl->SetVariable('createtime-year',      $date->Format($comment['createtime'], 'Y'));
            $tpl->SetVariable('createtime-time',      $date->Format($comment['createtime'], 'g:ia'));
            $tpl->SetVariable('level', 0);
            $tpl->ParseBlock('comment');

//             $tpl->SetBlock('comment');
//             $tpl->SetVariablesArray($comment);
//             $tpl->SetVariable('comments', $this->ParseText($comment['msg_txt']));
//             $tpl->SetVariable('level', 0);
//             $tpl->ParseBlock('comment');
        }
        return $tpl->Get();
    }
    /**
     * Displays a given phoo comments and a form for replying
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Reply()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('id', 'photoid', 'albumid'), 'get');
        return $this->ViewImage((int)$post['photoid'], (int)$post['albumid'], false, (int)$post['id']);
    }

    /**
     * Displays a form to send a comment to the phoo
     *
     * @access  public
     * @param   int     $parent_id  id of the replied item(immediately before on the thread)
     * @param   int     $albumid    album ID
     * @param   int     $parent     id of the replied entry(comment thread starter)
     * @param   string  $title      title of the comment
     * @param   string  $comment    body of the comment(optional, empty by default)
     * @return  string  XHTML template content
     */
    function DisplayCommentForm($parent_id, $albumid, $parent = 0, $title = '', $comments = '')
    {
        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('CommentForm.html');
        $tpl->SetBlock('commentform');

        $post = $GLOBALS['app']->Session->PopSimpleResponse('Phoo_Comment');

        if (!$GLOBALS['app']->Session->Logged()) {
            $tpl->SetBlock('commentform/unregistered');
            // Get person info from cookie or post...
            if (!is_null($post['name'])) {
                $visitorName = $post['name'];
            } elseif ($GLOBALS['app']->Session->GetCookie('visitor_name')) {
                $visitorName = $GLOBALS['app']->Session->GetCookie('visitor_name');
            } else {
                $visitorName = '';
            }

            if (!is_null($post['email'])) {
                $visitorEmail = $post['email'];
            } elseif ($GLOBALS['app']->Session->GetCookie('visitor_email')) {
                $visitorEmail = $GLOBALS['app']->Session->GetCookie('visitor_email');
            } else {
                $visitorEmail = '';
            }

            if (!is_null($post['url'])) {
                $visitorUrl = $post['url'];
            } elseif ($GLOBALS['app']->Session->GetCookie('visitor_url')) {
                $visitorUrl = $GLOBALS['app']->Session->GetCookie('visitor_url');
            } else {
                $visitorUrl = 'http://';
            }

            $tpl->SetVariable('name', _t('GLOBAL_NAME'));
            $tpl->SetVariable('name_value', $visitorName);
            $tpl->SetVariable('email', _t('GLOBAL_EMAIL'));
            $tpl->SetVariable('email_value', $visitorEmail);
            $tpl->SetVariable('url',  _t('GLOBAL_URL'));
            $tpl->SetVariable('url_value', $visitorUrl);
            $tpl->ParseBlock('commentform/unregistered');
        }

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        if ($mPolicy->LoadCaptcha($captcha, $entry, $label, $description)) {
            $tpl->SetBlock('commentform/captcha');
            $tpl->SetVariable('lbl_captcha', $label);
            $tpl->SetVariable('captcha', $captcha);
            if (!empty($entry)) {
                $tpl->SetVariable('captchavalue', $entry);
            }
            $tpl->SetVariable('captcha_msg', $description);
            $tpl->ParseBlock('commentform/captcha');
        }

        if (!is_null($post['title'])) {
            $title = $post['title'];
        }

        if (!is_null($post['comments'])) {
            $comments = $post['comments'];
        }

        if (!is_null($post['parent'])) {
            $parent = $post['parent'];
        }

        $tpl->SetVariable('title', _t('PHOO_LEAVE_COMMENT'));
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('parent_id',   $parent_id);
        $tpl->SetVariable('albumid', $albumid);
        $tpl->SetVariable('parent', $parent);
        $tpl->SetVariable('action', 'SaveComment');

        // Test to see if this does any good to reduce spam
        $tpl->SetVariable('url2', _t('GLOBAL_SPAMCHECK_EMPTY'));
        $tpl->SetVariable('url2_value',  '');
        $tpl->SetVariable('comment_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title_value', $title);
        $tpl->SetVariable('comments', _t('PHOO_COMMENT'));
        $tpl->SetVariable('comments_value', $comments);

        $tpl->SetVariable('send',    _t('PHOO_SEND_COMMENT'));
        $tpl->SetVariable('preview',    _t('GLOBAL_PREVIEW'));

        /*
        if ($GLOBALS['app']->Registry->Get('/network/mailer') !== 'DISABLED') {
            $tpl->SetBlock('commentform/mail_me');
            $tpl->SetVariable('mail_me', _t('PHOO_MAIL_COMMENT_TO_ME'));
            $tpl->ParseBlock('commentform/mail_me');
        }
        */

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Phoo')) {
            $tpl->SetBlock('commentform/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('commentform/response');
        }

        $tpl->ParseBlock('commentform');

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
            'ip_address', 'parent_id', 'parent', 'albumid'
        );
        $post = $request->get($names, 'post');
        $post['parent_id'] = (int)$post['parent_id'];
        $post['albumid']   = (int)$post['albumid'];
        $GLOBALS['app']->Session->PushSimpleResponse($post, 'Phoo_Comment');

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $image = $model->GetImage($post['parent_id'], $post['albumid']);
        if (Jaws_Error::isError($image)) {
            $GLOBALS['app']->Session->PushSimpleResponse($image->getMessage(), 'Phoo');
            Jaws_Header::Location($this->GetURLFor('DefaultAction'), true);
        }

        return $this->ViewImage($post['parent_id'], $post['albumid'], true);
    }

    /**
     * Displays a preview of the given phoo comment
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ShowPreview()
    {
        $post = $GLOBALS['app']->Session->PopSimpleResponse('Phoo_Comment', false);
        if ($GLOBALS['app']->Session->Logged()) {
            $post['name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('Comment.html');
        $tpl->SetBlock('comment');
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        $tpl->SetVariable('name',  $xss->filter($post['name']));
        $tpl->SetVariable('email', $xss->filter($post['email']));
        $tpl->SetVariable('url',   $xss->filter($post['url']));
        if (is_null($post['ip_address'])) {
            $post['ip_address'] = $_SERVER['REMOTE_ADDR'];
        }
        $tpl->SetVariable('title', $xss->filter($post['title']));
        $tpl->SetVariable('comments', $this->ParseText($post['comments']));
        if (!isset($post['createtime'])) {
            $date = $GLOBALS['app']->loadDate();
            $post['createtime'] = $date->Format(time());
        }
        $tpl->SetVariable('createtime', $post['createtime']);
        $tpl->SetVariable('level', 0);
        $tpl->SetVariable('status_message', '&nbsp;');
        $tpl->SetVariable('ip_address', $post['ip_address']);
        $tpl->SetVariable('avatar_source', 'images/unknown.png');
        $tpl->SetVariable('replies', '0');
        $tpl->SetVariable('commentname', 'comment_preview');

        $tpl->ParseBlock('comment');
        return $tpl->Get();
    }

    /**
     * Saves the given phoo comment
     *
     * @access  public
     * @return  void
     */
    function SaveComment()
    {
        $request =& Jaws_Request::getInstance();
        $names = array(
            'name', 'email', 'url', 'title', 'comments', 'createtime',
            'ip_address', 'parent_id', 'parent', 'url2', 'albumid',
        );
        $post = $request->get($names, 'post');
        $post['parent_id'] = (int)$post['parent_id'];
        $post['albumid']   = (int)$post['albumid'];

        if ($GLOBALS['app']->Session->Logged()) {
            $post['name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $image = $model->GetImage($post['parent_id'], $post['albumid']);
        if (Jaws_Error::isError($image)) {
            $GLOBALS['app']->Session->PushSimpleResponse($entry->getMessage(), 'Phoo');
            Jaws_Header::Location($this->GetURLFor('DefaultAction'), true);
        }

        $url = $this->GetURLFor('ViewImage', array('id' => $post['parent_id'], 'albumid' => $post['albumid']));

        $allow_comments_config = $GLOBALS['app']->Registry->Get('/config/allow_comments');
        $restricted = $allow_comments_config == 'restricted';
        $allow_comments_config = $restricted? $GLOBALS['app']->Session->Logged() : ($allow_comments_config == 'true');

        // Check if comments are allowed.
        if ($image['allow_comments'] !== true ||
            $image['album_allow_comments'] !== true ||
            $GLOBALS['app']->Registry->Get('/gadgets/Phoo/allow_comments') != 'true' ||
            !$allow_comments_config)
        {
            Jaws_Header::Location($url, true);
        }

        if (trim($post['name']) == '' || trim($post['title']) == '' || trim($post['comments']) == '') {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('GLOBAL_ERROR_INCOMPLETE_FIELDS'), 'Phoo');
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Phoo_Comment');
            Jaws_Header::Location($url, true);
        }

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $resCheck = $mPolicy->CheckCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushSimpleResponse($resCheck->getMessage(), 'Phoo');
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Phoo_Comment');
            Jaws_Header::Location($url, true);
        }

        $result = $model->NewComment($post['name'], $post['title'], $post['url'],
                                     $post['email'], $post['comments'], $post['parent'], $post['parent_id'], $url);
        if (Jaws_Error::isError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($result->getMessage(), 'Phoo');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('GLOBAL_MESSAGE_SENT'), 'Phoo');
        }

        Jaws_Header::Location($url, true);
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