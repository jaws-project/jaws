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
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Photos extends Jaws_Gadget_Action
{

    /**
     * Displays an index of pictures in an album.
     *
     * @access  public
     * @return  string   XHTML template content
     */
//    function ViewAlbum()
//    {
//        return $this->ViewAlbumPage();
//    }

    /**
     * Displays an index of pictures of user photos.
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function ViewUserPhotos()
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
    function Photos()
    {
        $tpl = $this->gadget->template->load('ViewAlbumPage.html');
        $tpl->SetBlock('ViewAlbumPage');

        $get = $this->gadget->request->fetch(array('user', 'album', 'order', 'page'), 'get');
        $user = !empty($get['user'])? (int) $get['user'] : 0;
        $req_album = !empty($get['album'])? (int)$get['album'] : 0;
        $order = !empty($get['order'])? (int) $get['order'] : 1;
        $page = !empty($get['page'])? (int) $get['page'] : 1;

        $pModel = $this->gadget->model->load('Photos');
        $aModel = $this->gadget->model->load('Albums');
        $album = $pModel->GetAlbumImages($req_album, $page, null, null, null, $user);
        if (!Jaws_Error::IsError($album) && !empty($album) && $album['published']) {
            // display album info
            if ($req_album == 0) {
                $tpl->SetVariable('title', _t('PHOO_UNKNOW_ALBUM'));
                $this->SetTitle(_t('PHOO_UNKNOW_ALBUM'));
                $tpl->SetVariable('description', '');
            } else {
                $tpl->SetVariable('title', $album['name']);
                $this->SetTitle($album['name']);
                $tpl->SetVariable('description', $this->gadget->plugin->parseAdmin($album['description']));
            }

            // display images
            $tpl->SetBlock('ViewAlbumPage/photos');
            if (isset($album['images']) && is_array($album['images'])) {
                foreach ($album['images'] as $image) {
                    if ($image['published'] === true) {
                        $imgData = Jaws_Image::getimagesize(ROOT_DATA_PATH . 'phoo/' . $image['thumb']);
                        if (Jaws_Error::IsError($imgData)) {
                            continue;
                        }
                        $tpl->SetBlock('ViewAlbumPage/photos/item');
                        $url = $this->gadget->urlMap(
                            'Photo',
                            array('photo' => $image['id'], 'album' => $image['albumid'])
                        );
                        $tpl->SetVariable('url',      $url);
                        $tpl->SetVariable('thumb',    $this->app->getDataURL('phoo/' . $image['thumb']));
                        $tpl->SetVariable('medium',   $this->app->getDataURL('phoo/' . $image['medium']));
                        $tpl->SetVariable('image',    $this->app->getDataURL('phoo/' . $image['image']));
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

            $total = $aModel->GetAlbumCount($req_album);
            $limit = $this->gadget->registry->fetch('thumbnail_limit');
            // pagination
            $this->gadget->action->load('PageNavigation')->pagination(
                $tpl,
                $page,
                $limit,
                $total,
                'Photos',
                array('album' => $req_album),
                _t('PHOO_PHOTOS_COUNT', $total)
            );
        } else {
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
     * @return  string   XHTML template content
     */
    function Photo($id = null, $albumid = null)
    {
        $tpl = $this->gadget->template->load('ViewImage.html');

        $get = $this->gadget->request->fetch(array('photo', 'album'), 'get');
        $id  = !is_null($id)? $id : (!empty($get['photo'])? $get['photo'] : '0');
        $albumid = !is_null($albumid)? $albumid : (!empty($get['album'])? $get['album'] : '0');

        $pModel = $this->gadget->model->load('Photos');
        $sModel = $this->gadget->model->load('Settings');
        $image = $pModel->GetImage($id, $albumid);
        if (Jaws_Error::IsError($image) || empty($image)) {
            return Jaws_HTTPError::Get(404);
        }

        $imgData = Jaws_Image::getimagesize(ROOT_DATA_PATH . 'phoo/' . $image['medium']);
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
        $tpl->SetVariable('description', $this->gadget->plugin->parseAdmin($image['description']));
        $tpl->SetVariable('medium',      $this->app->getDataURL('phoo/' . $image['medium']));
        $tpl->SetVariable('image',       $this->app->getDataURL('phoo/' . $image['image']));
        $tpl->SetVariable('width',       $imgData[0]);
        $tpl->SetVariable('height',      $imgData[1]);

        // show if the original was kept
        $settings = $sModel->GetSettings();
        if ($settings['keep_original'] == 'true') {
            $tpl->SetVariable('url', $this->app->getDataURL('phoo/' . $image['image']));
        } else {
            $tpl->SetVariable('url', 'javascript:void();');
        }

        if (Jaws_Gadget::IsGadgetInstalled('Comments')) {
            $allow_comments_config = $this->gadget->registry->fetch('allow_comments', 'Comments');
            switch ($allow_comments_config) {
                case 'restricted':
                    $allow_comments_config = $this->app->session->user->logged;
                    $restricted = !$allow_comments_config;
                    break;

                default:
                    $restricted = false;
                    $allow_comments_config = $allow_comments_config == 'true';
            }

            $allow_comments = $image['allow_comments'] == true &&
                $image['album_allow_comments'] == true &&
                $this->gadget->registry->fetch('allow_comments') == 'true' &&
                $allow_comments_config;

            $redirect_to = $this->gadget->urlMap('Photo', array('photo' => $image['id'], 'album' => $albumid));

            $cHTML = Jaws_Gadget::getInstance('Comments')->action->load('Comments');
            $tpl->SetVariable('comments', $cHTML->ShowComments('Phoo', 'Image', $image['id'],
                array('action' => 'Photo',
                    'params' => array('album' => $albumid, 'photo' => $image['id']))));

            if ($allow_comments) {
                $tpl->SetVariable('comment-form', $cHTML->ShowCommentsForm(
                    'Phoo',
                    'Image',
                    $image['id']
                ));
            } elseif ($restricted) {
                $login_url = $this->app->map->GetMappedURL('Users', 'Login');
                $register_url = $this->app->map->GetMappedURL('Users', 'Registration');
                $tpl->SetVariable('comment-form', _t('COMMENTS_COMMENTS_RESTRICTED', $login_url, $register_url));
            }

        }

        // Pager
        $tpl->SetBlock('ViewImage/navigation');
        $tpl->SetVariable('lbl_page_counter', _t('PHOO_PHOTO_COUNTER', $image['pos'], $image['total']));
        $tpl->SetVariable('lbl_thumbs', _t('PHOO_THUMBS'));
        $url = $this->gadget->urlMap('Photos', array('album' => $albumid));
        $tpl->SetVariable('url_thumbs', $url);

        if ($image['first'] != $image['id']) {
            $tpl->SetBlock('ViewImage/navigation/no-first-photo');
            $tpl->SetVariable('lbl_first', _t('PHOO_FIRST'));
            $url = $this->gadget->urlMap('Photo', array('photo' => $image['first'], 'album' => $albumid));
            $tpl->SetVariable('url_first', $url);
            $tpl->SetVariable('lbl_prev', _t('PHOO_PREVIOUS'));
            $url = $this->gadget->urlMap('Photo', array('photo' => $image['previous'], 'album' => $albumid));
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
            $url = $this->gadget->urlMap('Photo', array('photo' => $image['next'], 'album' => $albumid));
            $tpl->SetVariable('url_next', $url);
            $tpl->SetVariable('lbl_last', _t('PHOO_LAST'));
            $url = $this->gadget->urlMap('Photo', array('photo' => $image['last'], 'album' => $albumid));
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
                $date = Jaws_Date::getInstance();
                $datatext .= Jaws::t('DATE') . ': ' . $date->Format($image['exif']['datetime']) . '<br />';
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

        $pModel->ImageHits($id);
        return $tpl->Get();
    }

    /**
     * Upload photo UI
     *
     * @access  public
     * @return  string  HTML content
     */
    function UploadPhotoUI()
    {
        if (!$this->app->session->user->logged) {
            $userGadget = Jaws_Gadget::getInstance('Users');
            return Jaws_Header::Location(
                $userGadget->urlMap(
                    'Login',
                    array('referrer' => bin2hex(Jaws_Utils::getRequestURL(true)))
                ), 401
            );
        }

        $tpl = $this->gadget->template->load('UploadPhoto.html');
        $tpl->SetBlock('uploadUI');

        if ($response = $this->gadget->session->pop('UploadPhoto')) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $this->SetTitle(_t('PHOO_UPLOAD_PHOTO'));
        $tpl->SetVariable('title', _t('PHOO_UPLOAD_PHOTO'));

        $tpl->SetVariable('lbl_file', Jaws::t('FILE'));
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('lbl_description', Jaws::t('DESCRIPTION'));
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('lbl_save', Jaws::t('SAVE'));

        if ($this->gadget->GetPermission('PublishFiles')) {
            $tpl->SetBlock('uploadUI/published');
            $tpl->SetVariable('lbl_published', Jaws::t('PUBLISHED'));
            if (isset($fileInfo['published']) && $fileInfo['published']) {
                $tpl->SetVariable('published_checked', 'checked');
            }
            $tpl->ParseBlock('uploadUI/published');
        }

        // description
        $descriptionEditor =& $this->app->loadEditor('Phoo', 'description', '');
        $descriptionEditor->setId('description');
        $descriptionEditor->TextArea->SetRows(8);
        $tpl->SetVariable('description', $descriptionEditor->Get());

        $tpl->SetVariable('url', $this->gadget->urlMap('UploadPhotoUI'));
        $tpl->SetVariable('back_url', $this->gadget->urlMap('Albums'));

        $tpl->ParseBlock('uploadUI');
        return $tpl->Get();
    }

    /**
     * Upload photo
     *
     * @access  public
     * @return  string  HTML content
     */
    function UploadPhoto()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }
        $post  = $this->gadget->request->fetch(array('title', 'description'), 'post');

        $model = $this->gadget->model->load('Photos');
        $res = $model->SavePhoto($_FILES['photo'], $post['title'], $post['description']);
        if (Jaws_Error::IsError($res)) {
            $this->gadget->session->push(
                $res->getMessage(),
                RESPONSE_ERROR,
                'UploadPhoto'
            );
        } else {
            $this->gadget->session->push(
                _t('PHOO_PHOTO_ADDED'),
                RESPONSE_NOTICE,
                'UploadPhoto'
            );
        }

        return Jaws_Header::Location($this->gadget->urlMap('UploadPhotoUI'));
    }

}