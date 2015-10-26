<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2015 Jaws Development Group
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

        $album = jaws()->request->fetch('album', 'get');
        $post  = jaws()->request->fetch(array('date', 'album:array', 'group'), 'post');

        $aModel = $this->gadget->model->load('Albums');
        $pModel = $this->gadget->model->loadAdmin('Photos');
        $pnModel = $this->gadget->model->load('Photos');
        $albums = $aModel->GetAlbums('createtime', 'ASC', $post['group']);
        if (!Jaws_Error::IsError($albums) && !empty($albums)) {
            $this->AjaxMe('script.js');
            $objDate = Jaws_Date::getInstance();
            $tpl->SetBlock('phoo/photos');
            $tpl->SetVariable('base_action', BASE_SCRIPT . '?gadget=Phoo');

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
            $tpl->SetVariable('date', _t('GLOBAL_DATE'));
            $datecombo->SetDefault(isset($post['date']) ? $post['date'] : null);
            $datecombo->AddEvent(ON_CHANGE, 'selectAllAlbums(); this.form.submit();');
            $tpl->SetVariable('date_combo', $datecombo->Get());

            $albumcombo =& Piwi::CreateWidget('Combo', 'album[]');
            $albumcombo->SetID('albums_list');
            $albumcombo->SetStyle('width: 200px;');
            $albumcombo->SetSize(16);
            $albumcombo->SetMultiple(true);

            $free_photos[] = array('id'         => 0,
                'name'       => _t('PHOO_WITHOUT_ALBUM'),
                'createtime' => date('Y-m-d H:i:s'),
                'howmany'    => 0);
            $albums = array_merge($free_photos, $albums);

            foreach ($albums as $a) {
                // FIXME: Ugly hack to add title to albumcombo
                $o =& Piwi::CreateWidget('ComboOption', $a['id'], $a['name']);
                $o->SetTitle(_t('PHOO_NUM_PHOTOS_ALBUM', $a['howmany']) . ' / '.
                _t('PHOO_ALBUM_CREATION_DATE', $objDate->Format($a['createtime'])));
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
            $tpl->SetVariable('albums', _t('PHOO_ALBUMS'));
            $tpl->SetVariable('albums_combo', $albumcombo->Get());

            if ($this->gadget->GetPermission('ManageAlbums')) {
                $newalbum =& Piwi::CreateWidget('Button', 'newalbum', _t('PHOO_CREATE_NEW_ALBUM'), STOCK_NEW);
                $newalbum->AddEvent(ON_CLICK, "this.form.action.value='NewAlbum'; this.form.submit();");
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
                        $tpl->SetVariable('description', $this->gadget->ParseText($album['description']));
                        $tpl->SetVariable('createtime', $objDate->Format($album['createtime']));
                        $upload_url = BASE_SCRIPT."?gadget=Phoo&amp;action=UploadPhotos&amp;album={$album['id']}";
                        $manageAlbumActions = "<a href=\"{$upload_url}\">"._t('PHOO_UPLOAD_PHOTOS')."</a>";
                        $manageAlbumActions.= " | <a href=\"".BASE_SCRIPT."?gadget=Phoo&amp;action=EditAlbum&amp;album={$album['id']}\">".
                            _t('PHOO_EDIT_DESCRIPTION')."</a>";
                        $manageAlbumActions.= " | <a href=\"javascript:void(0);\" onclick=\"if (confirm('".
                            _t('PHOO_DELETE_ALBUM_CONFIRM').
                            "')) { window.location = '".BASE_SCRIPT.'?gadget=Phoo&amp;action=DeleteAlbum&amp'.
                            ";album={$album['id']}';  }\">"._t('PHOO_DELETE_ALBUM')."</a>";
                        if ($album['id'] != 0) {
                            $tpl->SetVariable('actions', $manageAlbumActions);
                        } else {
                            $tpl->SetVariable('actions', '');
                        }

                        if ((isset($album['images']) && is_array($album['images'])) &&(count($album['images']) > 0)) {
                            // Show photos
                            foreach ($album['images'] as $img) {
                                $imgData = Jaws_Image::getimagesize(JAWS_DATA . 'phoo/' . $img['image']);
                                $tpl->SetBlock('phoo/photos/albums/item');
                                $tpl->SetVariable('url', BASE_SCRIPT . '?gadget=Phoo&amp;action=EditPhoto&amp;image='.
                                    $img['id'].'&amp;album='.$albumId);
                                if (Jaws_Error::IsError($imgData)) {
                                    $tpl->SetVariable('thumb',  'images/unknown.png');
                                    $tpl->SetVariable('width',  60);
                                    $tpl->SetVariable('height', 60);
                                    $tpl->SetBlock('phoo/photos/albums/item/notfound');
                                    $tpl->SetVariable('notfound', _t('PHOO_NOT_FOUND'));
                                    $tpl->ParseBlock('phoo/photos/albums/item/notfound');
                                } else {
                                    $tpl->SetVariable('thumb',  $GLOBALS['app']->getDataURL('phoo/' . $img['thumb']));
                                    $tpl->SetVariable('width',  $imgData[0]);
                                    $tpl->SetVariable('height', $imgData[1]);
                                }
                                $tpl->SetVariable('name',   $img['name']);
                                $tpl->SetVariable('album',  $img['albumid']);
                                if ($img['published'] == false) {
                                    $tpl->SetBlock('phoo/photos/albums/item/notpublished');
                                    $tpl->SetVariable('notpublished', _t('PHOO_NOT_PUBLISHED'));
                                    $tpl->ParseBlock('phoo/photos/albums/item/notpublished');
                                }
                                $tpl->ParseBlock('phoo/photos/albums/item');
                            }
                        } else {
                            if ($album['id'] != 0) {
                                $tpl->SetBlock('phoo/photos/albums/nophotos');
                                $tpl->SetVariable('message', "<a href=\"{$upload_url}\">"._t('PHOO_START_UPLOADING_PHOTOS')."</a>");
                                $tpl->ParseBlock('phoo/photos/albums/nophotos');
                            }
                        }
                        $tpl->ParseBlock('phoo/photos/albums');
                    } else {
                        $GLOBALS['app']->Session->PushLastResponse(_t('PHOO_INEXISTENT_ALBUM'), RESPONSE_ERROR);
                    }
                }
            }

            //Get failures
            $failures = $GLOBALS['app']->Session->GetAttribute('failures');
            // Failures
            if (is_array($failures) && count($failures) > 0) {
                foreach ($failures as $f) {
                    $tpl->SetBlock('phoo/photos/failures');
                    $tpl->SetVariable('message', $f);
                    $tpl->ParseBlock('phoo/photos/failures');
                }
            }
            //Delete key
            $GLOBALS['app']->Session->DeleteAttribute('failures');

            // Groups
            $gModel = $this->gadget->model->load('Groups');
            $groups = $gModel->GetGroups();
            $tpl->SetVariable('lbl_group', _t('GLOBAL_GROUP'));
            $tpl->SetVariable('lbl_all', _t('GLOBAL_ALL'));
            if (!isset($post['group'])) {
                $post['group'] = 0;
            }

            foreach ($groups as $group) {
                $tpl->SetBlock('phoo/photos/group');
                $tpl->SetVariable('gid', $group['id']);
                $tpl->SetVariable('group', $group['name']);
                if ($post['group'] == $group['id']) {
                    $tpl->SetBlock('phoo/photos/group/selected_group');
                    $tpl->ParseBlock('phoo/photos/group/selected_group');
                }
                $tpl->ParseBlock('phoo/photos/group');
            }

            $tpl->ParseBlock('phoo/photos');

        } else {
            $tpl->SetBlock('phoo/noalbums');
            $tpl->SetVariable('message', _t('PHOO_EMPTY_ALBUMSET'));
            $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Phoo'));
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'NewAlbum'));
            $b =& Piwi::CreateWidget('Button', 'newalbum', _t('PHOO_CREATE_NEW_ALBUM'), STOCK_NEW);
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

        $get = jaws()->request->fetch(array('image', 'album'), 'get');
        $image = $pModel->GetImageEntry((int)$get['image']);
        if (Jaws_Error::IsError($image)) {
            $GLOBALS['app']->Session->PushLastResponse($image->GetMessage(), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo');
        }

        $id             = $image['id'];
        $filename       = $GLOBALS['app']->getDataURL('phoo/' . $image['medium']);
        $title          = $image['title'];
        $desc           = $image['description'];
        $albums         = $image['albums'];
        $allow_comments = $image['allow_comments'];
        $published      = $image['published'];

        $tpl = $this->gadget->template->loadAdmin('EditPhoto.html');
        $tpl->SetBlock('edit_photo');
        $tpl->SetVariable('base_script', BASE_SCRIPT . '?gadget=Phoo');
        $tpl->SetVariable('menubar', $this->MenuBar('AdminPhotos'));

        // Tabs titles
        $tpl->SetVariable('editPhoto_tab',  _t('GLOBAL_EDIT', _t('PHOO_PHOTO')));
        $tpl->SetVariable('albums_tab', _t('PHOO_ALBUMS'));
        $tpl->SetVariable('description_tab', _t('PHOO_PHOTO_DESCRIPTION'));

        $photoid =& Piwi::CreateWidget('HiddenEntry', 'image', $id);
        $tpl->SetVariable('imageid', $photoid->Get());
        $filterby =& Piwi::CreateWidget('HiddenEntry', 'filterby', 'id');
        $tpl->SetVariable('filterby', $filterby->Get());
        $filter =& Piwi::CreateWidget('HiddenEntry', 'filter', $id);
        $tpl->SetVariable('filter', $filter->Get());
        $albumid =& Piwi::CreateWidget('HiddenEntry', 'fromalbum', $get['album']);
        $tpl->SetVariable('albumid', $albumid->Get());
        $tpl->SetVariable('name', _t('PHOO_PHOTO_TITLE'));

        $name =& Piwi::CreateWidget('Entry', 'title', $title);
        $name->SetStyle('width: 99%;');
        $name->setId('title');
        $tpl->SetVariable('name_field', $name->Get());

        // Include the editor
        $editor =& $GLOBALS['app']->LoadEditor('Phoo', 'description', $desc, false);
        $editor->setLabel(_t('PHOO_PHOTO_DESCRIPTION'));
        $editor->_Container->setStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'text-align: right;' : 'text-align: left;');
        $editor->TextArea->setStyle('width: 99%;');
        $editor->SetWidth('100%');

        // FIXME: Ugly hack to set rows in editor
        $editor->TextArea->SetRows(6);

        $editor->setId('description');
        $tpl->SetVariable('description', $editor->Get());

        $albumchecks =& Piwi::CreateWidget('CheckButtons', 'album', 'vertical');
        $albumsbyname = $aModel->GetAlbums('name', 'ASC');
        if (!Jaws_Error::IsError($albumsbyname) && !empty($albumsbyname)) {
            foreach ($albumsbyname as $a) {
                $albumchecks->AddOption($a['name'], $a['id']);
            }
        }
        $albumchecks->SetDefault($albums);
        $tpl->SetVariable('albums', _t('PHOO_ALBUMS'));
        $tpl->SetVariable('album', _t('PHOO_ALBUM'));
        $tpl->SetVariable('album_field', $albumchecks->Get());

        // Allow Comments
        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        $comments->AddOption(_t('PHOO_ALLOW_COMMENTS'), '1', 'allow_comments', $allow_comments);
        $tpl->SetVariable('allow_comments_field', $comments->Get());

        // Status
        $tpl->SetVariable('status', _t('PHOO_STATUS'));
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->setId('published');
        $statCombo->AddOption(_t('PHOO_HIDDEN'), '0');
        $statCombo->AddOption(_t('PHOO_PUBLISHED'), '1');
        $statCombo->SetDefault((int)$published);
        $tpl->SetVariable('status_field', $statCombo->Get());

        // Photo actions
        $tpl->SetVariable('image_thumb', $filename.'?'.rand());

        // Photo actions
        $comments =& Piwi::CreateWidget('Button', 'comments', '', 'images/stock/stock-comments.png');
        $comments->AddEvent(ON_CLICK, "this.form.action.value = 'ManageComments'; this.form.submit();");
        $tpl->SetVariable('comments', $comments->Get());
        $delete =& Piwi::CreateWidget('Button', 'delete', '', STOCK_DELETE);
        $delete->AddEvent(ON_CLICK, "if (confirm('"._t('PHOO_DELETE_PHOTO_CONFIRM').
            "')) { this.form.action.value = 'DeletePhoto'; this.form.submit();}");
        $tpl->SetVariable('delete', $delete->Get());
        if (function_exists('imagerotate')) {
            $rleft =& Piwi::CreateWidget('Button', 'rotate_left', '', STOCK_ROTATE_LEFT);
            $rleft->AddEvent(ON_CLICK, "this.form.action.value = 'RotateLeft'; this.form.submit();");
            $tpl->SetVariable('rotate_left', $rleft->Get());
            $rright =& Piwi::CreateWidget('Button', 'rotate_right', '', STOCK_ROTATE_RIGHT);
            $rright->AddEvent(ON_CLICK, "this.form.action.value = 'RotateRight'; this.form.submit();");
            $tpl->SetVariable('rotate_right', $rright->Get());
        }
        $tpl->SetVariable('photo_name', $title);

        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "gotoLocation({$get['album']})");
        $tpl->SetVariable('cancel', $cancel->Get());
        $save =& Piwi::CreateWidget('Button', 'save', _t('PHOO_SAVE_CHANGES'), STOCK_SAVE);
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
        $post = jaws()->request->fetch(array('allow_comments', 'image', 'title', 'published',
            'title', 'album', 'fromalbum'), 'post');

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

        $description = jaws()->request->fetch('description', 'post', 'strip_crlf');
        // Update photo
        $model = $this->gadget->model->loadAdmin('Photos');
        $res = $model->UpdateEntry($post['image'], $post['title'],
            $description, $allow_comments,
            $published);
        if (!Jaws_Error::IsError($res)) {
            // Update albums
            $rs2 = $model->SetEntryAlbums($post['image'], $post['album']);
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=EditPhoto&image=' . $post['image'] . '&album='.$post['fromalbum']);
    }

    /**
     * Delete a photo
     *
     * @access  public
     */
    function DeletePhoto()
    {
        $post = jaws()->request->fetch(array('image', 'fromalbum'), 'post');
        $model = $this->gadget->model->loadAdmin('Photos');
        $model->DeletePhoto($post['image']);
        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&album='.$post['fromalbum']);
    }
}