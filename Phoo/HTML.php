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
                $t->SetVariable('description', $this->gadget->ParseText($album['description']));
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
                        $url = $this->gadget->GetURLFor('ViewImage', array('id' => $image['id'], 'albumid' => $image['albumid']));
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
                            $url = $this->gadget->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
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
                            $url = $this->gadget->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
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
                        $url = $this->gadget->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
                        $t->SetVariable('lbl_page', $v);
                        $t->SetVariable('url_page', $url);
                        $t->ParseBlock('ViewAlbumPage/pager/item/page_current');
                    } elseif ($k != 'total' && $k != 'next' && $k != 'previous') {
                        $t->SetBlock('ViewAlbumPage/pager/item/page_number');
                        $url = $this->gadget->GetURLFor('ViewAlbumPage', array('id' => $image['albumid'], 'page' => $v));
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
     * @return  string   XHTML template content
     */
    function ViewImage($id = null, $albumid = null, $preview_mode = false)
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
        $t->SetVariable('description', $this->gadget->ParseText($image['description']));
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
            $t->SetVariable('comments', $cHTML->ShowComments('Phoo', 'photo', $image['id'],
                            array('action' => 'ViewImage',
                                  'params' => array('albumid' => $albumid, 'id' => $image['id']))));

            if ($allow_comments) {
                if ($preview_mode) {
                    $t->SetVariable('preview', $this->ShowPreview());
                }
                $t->SetVariable('comment-form', $cHTML->ShowCommentsForm('Phoo', 'photo', $image['id'], $redirect_to));
            } elseif ($restricted) {
                $login_url = $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox');
                $register_url = $GLOBALS['app']->Map->GetURLFor('Users', 'Registration');
                $t->SetVariable('comment-form', _t('GLOBAL_COMMENTS_RESTRICTED', $login_url, $register_url));
            }

        }

        // Pager
        $t->SetBlock('ViewImage/navigation');
        $t->SetVariable('lbl_page_counter', _t('PHOO_PHOTO_COUNTER', $image['pos'], $image['total']));
        $t->SetVariable('lbl_thumbs', _t('PHOO_THUMBS'));
        $url = $this->gadget->GetURLFor('ViewAlbum', array('id' => $albumid));
        $t->SetVariable('url_thumbs', $url);

        if ($image['first'] != $image['id']) {
            $t->SetBlock('ViewImage/navigation/no-first-photo');
            $t->SetVariable('lbl_first', _t('PHOO_FIRST'));
            $url = $this->gadget->GetURLFor('ViewImage', array('id' => $image['first'], 'albumid' => $albumid));
            $t->SetVariable('url_first', $url);
            $t->SetVariable('lbl_prev', _t('PHOO_PREVIOUS'));
            $url = $this->gadget->GetURLFor('ViewImage', array('id' => $image['previous'], 'albumid' => $albumid));
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
            $url = $this->gadget->GetURLFor('ViewImage', array('id' => $image['next'], 'albumid' => $albumid));
            $t->SetVariable('url_next', $url);
            $t->SetVariable('lbl_last', _t('PHOO_LAST'));
            $url = $this->gadget->GetURLFor('ViewImage', array('id' => $image['last'], 'albumid' => $albumid));
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
                    $url = $this->gadget->GetURLFor('PhotoblogPortrait', array('photoid' => $entry['id']));
                    $t->SetVariable('url', $url);
                    $t->SetVariable('title', $entry['name']);
                    $t->SetVariable('description', $this->gadget->ParseText($entry['description']));
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
                    $t->SetVariable('description', $this->gadget->ParseText($entry['description']));
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
                    $t->SetVariable('description', $this->gadget->ParseText($entry['description']));
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
                    $url = $this->gadget->GetURLFor('PhotoblogPortrait', array('photoid' => $entry['id']));
                    $t->SetVariable('url', $url);
                    $t->SetVariable('title', $entry['name']);
                    $t->SetVariable('description', $this->gadget->ParseText($entry['description']));
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
     * @param   int     $reply_link     1 to show reply-to link
     * @param   array   $data           Array with comments data if null it's loaded from model.
     * @return  string  XHTML template content
     */
    function ShowComments($id, $albumid, $reply_link, $data = null)
    {
        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('Comment.html');
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        if (is_null($data)) {
            $comments = $model->GetComments($id);
        } else {
            $comments = $data;
        }

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $userModel = new Jaws_User();

        if (!Jaws_Error::IsError($comments)) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($comments as $c) {
                $tpl->SetBlock('comment');
                $tpl->SetVariable('id', $c['id']);
                $tpl->SetVariable('parent_id', $c['reference']);
                $tpl->SetVariable('name', $c['name']);
                $email = $c['email'];

                $_obfuscator = $this->gadget->registry->fetch('obfuscator', 'Policy');
                if (($_obfuscator != 'DISABLED') && (!empty($email))) {
                    require_once JAWS_PATH . 'gadgets/Policy/obfuscators/' . $_obfuscator . '.php';
                    $obf = new $_obfuscator();
                    $tpl->SetVariable('email', $obf->Get($email, _t('GLOBAL_EMAIL')));
                } elseif (empty($email)) {
                    $tpl->SetVariable('email', '');
                } else {
                    $tpl->SetVariable('email', '<a href="mailto:' . $email . '">' . _t('GLOBAL_EMAIL') . '</a>');
                }
                $tpl->SetVariable('url', $c['url']);
                $tpl->SetVariable('ip_address', '127.0.0.1');
                $tpl->SetVariable('avatar_source', $c['avatar_source']);
                $tpl->SetVariable('commentname', 'comment'.$c['id']);
                $commentsText = Jaws_String::AutoParagraph($c['msg_txt']);
                $tpl->SetVariable('comments', $commentsText);
                $tpl->SetVariable('createtime',           $date->Format($c['createtime']));
                $tpl->SetVariable('createtime-monthname', $date->Format($c['createtime'], 'MN'));
                $tpl->SetVariable('createtime-month',     $date->Format($c['createtime'], 'm'));
                $tpl->SetVariable('createtime-day',       $date->Format($c['createtime'], 'd'));
                $tpl->SetVariable('createtime-year',      $date->Format($c['createtime'], 'Y'));
                $tpl->SetVariable('createtime-time',      $date->Format($c['createtime'], 'g:ia'));

                if(!empty($c['reply'])) {
                    $user = $userModel->GetUser((int)$c['replier'], true, true);
                    $tpl->SetBlock('comment/reply');
                    $tpl->SetVariable('reply', $c['reply']);
                    $tpl->SetVariable('replier', $user['nickname']);
                    $tpl->SetVariable('url', $user['url']);
                    $tpl->SetVariable('email', $user['email']);
                    $tpl->SetVariable('lbl_reply', _t('PHOO_REPLY'));
                    $tpl->ParseBlock('comment/reply');
                }

                if ($c['status'] == 3) {
                    $tpl->SetVariable('status_message', _t('PHOO_COMMENT_IS_SPAM'));
                } elseif ($c['status'] == 2) {
                    $tpl->SetVariable('status_message', _t('PHOO_COMMENT_IS_WAITING'));
                } else {
                    $tpl->SetVariable('status_message', '&nbsp;');
                }
                if ($reply_link === 1) {
                    $tpl->SetBlock('comment/reply-link');
                    $tpl->SetVariablesArray($c);
                    $tpl->SetVariable('reply-link', '<a href="'.
                                      $this->gadget->GetURLFor('Reply', array('id' => $c['id'],
                                                                      'photoid' => $c['reference'],
                                                                      'albumid' => $albumid)).'">'.
                                      _t('PHOO_REPLY').'</a>');

                    $tpl->ParseBlock('comment/reply-link');
                }

                $tpl->ParseBlock('comment');
            }
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
            Jaws_Header::Location($this->gadget->GetURLFor('DefaultAction'));
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
        $tpl->SetVariable('name',  $post['name']);
        $tpl->SetVariable('email', $post['email']);
        $tpl->SetVariable('url',   $post['url']);
        if (is_null($post['ip_address'])) {
            $post['ip_address'] = $_SERVER['REMOTE_ADDR'];
        }
        $tpl->SetVariable('title', $post['title']);
        $tpl->SetVariable('comments', Jaws_String::AutoParagraph($post['comments']));
        if (!isset($post['createtime'])) {
            $date = $GLOBALS['app']->loadDate();
            $post['createtime'] = $date->Format(time());
        }
        $tpl->SetVariable('createtime', $post['createtime']);
        $tpl->SetVariable('level', 0);
        $tpl->SetVariable('status_message', '&nbsp;');
        $tpl->SetVariable('ip_address', $post['ip_address']);
        $tpl->SetVariable('avatar_source', 'images/unknown.png');
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
            'name', 'email', 'url', 'comments', 'createtime',
            'ip_address', 'parent_id', 'url2', 'albumid',
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
            $GLOBALS['app']->Session->PushSimpleResponse($image->getMessage(), 'Phoo');
            Jaws_Header::Location($this->gadget->GetURLFor('DefaultAction'));
        }

        $url = $this->gadget->GetURLFor('ViewImage', array('id' => $post['parent_id'], 'albumid' => $post['albumid']));

        $allow_comments_config = $this->gadget->registry->fetch('allow_comments', 'Comments');
        $restricted = $allow_comments_config == 'restricted';
        $allow_comments_config = $restricted? $GLOBALS['app']->Session->Logged() : ($allow_comments_config == 'true');

        // Check if comments are allowed.
        if ($image['allow_comments'] !== true ||
            $image['album_allow_comments'] !== true ||
            $this->gadget->registry->fetch('allow_comments') != 'true' ||
            !$allow_comments_config)
        {
            Jaws_Header::Location($url);
        }

        if (trim($post['name']) == '' || trim($post['comments']) == '') {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('GLOBAL_ERROR_INCOMPLETE_FIELDS'), 'Phoo');
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Phoo_Comment');
            Jaws_Header::Location($url);
        }

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $resCheck = $mPolicy->CheckCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushSimpleResponse($resCheck->getMessage(), 'Phoo');
            $GLOBALS['app']->Session->PushSimpleResponse($post, 'Phoo_Comment');
            Jaws_Header::Location($url);
        }

        $result = $model->NewComment($post['name'], $post['url'], $post['email'], $post['comments'],
                                     $post['parent_id'], $url);
        if (Jaws_Error::isError($result)) {
            $GLOBALS['app']->Session->PushSimpleResponse($result->getMessage(), 'Phoo');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('GLOBAL_MESSAGE_SENT'), 'Phoo');
        }

        Jaws_Header::Location($url);
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