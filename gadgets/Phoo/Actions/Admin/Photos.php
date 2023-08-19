<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_Photos extends Phoo_Actions_Admin_Default
{
    /**
     * Main UI to admin photos
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function AdminPhotos()
    {
        $tpl = $this->gadget->template->loadAdmin('Photos.html');
        $tpl->SetBlock('phoo');
        $tpl->SetVariable('menubar', $this->MenuBar('AdminPhotos'));

        $album = $this->gadget->request->fetch('album', 'get');
        $post  = $this->gadget->request->fetch(array('date', 'album:array'), 'post');

        $aModel = $this->gadget->model->load('Albums');
        $pnModel = $this->gadget->model->load('Photos');
        $albums = $aModel->GetAlbums('createtime', 'ASC');
        if (!Jaws_Error::IsError($albums) && !empty($albums)) {
            $this->AjaxMe('script.js');
            $objDate = Jaws_Date::getInstance();
            $tpl->SetBlock('phoo/photos');
            $tpl->SetVariable('base_action', BASE_SCRIPT . '?reqGadget=Phoo');

            $datecombo =& Piwi::CreateWidget('Combo', 'date');
            $datecombo->SetStyle('width: 200px;');
            $datecombo->AddOption('&nbsp;', '');
            $mindate = $pnModel->GetMinDate();
            if ($mindate) {
                $maxdate = $pnModel->GetMaxDate();
                $mindateArray = explode('/', $mindate);
                $maxdateArray = explode('/', $maxdate);
                for ($year = $maxdateArray[2]; $year >= $mindateArray[2]; $year--) {
                    if ($maxdateArray[2] == $mindateArray[2]) {
                        $mstart = $maxdateArray[0];
                        $mend = $mindateArray[0];
                    } else {
                        if ($year == $maxdateArray[2]) {
                            $mstart = $maxdateArray[0];
                        } else  {
                            $mstart = 12;
                        }
                        if ($year == $mindateArray[2]) {
                            $mend = $mindateArray[0];
                        } else {
                            $mend = 1;
                        }
                    }
                    for ($m = $mstart; $m >= $mend; $m--) {
                        $month = $objDate->MonthString($m);
                        $datecombo->AddOption($month.' '.$year, $m.'/'.$year);
                    }
                }
            }
            $tpl->SetVariable('date', Jaws::t('DATE'));
            $datecombo->SetDefault(isset($post['date']) ? $post['date'] : null);
            $datecombo->AddEvent(ON_CHANGE, 'selectAllAlbums(); this.form.submit();');
            $tpl->SetVariable('date_combo', $datecombo->Get());

            $albumcombo =& Piwi::CreateWidget('Combo', 'album[]');
            $albumcombo->SetID('albums_list');
            $albumcombo->SetStyle('width: 200px;');
            $albumcombo->SetSize(16);
            $albumcombo->SetMultiple(true);

            $free_photos[] = array('id'         => 0,
                'name'       => $this::t('WITHOUT_ALBUM'),
                'createtime' => date('Y-m-d H:i:s'),
                'howmany'    => 0);
            $albums = array_merge($free_photos, $albums);

            foreach ($albums as $a) {
                // FIXME: Ugly hack to add title to albumcombo
                $o =& Piwi::CreateWidget('ComboOption', $a['id'], $a['name']);
                $o->SetTitle($this::t('NUM_PHOTOS_ALBUM', $a['howmany']) . ' / '.
                $this::t('ALBUM_CREATION_DATE', $objDate->Format($a['createtime'])));
                $albumcombo->_options[$a['id']] = $o;
            }

            // r_album = request album
            if (isset($post['album'])) {
                $r_album = $post['album'];
            } else {
                $r_album = isset($album) ? $album : 0;
            }

            $albumcombo->SetDefault($r_album);
            $albumcombo->AddEvent(ON_CHANGE, 'this.form.submit();');
            $tpl->SetVariable('albums', $this::t('ALBUMS'));
            $tpl->SetVariable('albums_combo', $albumcombo->Get());

            if ($this->gadget->GetPermission('ManageAlbums')) {
                $newalbum =& Piwi::CreateWidget('Button', 'newalbum', $this::t('CREATE_NEW_ALBUM'), STOCK_NEW);
                $newalbum->AddEvent(ON_CLICK, "this.form.reqAction.value='NewAlbum'; this.form.submit();");
                $tpl->SetVariable('new_album', $newalbum->Get());
            } else {
                $tpl->SetVariable('new_album','');
            }

            // Ugly hack to convert $r_album to array...
            if (!is_array($r_album)) {
                $aux = $r_album;
                $r_album = array();
                $r_album[] = $aux;
            }

            // Show albums
            if (!empty($r_album) && is_array($r_album)) {
                if (!empty($post['date'])) {
                    $aux = explode('/', $post['date']);
                    $aux = $objDate->ToBaseDate($aux[1], $aux[0]);
                    $year  = $aux['year'];
                    $month = $aux['month'];
                    $day   = $aux['day'];
                } else {
                    $day   = null;
                    $month = null;
                    $year  = null;
                }

                foreach ($r_album as $albumId) {
                    $album = $pnModel->GetAlbumImages($albumId, null, $day, $month, $year);
                    if (!Jaws_Error::IsError($album)) {
                        if ((isset($album['images']) &&
                                !is_array($album['images'])) &&
                            (count($album['images']) == 0)
                        ) {
                            continue;
                        }
                        $tpl->SetBlock('phoo/photos/albums');
                        $tpl->SetVariable('title', $album['name']);
                        $tpl->SetVariable('description', $this->gadget->plugin->parseAdmin($album['description']));
                        $tpl->SetVariable('createtime', $objDate->Format($album['createtime']));
                        $upload_url = BASE_SCRIPT."?reqGadget=Phoo&amp;reqAction=UploadPhotos&amp;album={$album['id']}";
                        $manageAlbumActions = "<a href=\"{$upload_url}\">".$this::t('UPLOAD_PHOTOS')."</a>";
                        $manageAlbumActions.= " | <a href=\"".BASE_SCRIPT."?reqGadget=Phoo&amp;reqAction=EditAlbum&amp;album={$album['id']}\">".
                            $this::t('EDIT_DESCRIPTION')."</a>";
                        $manageAlbumActions.= " | <a href=\"javascript:void(0);\" onclick=\"if (confirm('".
                            $this::t('DELETE_ALBUM_CONFIRM').
                            "')) { window.location = '".BASE_SCRIPT.'?reqGadget=Phoo&amp;reqAction=DeleteAlbum&amp'.
                            ";album={$album['id']}';  }\">".$this::t('DELETE_ALBUM')."</a>";
                        if ($album['id'] != 0) {
                            $tpl->SetVariable('actions', $manageAlbumActions);
                        } else {
                            $tpl->SetVariable('actions', '');
                        }

                        if ((isset($album['images']) && is_array($album['images'])) &&(count($album['images']) > 0)) {
                            // Show photos
                            foreach ($album['images'] as $img) {
                                $imgData = Jaws_Image::getimagesize(ROOT_DATA_PATH . 'phoo/' . $img['image']);
                                $tpl->SetBlock('phoo/photos/albums/item');
                                $tpl->SetVariable('url', BASE_SCRIPT . '?reqGadget=Phoo&amp;reqAction=EditPhoto&amp;image='.
                                    $img['id'].'&amp;album='.$albumId);
                                if (Jaws_Error::IsError($imgData)) {
                                    $tpl->SetVariable('thumb',  'images/unknown.png');
                                    $tpl->SetVariable('width',  60);
                                    $tpl->SetVariable('height', 60);
                                    $tpl->SetBlock('phoo/photos/albums/item/notfound');
                                    $tpl->SetVariable('notfound', $this::t('NOT_FOUND'));
                                    $tpl->ParseBlock('phoo/photos/albums/item/notfound');
                                } else {
                                    $tpl->SetVariable('thumb',  $this->app->getDataURL('phoo/' . $img['thumb']));
                                    $tpl->SetVariable('width',  $imgData[0]);
                                    $tpl->SetVariable('height', $imgData[1]);
                                }
                                $tpl->SetVariable('name',   $img['name']);
                                $tpl->SetVariable('album',  $img['albumid']);
                                if ($img['published'] == false) {
                                    $tpl->SetBlock('phoo/photos/albums/item/notpublished');
                                    $tpl->SetVariable('notpublished', $this::t('NOT_PUBLISHED'));
                                    $tpl->ParseBlock('phoo/photos/albums/item/notpublished');
                                }
                                $tpl->ParseBlock('phoo/photos/albums/item');
                            }
                        } else {
                            if ($album['id'] != 0) {
                                $tpl->SetBlock('phoo/photos/albums/nophotos');
                                $tpl->SetVariable('message', "<a href=\"{$upload_url}\">".$this::t('START_UPLOADING_PHOTOS')."</a>");
                                $tpl->ParseBlock('phoo/photos/albums/nophotos');
                            }
                        }
                        $tpl->ParseBlock('phoo/photos/albums');
                    } else {
                        $this->gadget->session->push($this::t('INEXISTENT_ALBUM'), RESPONSE_ERROR);
                    }
                }
            }

            //Get failures
            $failures = $this->gadget->session->failures;
            // Failures
            if (is_array($failures) && count($failures) > 0) {
                foreach ($failures as $f) {
                    $tpl->SetBlock('phoo/photos/failures');
                    $tpl->SetVariable('message', $f);
                    $tpl->ParseBlock('phoo/photos/failures');
                }
            }
            //Delete key
            $this->app->session->deleteAttribute('failures');
            $tpl->ParseBlock('phoo/photos');

        } else {
            $tpl->SetBlock('phoo/noalbums');
            $tpl->SetVariable('message', $this::t('EMPTY_ALBUMSET'));
            $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'reqGadget', 'Phoo'));
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'reqAction', 'NewAlbum'));
            $b =& Piwi::CreateWidget('Button', 'newalbum', $this::t('CREATE_NEW_ALBUM'), STOCK_NEW);
            $b->SetSubmit(true);
            $form->Add($b);
            $tpl->SetVariable('form', $form->Get());
            $tpl->ParseBlock('phoo/noalbums');
        }

        $tpl->ParseBlock('phoo');
        return $tpl->Get();
    }

    /**
     * Displays a form to edit Photo
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function EditPhoto()
    {
        $this->gadget->CheckPermission('ManagePhotos');
        $this->AjaxMe('script.js');
        $pModel = $this->gadget->model->load('Photos');
        $aModel = $this->gadget->model->load('Albums');

        $get = $this->gadget->request->fetch(array('image', 'album'), 'get');
        $image = $pModel->GetImageEntry((int)$get['image']);
        if (Jaws_Error::IsError($image)) {
            $this->gadget->session->push($image->GetMessage(), RESPONSE_ERROR);
            return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Phoo');
        }

        $id                 = $image['id'];
        $filename           = $this->app->getDataURL('phoo/' . $image['medium']);
        $title              = $image['title'];
        $desc               = $image['description'];
        $meta_keywords      = $image['meta_keywords'];
        $meta_description   = $image['meta_description'];
        $tags               = $image['tags'];
        $albums             = $image['albums'];
        $allow_comments     = $image['allow_comments'];
        $published          = $image['published'];

        $tpl = $this->gadget->template->loadAdmin('EditPhoto.html');
        $tpl->SetBlock('edit_photo');
        $tpl->SetVariable('base_script', BASE_SCRIPT . '?reqGadget=Phoo');
        $tpl->SetVariable('menubar', $this->MenuBar('AdminPhotos'));

        // Tabs titles
        $tpl->SetVariable('editPhoto_tab',  Jaws::t('EDIT', $this::t('PHOTO')));
        $tpl->SetVariable('albums_tab', $this::t('ALBUMS'));
        $tpl->SetVariable('description_tab', $this::t('PHOTO_DESCRIPTION'));

        $photoid =& Piwi::CreateWidget('HiddenEntry', 'image', $id);
        $tpl->SetVariable('imageid', $photoid->Get());
        $filterby =& Piwi::CreateWidget('HiddenEntry', 'filterby', 'id');
        $tpl->SetVariable('filterby', $filterby->Get());
        $filter =& Piwi::CreateWidget('HiddenEntry', 'filter', $id);
        $tpl->SetVariable('filter', $filter->Get());
        $albumid =& Piwi::CreateWidget('HiddenEntry', 'fromalbum', $get['album']);
        $tpl->SetVariable('albumid', $albumid->Get());
        $tpl->SetVariable('name', $this::t('PHOTO_TITLE'));

        $name =& Piwi::CreateWidget('Entry', 'title', $title);
        $name->SetStyle('width: 99%;');
        $name->setId('title');
        $tpl->SetVariable('name_field', $name->Get());

        // Include the editor
        $editor =& $this->app->loadEditor('Phoo', 'description', $desc, false);
        $editor->_Container->setStyle(Jaws::t('LANG_DIRECTION')=='rtl'?'text-align: right;' : 'text-align: left;');
        $editor->TextArea->setStyle('width: 99%;');

        // FIXME: Ugly hack to set rows in editor
        $editor->TextArea->SetRows(6);

        $editor->setId('description');
        $tpl->SetVariable('description', $editor->Get());
        $tpl->SetVariable('lbl_description', $this::t('PHOTO_DESCRIPTION'));

        // Meta keywords
        $metaKeywords =& Piwi::CreateWidget('Entry', 'meta_keywords', $meta_keywords);
        $metaKeywords->SetStyle('width: 100%;');
        $tpl->SetVariable('lbl_meta_keywords', Jaws::t('META_KEYWORDS'));
        $tpl->SetVariable('meta_keywords', $metaKeywords->Get());

        // Meta Description
        $metaDesc =& Piwi::CreateWidget('Entry', 'meta_description', $meta_description);
        $metaDesc->SetStyle('width: 100%;');
        $tpl->SetVariable('lbl_meta_description', Jaws::t('META_DESCRIPTION'));
        $tpl->SetVariable('meta_description', $metaDesc->Get());

        // Tags
        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tpl->SetBlock('edit_photo/tags');
            $tpl->SetVariable('lbl_tag', Jaws::t('TAGS'));
            $linktags =& Piwi::CreateWidget('Entry', 'tags', $tags);
            $linktags->SetStyle('width: 100%;');
            $tpl->SetVariable('tag', $linktags->Get());
            $tpl->ParseBlock('edit_photo/tags');
        }

        $albumchecks =& Piwi::CreateWidget('CheckButtons', 'album', 'vertical');
        $albumsbyname = $aModel->GetAlbums('name', 'ASC');
        if (!Jaws_Error::IsError($albumsbyname) && !empty($albumsbyname)) {
            foreach ($albumsbyname as $a) {
                $albumchecks->AddOption($a['name'], $a['id']);
            }
        }
        $albumchecks->SetDefault($albums);
        $tpl->SetVariable('albums', $this::t('ALBUMS'));
        $tpl->SetVariable('album', $this::t('ALBUM'));
        $tpl->SetVariable('album_field', $albumchecks->Get());

        // Allow Comments
        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        $comments->AddOption($this::t('ALLOW_COMMENTS'), '1', 'allow_comments', $allow_comments);
        $tpl->SetVariable('allow_comments_field', $comments->Get());

        // Status
        $tpl->SetVariable('status', $this::t('STATUS'));
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->setId('published');
        $statCombo->AddOption($this::t('HIDDEN'), '0');
        $statCombo->AddOption($this::t('PUBLISHED'), '1');
        $statCombo->SetDefault((int)$published);
        $tpl->SetVariable('status_field', $statCombo->Get());

        // Photo actions
        $tpl->SetVariable('image_thumb', $filename.'?'.rand());

        // Photo actions
        $comments =& Piwi::CreateWidget('Button', 'comments', '', 'images/stock/stock-comments.png');
        $comments->AddEvent(ON_CLICK, "this.form.reqAction.value = 'ManageComments'; this.form.submit();");
        $tpl->SetVariable('comments', $comments->Get());
        $delete =& Piwi::CreateWidget('Button', 'delete', '', STOCK_DELETE);
        $delete->AddEvent(ON_CLICK, "if (confirm('".$this::t('DELETE_PHOTO_CONFIRM').
            "')) { this.form.reqAction.value = 'DeletePhoto'; this.form.submit();}");
        $tpl->SetVariable('delete', $delete->Get());
        if (function_exists('imagerotate')) {
            $rleft =& Piwi::CreateWidget('Button', 'rotate_left', '', STOCK_ROTATE_LEFT);
            $rleft->AddEvent(ON_CLICK, "this.form.reqAction.value = 'RotateLeft'; this.form.submit();");
            $tpl->SetVariable('rotate_left', $rleft->Get());
            $rright =& Piwi::CreateWidget('Button', 'rotate_right', '', STOCK_ROTATE_RIGHT);
            $rright->AddEvent(ON_CLICK, "this.form.reqAction.value = 'RotateRight'; this.form.submit();");
            $tpl->SetVariable('rotate_right', $rright->Get());
        }
        $tpl->SetVariable('photo_name', $title);

        $cancel =& Piwi::CreateWidget('Button', 'cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "gotoLocation({$get['album']})");
        $tpl->SetVariable('cancel', $cancel->Get());
        $save =& Piwi::CreateWidget('Button', 'save', $this::t('SAVE_CHANGES'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'updatePhoto();');
        //$save->SetSubmit(true);
        $tpl->SetVariable('save', $save->Get());

        $tpl->ParseBlock('edit_photo');
        return $tpl->Get();
    }

    /**
     * Update a photo
     *
     * @access  public
     */
    function SaveEditPhoto()
    {
        $post = $this->gadget->request->fetch(array('allow_comments', 'image', 'title', 'published',
            'title', 'album', 'fromalbum', 'meta_keywords', 'meta_description', 'tags'), 'post');

        if (isset($post['allow_comments'][0])) {
            $allow_comments = true;
        } else {
            $allow_comments = false;
        }

        if ($post['published'] == '1') {
            $published = true;
        } else {
            $published = false;
        }

        $description = $this->gadget->request->fetch('description', 'post', false, array('filters' => 'strip_crlf'));
        // Update photo
        $model = $this->gadget->model->loadAdmin('Photos');
        $res = $model->UpdateEntry(
            $post['image'],
            $post['title'],
            $description,
            $post['meta_keywords'],
            $post['meta_description'],
            $allow_comments,
            $published,
            $post['tags']);
        if (!Jaws_Error::IsError($res)) {
            // Update albums
            $rs2 = $model->SetEntryAlbums($post['image'], $post['album']);
        }

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Phoo&reqAction=EditPhoto&image=' . $post['image'] . '&album='.$post['fromalbum']);
    }

    /**
     * Delete a photo
     *
     * @access  public
     */
    function DeletePhoto()
    {
        $post = $this->gadget->request->fetch(array('image', 'fromalbum'), 'post');
        $model = $this->gadget->model->loadAdmin('Photos');
        $model->DeletePhoto($post['image']);
        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Phoo&album='.$post['fromalbum']);
    }
}