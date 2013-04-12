<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_AdminHTML extends Jaws_Gadget_HTML
{
    /**
     * Returns the default administration action to use if none is specified.
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function Admin()
    {
        return $this->AdminPhotos();
    }

    /**
     * Displays a menu bar for the control panel gadget.
     *
     * @access protected
     * @param   string   $action_selected    The item to display as selected.
     * @return  string   XHTML template content for menubar
     */
    function MenuBar($action_selected)
    {
        $actions = array('Photos', 'ManageComments', 'AdditionalSettings', 'Import');
        if (!in_array($action_selected, $actions))
            $action_selected = 'Photos';

        require_once JAWS_PATH . 'include/Jaws/Widgets/Menubar.php';
        $menubar = new Jaws_Widgets_Menubar();

        $menubar->AddOption('Photos', _t('PHOO_PHOTOS'), BASE_SCRIPT . '?gadget=Phoo', STOCK_IMAGE);

        if (Jaws_Gadget::IsGadgetInstalled('Comments') && $this->gadget->GetPermission('ManageComments')) {
            $menubar->AddOption('ManageComments', _t('PHOO_COMMENTS'),
                                BASE_SCRIPT . '?gadget=Phoo&amp;action=ManageComments', 'images/stock/stock-comments.png');
        }
        if ($this->gadget->GetPermission('Settings')) {
            $menubar->AddOption('AdditionalSettings', _t('PHOO_ADDITIONAL_SETTINGS'),
                                BASE_SCRIPT . '?gadget=Phoo&amp;action=AdditionalSettings', 'images/stock/properties.png');
        }

        if ($this->gadget->GetPermission('Import')) {
            $menubar->AddOption('Import', _t('PHOO_IMPORT'),
                                BASE_SCRIPT . '?gadget=Phoo&amp;action=Import', STOCK_IMAGE);
        }

        $menubar->Activate($action_selected);

        return $menubar->Get();
    }

    /**
     * Main UI to admin photos
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function AdminPhotos()
    {
        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('AdminPhotos.html');
        $t->SetBlock('phoo');
        $t->SetVariable('menubar', $this->MenuBar('AdminPhotos'));

        $request =& Jaws_Request::getInstance();
        $album   = $request->get('album', 'get');
        $post    = $request->get(array('date', 'album'), 'post');

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $albums = $model->GetAlbums('createtime', 'ASC');
        if (!Jaws_Error::IsError($albums) && !empty($albums)) {
            $objDate = $GLOBALS['app']->loadDate();
            $t->SetBlock('phoo/photos');
            $t->SetVariable('base_action', BASE_SCRIPT . '?gadget=Phoo');

            $datecombo =& Piwi::CreateWidget('Combo', 'date');
            $datecombo->SetStyle('width: 200px;');
            $datecombo->AddOption('&nbsp;', '');
            $mindate = $model->GetMinDate();
            if ($mindate) {
                $maxdate = $model->GetMaxDate();
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
            $t->SetVariable('date', _t('GLOBAL_DATE'));
            $datecombo->SetDefault(isset($post['date']) ? $post['date'] : null);
            $datecombo->AddEvent(ON_CHANGE, 'selectAllAlbums(); this.form.submit();');
            $t->SetVariable('date_combo', $datecombo->Get());

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
            $t->SetVariable('albums', _t('PHOO_ALBUMS'));
            $t->SetVariable('albums_combo', $albumcombo->Get());

            if ($this->gadget->GetPermission('ManageAlbums') === true) {
                $newalbum =& Piwi::CreateWidget('Button', 'newalbum', _t('PHOO_CREATE_NEW_ALBUM'), STOCK_NEW);
                $newalbum->AddEvent(ON_CLICK, "this.form.action.value='NewAlbum'; this.form.submit();");
                $t->SetVariable('new_album', $newalbum->Get());
            } else {
                $t->SetVariable('new_album','');
            }

            // Ugly hack to convert $r_album to array...
            if (!is_array($r_album)) {
                $aux = $r_album;
                $r_album = array();
                $r_album[] = $aux;
            }

            require_once JAWS_PATH . 'include/Jaws/Image.php';
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
                    $album = $model->GetAlbumImages($albumId, null, $day, $month, $year);
                    if (!Jaws_Error::IsError($album)) {
                        if ((isset($album['images']) &&
                            !is_array($album['images'])) &&
                            (count($album['images']) == 0)
                            ) {
                            continue;
                        }
                        $t->SetBlock('phoo/photos/albums');
                        $t->SetVariable('title', $album['name']);
                        $t->SetVariable('description', $this->gadget->ParseText($album['description']));
                        $t->SetVariable('createtime', $objDate->Format($album['createtime']));
                        $upload_url = BASE_SCRIPT."?gadget=Phoo&amp;action=UploadPhotos&amp;album={$album['id']}";
                        $manageAlbumActions = "<a href=\"{$upload_url}\">"._t('PHOO_UPLOAD_PHOTOS')."</a>";
                        $manageAlbumActions.= " | <a href=\"".BASE_SCRIPT."?gadget=Phoo&amp;action=EditAlbum&amp;album={$album['id']}\">".
                            _t('PHOO_EDIT_DESCRIPTION')."</a>";
                        $manageAlbumActions.= " | <a href=\"javascript:void(0);\" onclick=\"if (confirm('".
                            _t('PHOO_DELETE_ALBUM_CONFIRM').
                            "')) { window.location = '".BASE_SCRIPT.'?gadget=Phoo&amp;action=DeleteAlbum&amp'.
                            ";album={$album['id']}';  }\">"._t('PHOO_DELETE_ALBUM')."</a>";
                        if ($album['id'] != 0) {
                            $t->SetVariable('actions', $manageAlbumActions);
                        } else {
                            $t->SetVariable('actions', '');
                        }

                        if ((isset($album['images']) && is_array($album['images'])) &&(count($album['images']) > 0)) {
                            // Show photos
                            foreach ($album['images'] as $img) {
                                $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $img['image']);
                                $t->SetBlock('phoo/photos/albums/item');
                                $t->SetVariable('url', BASE_SCRIPT . '?gadget=Phoo&amp;action=EditPhoto&amp;image='.
                                                $img['id'].'&amp;album='.$albumId);
                                if (Jaws_Error::IsError($imgData)) {
                                    $t->SetVariable('thumb',  'images/unknown.png');
                                    $t->SetVariable('width',  60);
                                    $t->SetVariable('height', 60);
                                    $t->SetBlock('phoo/photos/albums/item/notfound');
                                    $t->SetVariable('notfound', _t('PHOO_NOT_FOUND'));
                                    $t->ParseBlock('phoo/photos/albums/item/notfound');
                                } else {
                                    $t->SetVariable('thumb',  $GLOBALS['app']->getDataURL('phoo/' . $img['thumb']));
                                    $t->SetVariable('width',  $imgData[0]);
                                    $t->SetVariable('height', $imgData[1]);
                                }
                                $t->SetVariable('name',   $img['name']);
                                $t->SetVariable('album',  $img['albumid']);
                                if ($img['published'] == false) {
                                    $t->SetBlock('phoo/photos/albums/item/notpublished');
                                    $t->SetVariable('notpublished', _t('PHOO_NOT_PUBLISHED'));
                                    $t->ParseBlock('phoo/photos/albums/item/notpublished');
                                }
                                $t->ParseBlock('phoo/photos/albums/item');
                            }
                        } else {
                            if ($album['id'] != 0) {
                                $t->SetBlock('phoo/photos/albums/nophotos');
                                $t->SetVariable('message', "<a href=\"{$upload_url}\">"._t('PHOO_START_UPLOADING_PHOTOS')."</a>");
                                $t->ParseBlock('phoo/photos/albums/nophotos');
                            }
                        }
                        $t->ParseBlock('phoo/photos/albums');
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
                    $t->SetBlock('phoo/photos/failures');
                    $t->SetVariable('message', $f);
                    $t->ParseBlock('phoo/photos/failures');
                }
            }
            //Delete key
            $GLOBALS['app']->Session->DeleteAttribute('failures');


            $t->ParseBlock('phoo/photos');
        } else {
            $t->SetBlock('phoo/noalbums');
            $t->SetVariable('message', _t('PHOO_EMPTY_ALBUMSET'));
            $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Phoo'));
            $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'NewAlbum'));
            $b =& Piwi::CreateWidget('Button', 'newalbum', _t('PHOO_CREATE_NEW_ALBUM'), STOCK_NEW);
            $b->SetSubmit(true);
            $form->Add($b);
            $t->SetVariable('form', $form->Get());
            $t->ParseBlock('phoo/noalbums');
        }

        $t->ParseBlock('phoo');
        return $t->Get();
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
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $get     = $request->get(array('image', 'album'), 'get');

        $image = $model->GetImageEntry((int)$get['image']);
        if (Jaws_Error::IsError($image)) {
            $GLOBALS['app']->Session->PushLastResponse($image->GetMessage(), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=Admin');
        }

        $id             = $image['id'];
        $filename       = $GLOBALS['app']->getDataURL('phoo/' . $image['medium']);
        $title          = $image['title'];
        $desc           = $image['description'];
        $albums         = $image['albums'];
        $allow_comments = $image['allow_comments'];
        $published      = $image['published'];

        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('EditPhoto.html');
        $t->SetBlock('edit_photo');
        $t->SetVariable('base_script', BASE_SCRIPT . '?gadget=Phoo');
        $t->SetVariable('menubar', $this->MenuBar('AdminPhotos'));

        // Tabs titles
        $t->SetVariable('editPhoto_tab',  _t('GLOBAL_EDIT', _t('PHOO_PHOTO')));
        $t->SetVariable('albums_tab', _t('PHOO_ALBUMS'));
        $t->SetVariable('description_tab', _t('PHOO_PHOTO_DESCRIPTION'));

        $photoid =& Piwi::CreateWidget('HiddenEntry', 'image', $id);
        $t->SetVariable('imageid', $photoid->Get());
        $filterby =& Piwi::CreateWidget('HiddenEntry', 'filterby', 'id');
        $t->SetVariable('filterby', $filterby->Get());
        $filter =& Piwi::CreateWidget('HiddenEntry', 'filter', $id);
        $t->SetVariable('filter', $filter->Get());
        $albumid =& Piwi::CreateWidget('HiddenEntry', 'fromalbum', $get['album']);
        $t->SetVariable('albumid', $albumid->Get());
        $t->SetVariable('name', _t('PHOO_PHOTO_TITLE'));

        $name =& Piwi::CreateWidget('Entry', 'title', $title);
        $name->SetStyle('width: 99%;');
        $name->setId('title');
        $t->SetVariable('name_field', $name->Get());

        // Include the editor
        $editor =& $GLOBALS['app']->LoadEditor('Phoo', 'description', $desc, false);
        $editor->setLabel(_t('PHOO_PHOTO_DESCRIPTION'));
        $editor->_Container->setStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'text-align: right;' : 'text-align: left;');
        $editor->TextArea->setStyle('width: 99%;');
        $editor->SetWidth('100%');
        
        // FIXME: Ugly hack to set rows in editor
        $editor->TextArea->SetRows(6);

        $editor->setId('description');
        $t->SetVariable('description', $editor->Get());

        $albumchecks =& Piwi::CreateWidget('CheckButtons', 'album', 'vertical');
        $albumsbyname = $model->GetAlbums('name', 'ASC');
        if (!Jaws_Error::IsError($albumsbyname) && !empty($albumsbyname)) {
            foreach ($albumsbyname as $a) {
                $albumchecks->AddOption($a['name'], $a['id']);
            }
        }
        $albumchecks->SetDefault($albums);
        $t->SetVariable('albums', _t('PHOO_ALBUMS'));
        $t->SetVariable('album', _t('PHOO_ALBUM'));
        $t->SetVariable('album_field', $albumchecks->Get());

        // Allow Comments
        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        $selected = $allow_comments === true ? true : false;
        $comments->AddOption(_t('PHOO_ALLOW_COMMENTS'), '1', null, $selected);
        $t->SetVariable('allow_comments_field', $comments->Get());

        // Status
        $t->SetVariable('status', _t('PHOO_STATUS'));
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->setId('published');
        $statCombo->AddOption(_t('PHOO_HIDDEN'), '0');
        $statCombo->AddOption(_t('PHOO_PUBLISHED'), '1');
        $published = $published === true ? true : false;
        $statCombo->SetDefault($published);
        $t->SetVariable('status_field', $statCombo->Get());

        // Photo actions
        $t->SetVariable('image_thumb', $filename.'?'.rand());

        // Photo actions
        $comments =& Piwi::CreateWidget('Button', 'comments', '', 'images/stock/stock-comments.png');
        $comments->AddEvent(ON_CLICK, "this.form.action.value = 'ManageComments'; this.form.submit();");
        $t->SetVariable('comments', $comments->Get());
        $delete =& Piwi::CreateWidget('Button', 'delete', '', STOCK_DELETE);
        $delete->AddEvent(ON_CLICK, "if (confirm('"._t('PHOO_DELETE_PHOTO_CONFIRM').
                          "')) { this.form.action.value = 'DeletePhoto'; this.form.submit();}");
        $t->SetVariable('delete', $delete->Get());
        if (function_exists('imagerotate')) {
            $rleft =& Piwi::CreateWidget('Button', 'rotate_left', '', STOCK_ROTATE_LEFT);
            $rleft->AddEvent(ON_CLICK, "this.form.action.value = 'RotateLeft'; this.form.submit();");
            $t->SetVariable('rotate_left', $rleft->Get());
            $rright =& Piwi::CreateWidget('Button', 'rotate_right', '', STOCK_ROTATE_RIGHT);
            $rright->AddEvent(ON_CLICK, "this.form.action.value = 'RotateRight'; this.form.submit();");
            $t->SetVariable('rotate_right', $rright->Get());
        }
        $t->SetVariable('photo_name', $title);

        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "gotoLocation({$get['album']})");
        $t->SetVariable('cancel', $cancel->Get());
        $save =& Piwi::CreateWidget('Button', 'save', _t('PHOO_SAVE_CHANGES'), STOCK_SAVE);
        $save->AddEvent(ON_CLICK, 'updatePhoto();');
        //$save->SetSubmit(true);
        $t->SetVariable('save', $save->Get());

        $t->ParseBlock('edit_photo');
        return $t->Get();
    }

    /**
     * Update a photo
     * 
     * @access  public
     */
    function SaveEditPhoto()
    {
        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('allow_comments', 'image', 'title', 'published',
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

        $description = $request->get('description', 'post', false);
        // Update photo
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
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
     * Rotate left a image
     * 
     * @access  public
     */
    function RotateLeft()
    {
        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('image', 'fromalbum'), 'post');

        //FIXME: Ugly, maybe we need to pass just the image id, also we need to create a class
        //to manage image actions(resize, rotate)
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $image = $model->GetImageEntry($post['image']);
        if (Jaws_Error::IsError($image))  {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=EditPhoto&image='.$post['image'].
                                  '&album='.$post['fromalbum']);
        }

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        $objImage = Jaws_Image::factory();
        if (Jaws_Error::IsError($objImage)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            // thumb
            $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['thumb']));
            $objImage->rotate(-90);
            $res = $objImage->save();
            $objImage->free();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
            } else {
                // medium
                $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['medium']));
                $objImage->rotate(-90);
                $res = $objImage->save();
                $objImage->free();
                if (Jaws_Error::IsError($res)) {
                    $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
                } else {
                    // original image
                    $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['image']));
                    $objImage->rotate(-90);
                    $res = $objImage->save();
                    $objImage->free();
                    if (Jaws_Error::IsError($res)) {
                        $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
                    } else {
                        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_IMAGE_ROTATED_LEFT'), RESPONSE_NOTICE);
                    }
                }
            }
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=EditPhoto&image='.$post['image'].
                              '&album='.$post['fromalbum']);
    }

    /**
     * Rotate right a image
     * 
     * @access  public
     */
    function RotateRight()
    {
        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('image', 'fromalbum'), 'post');

        //FIXME: Ugly, maybe we need to pass just the image id, also we need to create a
        //class to manage image actions(resize, rotate)
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $image = $model->GetImageEntry($post['image']);
        if (Jaws_Error::IsError($image)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=EditPhoto&image='.$post['image'].
                                  '&album='.$post['fromalbum']);
        }

        include_once JAWS_PATH . 'include/Jaws/Image.php';
        $objImage = Jaws_Image::factory();
        if (Jaws_Error::IsError($objImage)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            // thumb
            $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['thumb']));
            $objImage->rotate(90);
            $res = $objImage->save();
            $objImage->free();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
            } else {
                // medium
                $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['medium']));
                $objImage->rotate(90);
                $res = $objImage->save();
                $objImage->free();
                if (Jaws_Error::IsError($res)) {
                    $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
                } else {
                    // original image
                    $objImage->load(JAWS_DATA. 'phoo/'. rawurldecode($image['image']));
                    $objImage->rotate(90);
                    $res = $objImage->save();
                    $objImage->free();
                    if (Jaws_Error::IsError($res)) {
                        $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
                    } else {
                        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_IMAGE_ROTATED_RIGHT'), RESPONSE_NOTICE);
                    }
                }
            }
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=EditPhoto&image='.$post['image'].
                             '&album='.$post['fromalbum']);
    }

    /**
     * Delete a photo
     * 
     * @access  public
     */
    function DeletePhoto()
    {
        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('image', 'fromalbum'), 'post');

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $model->DeletePhoto($post['image']);
        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=AdminPhotos&album='.$post['fromalbum']);
    }

    /**
     * Displays a form for adding new images.
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function UploadPhotos()
    {
        $this->gadget->CheckPermission('AddPhotos');
        $this->AjaxMe('script.js');

        $request =& Jaws_Request::getInstance();
        $album = $request->get('album', 'get');

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('UploadPhotos.html');
        $t->SetBlock('upload');
        $t->SetVariable('menubar', $this->MenuBar('UploadPhotos'));

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $phooFieldset = new Jaws_Widgets_FieldSet(_t('PHOO_UPLOAD_PHOTOS'));
        $phooFieldset->SetDirection('vertical');
        $phooFieldset->SetId('phoo_fieldset');
        $phooForm =& Piwi::CreateWidget('Form',
                                        BASE_SCRIPT . '?gadget=Phoo&action=AdminPhotos',
                                        'post',
                                        'multipart/form-data');
        $phooForm->Add(Piwi::CreateWidget('HiddenEntry', 'MAX_FILE_SIZE', '15000000'));
        $phooForm->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Phoo'));
        $phooForm->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'UploadPhotosStep2'));

        $albumcombo =& Piwi::CreateWidget('Combo', 'album', _t('PHOO_ALBUM'));
        $albums = $model->GetAlbums('name', 'ASC');
        if (!Jaws_Error::IsError($albums) && !empty($albums)) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($albums as $a) {
                // FIXME: Ugly hack to add title to albumcombo
                $o =& Piwi::CreateWidget('ComboOption', $a['id'], $a['name']);
                $o->SetTitle(_t('PHOO_NUM_PHOTOS_ALBUM', $a['howmany']) . ' / '.
                             _t('PHOO_ALBUM_CREATION_DATE', $date->Format($a['createtime'])));
                $albumcombo->_options[$a['id']] = $o;
            }
        }
        $albumcombo->SetDefault((int)$album);
        $phooFieldset->Add($albumcombo);
        for ($i = 1; $i <= 5; $i++) {
            $imageEntry =& Piwi::CreateWidget('FileEntry', 'photo'.$i);
            $imageEntry->SetTitle(_t('PHOO_PHOTO').' '.$i);
            $phooFieldset->Add($imageEntry);
        }

        $addEntryButton =& Piwi::CreateWidget('Button', 'addEntryButton', _t('PHOO_ADD_ANOTHER_PHOTO'), STOCK_ADD);
        $addEntryButton->AddEvent(ON_CLICK, "addEntry('" . _t('PHOO_PHOTO') . "');");
        $addEntryUrl = '<span id="phoo_addentry6"><div><a href="#" onclick="addEntry(\'' . _t('PHOO_PHOTO') . '\');">' . _t('PHOO_ADD_ANOTHER_PHOTO') . '</a></div></span>';
        $addEntryArea = '<span id="phoo_addentry6"><div>' . $addEntryButton->Get() . '</div></span>';
        $addEntry =& Piwi::CreateWidget('StaticEntry', $addEntryArea);
        $phooFieldset->Add($addEntry);
        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;'); //hig style
        $submit =& Piwi::CreateWidget('Button', 'uploadphotos', _t('PHOO_UPLOAD_PHOTOS'), STOCK_SAVE);
        $submit->SetSubmit();
        $buttonbox->Add($submit);
        $phooForm->Add($phooFieldset);
        $phooForm->Add($buttonbox);

        $t->SetVariable('form', $phooForm->Get());

        $t->ParseBlock('upload');
        return $t->Get();
    }

    /**
     * Saves a new image using input data from UploadPhotos
     * If required any new albums will be created
     *
     * @access  public
     * @see Phoo::UploadPhotos()
     * @see Phoo_Model::AddEntryToAlbum()
     * @see Phoo_Model::AddCategoryToEntry()
     * @see Phoo_Model::NewAlbum()
     */
    function UploadPhotosStep2()
    {
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $files = $model->UnpackFiles($_FILES);

        $request =& Jaws_Request::getInstance();
        $album   = (int)$request->get('album', 'post');
        $extra_params = $request->get('extra_params', 'post');

        $failures = array();
        $uploadedImages = array();
        $user_id = $GLOBALS['app']->Session->GetAttribute('user');
        $album_data = $model->getAlbumInfo($album);
        if (Jaws_Error::IsError($album_data) || empty($album_data)) {
            $GLOBALS['app']->Session->PushLastResponse($album_data->getMessage());
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=AdminPhotos');
            exit;
        }
        for ($i = 1; $i <= count($files); $i++) {
            if (!isset($files['photo'.$i])) {
                continue;
            }

            $filename = $files['photo'.$i]['name'];
            if (!empty($filename)) {
                $exploded = explode('.', $filename);
                $ext  = array_pop($exploded);
                $name = basename($filename, '.'.$ext);
                $id = $model->NewEntry($user_id, $files['photo'.$i], $name, '', true, $album_data);
                if (!Jaws_Error::IsError($id)) {
                    $uploadedImages[] = $id;
                } else {
                    $failures[] = _t('PHOO_UPLOAD_FAILURE').' <strong>'.$name.'.'.$ext.'</strong>';
                }
            }
        }

        // Assign to album
        if (count($uploadedImages) > 0) {
            foreach ($uploadedImages as $img) {
                $res = $model->AddEntryToAlbum($img, $album);
                if (Jaws_Error::IsError($res)) {
                    ///FIXME: This is a unacceptable solution
                    Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=AdminPhotos');
                }
            }
        }

        $GLOBALS['app']->Session->SetAttribute('failures', $failures);
        if (empty($extra_params)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=AdminPhotos&album=' . $album);
        } else {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=BrowsePhoo&album=' . $album . html_entity_decode($extra_params));
        }

    }

    /**
     * New album
     * 
     * @access  public
     * @return  string  XHTML template content
     * @see Phoo_Model::SaveNewAlbum()
     * @see Phoo::AdminPhotos()
     */
    function NewAlbum()
    {
        $this->gadget->CheckPermission('ManageAlbums');

        $request     =& Jaws_Request::getInstance();
        $action      = $request->get('action', 'get');
        $description = $request->get('description', 'post', false);

        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('EditAlbum.html');
        $t->SetBlock('edit_album');
        $t->SetVariable('base_script', BASE_SCRIPT);
        $t->SetVariable('menubar', $this->MenuBar(isset($action) ? $action : ''));

        $t->SetVariable('action', 'SaveNewAlbum');

        $name =& Piwi::CreateWidget('Entry', 'name');
        $name->SetStyle('width: 100%;');
        $t->SetVariable('name', _t('PHOO_ALBUM_NAME'));
        $t->SetVariable('name_field', $name->get());

        // Allow Comments
        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        if ($this->gadget->GetRegistry('allow_comments') === 'true') {
            $selected = true;
        } else {
            $selected = false;
        }
        $comments->AddOption(_t('PHOO_ALLOW_COMMENTS'), '1', null, $selected);
        $t->SetVariable('allow_comments_field', $comments->get());

        // Status
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->setId('published');
        $statCombo->AddOption(_t('PHOO_HIDDEN'), '0');
        $statCombo->AddOption(_t('PHOO_PUBLISHED'), '1');
        if ($this->gadget->GetRegistry('published') === 'true') {
            $published = true;
        } else {
            $published = false;
        }
        $statCombo->SetDefault($published);
        $t->SetVariable('status', _t('PHOO_STATUS'));
        $t->SetVariable('status_field', $statCombo->get());

        $desc = isset($description) ? $description : '';
        $editor =& $GLOBALS['app']->LoadEditor('Phoo', 'description', $desc, false);
        $editor->setLabel(_t('PHOO_ALBUM_DESC'));
        $editor->_Container->setStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'text-align: right;' : 'text-align: left;');
        $editor->TextArea->setStyle('width: 100%;');
        $editor->SetWidth('100%');
        // FIXME: Ugly hack to set rows in editor
        $editor->TextArea->SetRows(5);
        $t->SetVariable('description', $editor->get());

        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, 'history.go(-1)');
        $t->SetVariable('cancel', $cancel->Get());
        $save =& Piwi::CreateWidget('Button', 'save', _t('PHOO_SAVE_CHANGES'), STOCK_SAVE);
        $save->SetSubmit(true);
        $t->SetVariable('save', $save->Get());

        $t->ParseBlock('edit_album');
        return $t->Get();
    }

    /**
     * Creates a new album based on input data from the New Album
     *
     * @access  public
     * @see Phoo_Model::NewAlbum()
     * @see Phoo::AdminPhotos()
     */
    function SaveNewAlbum()
    {
        $this->gadget->CheckPermission('ManageAlbums');
        $request     =& Jaws_Request::getInstance();
        $post        = $request->get(array('name', 'allow_comments', 'published'), 'post');
        $description = $request->get('description', 'post', false);

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $album = $model->NewAlbum($post['name'], $description, isset($post['allow_comments'][0]), $post['published']);
        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=Admin&album='.$album);
    }

    /**
     * Edit album
     * 
     * @access  public
     * @return  string  XHTML template content
     * @see Phoo_Model::SaveEditAlbum()
     * @see Phoo::AdminPhotos()
     */
    function EditAlbum()
    {
        $this->gadget->CheckPermission('ManageAlbums');
        $this->AjaxMe('script.js');
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');

        $request =& Jaws_Request::getInstance();
        $get     = $request->get(array('action', 'album'), 'get');

        $id = (int)$get['album'];
        $album = $model->GetAlbumInfo($id);
        if (Jaws_Error::IsError($album) || empty($album)) {
            ///FIXME the error msg never has a chance to show
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=AdminPhotos');
        }

        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('EditAlbum.html');
        $t->SetBlock('edit_album');
        $t->SetVariable('base_script', BASE_SCRIPT . '?gadget=Phoo');
        $t->SetVariable('menubar', $this->MenuBar($get['action']));

        $t->SetVariable('action', 'SaveEditAlbum');
        $albumid =& Piwi::CreateWidget('HiddenEntry', 'album', $album['id']);
        $t->SetVariable('album', $albumid->Get());

        $name =& Piwi::CreateWidget('Entry', 'name', $album['name']);
        $name->SetStyle('width: 100%;');
        $t->SetVariable('name', _t('PHOO_ALBUM_NAME'));
        $t->SetVariable('name_field', $name->get());

        // Allow Comments
        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        if ($album['allow_comments'] === true) {
            $selected = true;
        } else {
            $selected = false;
        }
        $comments->AddOption(_t('PHOO_ALLOW_COMMENTS'), '1', null, $selected);
        $t->SetVariable('allow_comments_field', $comments->get());

        // Status
        $t->SetVariable('status', _t('PHOO_STATUS'));
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->setId('published');
        $statCombo->AddOption(_t('PHOO_HIDDEN'), '0');
        $statCombo->AddOption(_t('PHOO_PUBLISHED'), '1');
        if ($album['published'] == true) {
            $published = true;
        } else {
            $published = false;
        }
        $statCombo->SetDefault($published);
        $t->SetVariable('status', _t('PHOO_STATUS'));
        $t->SetVariable('status_field', $statCombo->get());

        $editor =& $GLOBALS['app']->LoadEditor('Phoo', 'description', $album['description'], false);
        $editor->setLabel(_t('PHOO_ALBUM_DESC'));
        $editor->_Container->setStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'text-align: right;' : 'text-align: left;');
        $editor->TextArea->setStyle('width: 100%;');
        $editor->SetWidth('100%');
        // FIXME: Ugly hack to set rows in editor
        $editor->TextArea->SetRows(5);
        $t->SetVariable('description', $editor->get());

        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "gotoLocation({$get['album']})");
        $t->SetVariable('cancel', $cancel->Get());
        $save =& Piwi::CreateWidget('Button', 'save', _t('PHOO_SAVE_CHANGES'), STOCK_SAVE);
        $save->SetSubmit(true);
        $t->SetVariable('save', $save->Get());

        $t->ParseBlock('edit_album');

        return $t->Get();
    }

    /**
     * Updates a given album with the given info
     *
     * @access  public
     * @see Phoo_Model::NewAlbum()
     * @see Phoo::AdminPhotos()
     */
    function SaveEditAlbum()
    {
        $this->gadget->CheckPermission('ManageAlbums');

        $request     =& Jaws_Request::getInstance();
        $post        = $request->get(array('name', 'album', 'allow_comments', 'published'), 'post');
        $description = $request->get('description', 'post', false);

        $id = (int)$post['album'];
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $model->UpdateAlbum($id, $post['name'], $description, isset($post['allow_comments'][0]), $post['published']);
        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=EditAlbum&album='.$id);
    }

    /**
     * Delete an album and all its images
     * 
     * @access  public
     * @see Phoo_Model::DeleteAlbum()
     * @see Phoo::AdminPhotos()
     */
    function DeleteAlbum()
    {
        $this->gadget->CheckPermission('ManageAlbums');

        $request =& Jaws_Request::getInstance();
        $album   = (int)$request->get('album', 'get');

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $foo = $model->DeleteAlbum($album);
        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=AdminPhotos');
    }

    /**
     * Prepares the comments datagrid of an advanced search
     *
     * @access  public
     * @return  string  The XHTML template content of a datagrid
     */
    function CommentsDatagrid()
    {
        $cHtml = $GLOBALS['app']->LoadGadget('Comments', 'AdminHTML');
        return $cHtml->Get($this->gadget->name);
    }

    /**
     * Builds the data (an array) of filtered comments
     *
     * @access  public
     * @param   int     $limit   Limit of comments
     * @param   string  $filter  Filter
     * @param   string  $search  Search word
     * @param   string  $status  Spam status (approved, waiting, spam)
     * @return  array   Filtered Comments
     */
    function CommentsData($limit = 0, $filter = '', $search = '', $status = '')
    {
        $cHtml = $GLOBALS['app']->LoadGadget('Comments', 'AdminHTML');
        return $cHtml->GetDataAsArray(
            $this->gadget->name,
            BASE_SCRIPT . '?gadget=Phoo&amp;action=EditComment&amp;id={id}',
            BASE_SCRIPT . '?gadget=Phoo&amp;action=ReplyComment&amp;id={id}',
            $filter,
            $search,
            $status,
            $limit
        );
    }

    /**
     * Displays blog comments manager
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ManageComments()
    {
        $this->gadget->CheckPermission('ManageComments');
        if (!Jaws_Gadget::IsGadgetInstalled('Comments')) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo');
        }

        $this->AjaxMe('script.js');
        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('ManageComments.html');
        $tpl->SetBlock('manage_comments');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('menubar', $this->MenuBar('ManageComments'));

        $tpl->SetVariable('comments_where', _t('PHOO_COMMENTS_WHERE'));
        $tpl->SetVariable('status_label', _t('GLOBAL_STATUS'));
        $tpl->SetVariable('deleteConfirm', _t('PHOO_DELETE_MASSIVE_COMMENTS'));

        //Status
        $status =& Piwi::CreateWidget('Combo', 'status');
        $status->AddOption('&nbsp;','various');
        $status->AddOption(_t('GLOBAL_STATUS_APPROVED'), 1);
        $status->AddOption(_t('GLOBAL_STATUS_WAITING'), 2);
        $status->AddOption(_t('GLOBAL_STATUS_SPAM'), 3);
        $status->SetDefault('various');
        $status->AddEvent(ON_CHANGE, 'return searchComment();');
        $tpl->SetVariable('status', $status->Get());

        // filter by
        $filterByData = '';
        $filterBy =& Piwi::CreateWidget('Combo', 'filterby');
        $filterBy->AddOption('&nbsp;','various');
        $filterBy->AddOption(_t('PHOO_ID'), 'id');
        $filterBy->AddOption(_t('PHOO_COMMENT_CONTAINS'), 'comment');
        $filterBy->AddOption(_t('PHOO_NAME_CONTAINS'), 'name');
        $filterBy->AddOption(_t('PHOO_EMAIL_CONTAINS'), 'email');
        $filterBy->AddOption(_t('PHOO_URL_CONTAINS'), 'url');
        $filterBy->AddOption(_t('PHOO_IP_CONTAINS'), 'ip');
        $filterBy->SetDefault($filterByData);
        $tpl->SetVariable('filter_by', $filterBy->Get());

        // filter
        $filterData = '';
        $filterEntry =& Piwi::CreateWidget('Entry', 'filter', $filterData);
        $filterEntry->setSize(20);
        $tpl->SetVariable('filter', $filterEntry->Get());
        $filterButton =& Piwi::CreateWidget('Button', 'filter_button',
                                            _t('PHOO_FILTER'), STOCK_SEARCH);
        $filterButton->AddEvent(ON_CLICK, 'javascript: searchComment();');

        $tpl->SetVariable('filter_button', $filterButton->Get());

        // Display the data
        $tpl->SetVariable('comments', $this->CommentsDatagrid($filterByData, $filterData));

        $tpl->ParseBlock('manage_comments');

        return $tpl->Get();
    }

    /**
     * Displays phoo comment to be edited
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function EditComment()
    {
        $this->gadget->CheckPermission('ManageComments');
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $id      = $request->get('id', 'get');

        $comment = $model->GetComment($id);
        if (Jaws_Error::IsError($comment)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=ManageComments');
        }

        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('EditComment.html');
        $tpl->SetBlock('edit_comment');
        $tpl->SetVariable('menubar', $this->MenuBar('ManageComments'));
        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'id', $comment['id']));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Phoo'));
        $permalink = $GLOBALS['app']->Map->GetURLFor('Phoo', 'ViewImage', array('id' => $comment['reference'], 'albumid' => 0));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'SaveEditComment'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'permalink', $permalink));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'status', $comment['status']));

        $name =& Piwi::CreateWidget('Entry', 'name', $comment['name']);
        $name->SetTitle(_t('GLOBAL_NAME'));

        $email =& Piwi::CreateWidget('Entry', 'email', $comment['email']);
        $email->SetTitle(_t('GLOBAL_EMAIL'));
        $email->SetStyle('direction: ltr;');

        $url =& Piwi::CreateWidget('Entry', 'url', $comment['url']);
        $url->SetTitle(_t('GLOBAL_URL'));
        $url->SetStyle('direction: ltr;');

        $ip =& Piwi::CreateWidget('Entry', 'ip', $comment['ip']);
        $ip->SetTitle(_t('PHOO_IP_ADDRESS'));
        $ip->SetStyle('direction: ltr;');
        $ip->SetEnabled(false);

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $comment =& Piwi::CreateWidget('TextArea', 'comments', $xss->defilter($comment['msg_txt']));
        $comment->SetRows(5);
        $comment->SetColumns(60);
        $comment->SetStyle('width: 400px;');
        $comment->SetTitle(_t('PHOO_COMMENT'));

        $cancelButton =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelButton->AddEvent(ON_CLICK, 'history.go(-1);');

        $submitButton =& Piwi::CreateWidget('Button', 'send', _t('PHOO_UPDATE_COMMENT'), STOCK_SAVE);
        $submitButton->SetSubmit();

        $deleteButton =& Piwi::CreateWidget('Button', 'delete', _t('PHOO_DELETE_COMMENT'), STOCK_DELETE);
        $deleteButton->AddEvent(ON_CLICK, "this.form.action.value = 'DeleteComment'; this.form.submit();");

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($deleteButton);
        $buttonbox->PackStart($cancelButton);
        $buttonbox->PackStart($submitButton);

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('PHOO_UPDATE_COMMENT'));

        $fieldset->Add($name);
        $fieldset->Add($email);
        $fieldset->Add($url);
        $fieldset->Add($ip);
        $fieldset->Add($comment);
        $form->add($fieldset);
        $form->Add($buttonbox);

        $tpl->SetVariable('form', $form->Get());

        $tpl->ParseBlock('edit_comment');
        return $tpl->Get();
    }

    /**
     * Display phoo comment to reply
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ReplyComment()
    {
        $this->gadget->CheckPermission('ManageComments');
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $id      = $request->get('id', 'get');

        $comment = $model->GetComment($id);
        if (Jaws_Error::IsError($comment)) {
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=ManageComments');
        }

        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('ReplyComment.html');
        $tpl->SetBlock('reply_comment');
        $tpl->SetVariable('menubar', $this->MenuBar('ManageComments'));
        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'id', $comment['id']));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Phoo'));
        $permalink = $GLOBALS['app']->Map->GetURLFor('Phoo', 'ViewImage', array('id' => $comment['reference'], 'albumid' => 0));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'SaveReplyComment'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'permalink', $permalink));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'status', $comment['status']));

        $name =& Piwi::CreateWidget('Entry', 'name', $comment['name']);
        $name->SetReadOnly(true);
        $name->SetTitle(_t('GLOBAL_NAME'));

        $email =& Piwi::CreateWidget('Entry', 'email', $comment['email']);
        $email->SetReadOnly(true);
        $email->SetTitle(_t('GLOBAL_EMAIL'));
        $email->SetStyle('direction: ltr;');

        $url =& Piwi::CreateWidget('Entry', 'url', $comment['url']);
        $url->SetReadOnly(true);
        $url->SetTitle(_t('GLOBAL_URL'));
        $url->SetStyle('direction: ltr;');

        $ip =& Piwi::CreateWidget('Entry', 'ip', $comment['ip']);
        $ip->SetTitle(_t('PHOO_IP_ADDRESS'));
        $ip->SetStyle('direction: ltr;');
        $ip->SetReadOnly(true);

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $comment_msg =& Piwi::CreateWidget('TextArea', 'comments', $xss->defilter($comment['msg_txt']));
        $comment_msg->SetReadOnly(true);
        $comment_msg->SetRows(5);
        $comment_msg->SetColumns(60);
        $comment_msg->SetStyle('width: 400px;');
        $comment_msg->SetTitle(_t('PHOO_COMMENT'));

        $reply =& Piwi::CreateWidget('TextArea', 'reply', $xss->defilter($comment['reply']));
        $reply->SetRows(5);
        $reply->SetColumns(60);
        $reply->SetStyle('width: 400px;');
        $reply->SetTitle(_t('PHOO_REPLY'));

        $cancelButton =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancelButton->AddEvent(ON_CLICK, 'history.go(-1);');

        $submitButton =& Piwi::CreateWidget('Button', 'send', _t('PHOO_UPDATE_COMMENT'), STOCK_SAVE);
        $submitButton->SetSubmit();

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($cancelButton);
        $buttonbox->PackStart($submitButton);

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('PHOO_REPLY_COMMENT'));

        $fieldset->Add($name);
        $fieldset->Add($email);
        $fieldset->Add($url);
        $fieldset->Add($ip);
        $fieldset->Add($comment_msg);
        $fieldset->Add($reply);
        $form->add($fieldset);
        $form->Add($buttonbox);

        $tpl->SetVariable('form', $form->Get());

        $tpl->ParseBlock('reply_comment');
        return $tpl->Get();
    }

    /**
     * Applies changes to a phoo comment
     *
     * @access  public
     */
    function SaveEditComment()
    {
        $this->gadget->CheckPermission('ManageComments');

        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('id',  'name', 'url',
                                       'email', 'comments',
                                       'permalink', 'status'), 'post');

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $model->UpdateComment($post['id'], $post['name'], $post['url'],
                              $post['email'], $post['comments'],
                              $post['permalink'], $post['status']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=ManageComments');
    }

    /**
     * Save reply to a blog comment
     *
     * @access  public
     */
    function SaveReplyComment()
    {
        $this->gadget->CheckPermission('ManageComments');
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('id', 'reply'), 'post');

        $model->ReplyComment($post['id'], $post['reply']);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=ManageComments');
    }

    /**
     * Deletes a phoo comment
     *
     * @access  public
     */
    function DeleteComment()
    {
        $this->gadget->CheckPermission('ManageComments');
        $request =& Jaws_Request::getInstance();
        $id = $request->get('id');

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $model->DeleteComment($id);

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=ManageComments');
    }

    /**
     * Displays phoo settings administration panel
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function AdditionalSettings()
    {
        $this->gadget->CheckPermission('Settings');
        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('AdditionalSettings.html');
        $tpl->SetBlock('additional');

        // Header
        $tpl->SetVariable('menubar',$this->MenuBar('AdditionalSettings'));

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'gadget', 'Phoo'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'action', 'SaveAdditionalSettings'));

        include_once JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet(_t('PHOO_ADDITIONAL_SETTINGS'));

        // Save Button
        $save =& Piwi::CreateWidget('Button', 'save', _t('PHOO_SAVE_SETTINGS'), STOCK_SAVE);
        $save->SetSubmit();

        // Reset Button
        $reset =& Piwi::CreateWidget('Button', 'reset', _t('GLOBAL_RESET'), STOCK_RESET);
        $reset->setReset(true);

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'Model');
        $settings = $model->GetSettings();

        // Default View
        $tpl->SetVariable('label', _t('PHOO_DEFAULT_VIEW'));
        $viewCombo =& Piwi::CreateWidget('Combo', 'default_action');
        $viewCombo->setContainerClass('oneline');
        $viewCombo->SetTitle(_t('PHOO_DEFAULT_VIEW'));
        $viewCombo->AddOption(_t('PHOO_ALBUM_LIST'), 'AlbumList');
        $viewCombo->AddOption(_t('PHOO_PHOTOBLOG'), 'PhotoblogPortrait');
        $viewCombo->SetDefault($settings['default_action']);

        // Default status
        $statusCombo =& Piwi::CreateWidget('Combo', 'published');
        $statusCombo->setContainerClass('oneline');
        $statusCombo->SetTitle(_t('PHOO_DEFAULT_STATUS'));
        $statusCombo->AddOption(_t('PHOO_PUBLISHED'), 'true');
        $statusCombo->AddOption(_t('PHOO_HIDDEN'), 'false');
        $statusCombo->SetDefault($settings['published']);

        // Albums Order type
        $albumsorderType =& Piwi::CreateWidget('Combo', 'albums_order_type');
        $albumsorderType->setContainerClass('oneline');
        $albumsorderType->SetTitle(_t('PHOO_ALBUMS_ORDER_TYPE'));
        $albumsorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_CREATETIME') . ' &darr;', 'createtime');
        $albumsorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_CREATETIME') . ' &uarr;', 'createtime DESC');
        $albumsorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_NAME') . ' &darr;', 'name');
        $albumsorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_NAME') . ' &uarr;', 'name DESC');
        $albumsorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_ID') . ' &darr;', 'id');
        $albumsorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_ID') . ' &uarr;', 'id DESC');
        $albumsorderType->SetDefault($settings['albums_order_type']);

        // Photos Order type
        $photosorderType =& Piwi::CreateWidget('Combo', 'photos_order_type');
        $photosorderType->setContainerClass('oneline');
        $photosorderType->SetTitle(_t('PHOO_PHOTOS_ORDER_TYPE'));
        $photosorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_CREATETIME') . ' &darr;', 'createtime');
        $photosorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_CREATETIME') . ' &uarr;', 'createtime DESC');
        $photosorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_NAME') . ' &darr;', 'title');
        $photosorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_NAME') . ' &uarr;', 'title DESC');
        $photosorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_ID') . ' &darr;', 'id');
        $photosorderType->AddOption(_t('PHOO_ORDER_TYPE_BY_ID') . ' &uarr;', 'id DESC');
        $photosorderType->SetDefault($settings['photos_order_type']);

        // Comments
        $commCombo =& Piwi::CreateWidget('Combo', 'allow_comments');
        $commCombo->setContainerClass('oneline');
        $commCombo->SetTitle(_t('PHOO_COMMENTS'));
        $commCombo->AddOption(_t('GLOBAL_ENABLED'), 'true');
        $commCombo->AddOption(_t('GLOBAL_DISABLED'), 'false');
        $commCombo->SetDefault($settings['allow_comments']);

        // Moderate comments
        $moderateCombo =& Piwi::CreateWidget('Combo', 'comment_status');
        $moderateCombo->setContainerClass('oneline');
        $moderateCombo->SetTitle(_t('PHOO_MODERATE_COMMENTS'));
        $moderateCombo->AddOption(_t('GLOBAL_YES'), 'waiting');
        $moderateCombo->AddOption(_t('GLOBAL_NO'), 'approved');
        $moderateCombo->SetDefault($settings['comment_status']);

        // Keep original
        $keepCombo =& Piwi::CreateWidget('Combo', 'keep_original');
        $keepCombo->setContainerClass('oneline');
        $keepCombo->SetTitle(_t('PHOO_KEEP_ORIGINAL'));
        $keepCombo->AddOption(_t('GLOBAL_ENABLED'), 'true');
        $keepCombo->AddOption(_t('GLOBAL_DISABLED'), 'false');
        $keepCombo->SetDefault($settings['keep_original']);

        // Show EXIF info
        $exifCombo =& Piwi::CreateWidget('Combo', 'show_exif_info');
        $exifCombo->setContainerClass('oneline');
        $exifCombo->SetTitle(_t('PHOO_SHOW_EXIF_INFO'));
        $exifCombo->AddOption(_t('GLOBAL_ENABLED'), 'true');
        $exifCombo->AddOption(_t('GLOBAL_DISABLED'), 'false');
        $exifCombo->SetDefault($settings['show_exif_info']);

        // Moblog
        $albums = $model->GetAlbums('name', 'ASC');
        $moblogAlbumCombo =& Piwi::CreateWidget('Combo', 'moblog_album', _t('PHOO_MOBLOG_ALBUM'));
        $moblogAlbumCombo->setContainerClass('oneline');
        $moblogAlbumCombo->AddOption('&nbsp;', '');
        if (!Jaws_Error::IsError($albums) && !empty($albums)) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($albums as $a) {
                // FIXME: Ugly hack to add title to moblogAlbumCombo
                $o =& Piwi::CreateWidget('ComboOption', $a['name'], $a['name']);
                $o->SetTitle(_t('PHOO_NUM_PHOTOS_ALBUM', $a['howmany']) . ' / '.
                             _t('PHOO_ALBUM_CREATION_DATE') . ': ' . $date->Format($a['createtime']));
                $moblogAlbumCombo->_options[$a['name']] = $o;
            }
        }
        $moblogAlbumCombo->SetDefault($settings['moblog_album']);

        $moblogLimitCombo =& Piwi::CreateWidget('Combo', 'moblog_limit', _t('PHOO_MOBLOG_LIMIT'));
        $moblogLimitCombo->setContainerClass('oneline');
        $moblogLimitCombo->AddOption('5', '5');
        $moblogLimitCombo->AddOption('10', '10');
        $moblogLimitCombo->AddOption('15', '15');
        $moblogLimitCombo->AddOption('20', '20');
        $moblogLimitCombo->SetDefault($settings['moblog_limit']);

        // Photoblog
        $photoblogAlbumCombo =& Piwi::CreateWidget('Combo', 'photoblog_album', _t('PHOO_PHOTOBLOG_ALBUM'));
        $photoblogAlbumCombo->setContainerClass('oneline');
        $photoblogAlbumCombo->AddOption('&nbsp;', '');
        if (!Jaws_Error::IsError($albums)) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($albums as $a) {
                // FIXME: Ugly hack to add title to photoblogAlbumCombo
                $o =& Piwi::CreateWidget('ComboOption', $a['name'], $a['name']);
                $o->SetTitle(_t('PHOO_NUM_PHOTOS_ALBUM', $a['howmany']) . ' / '.
                             _t('PHOO_ALBUM_CREATION_DATE') . ': ' . $date->Format($a['createtime']));
                $photoblogAlbumCombo->_options[$a['name']] = $o;
            }
        }
        $photoblogAlbumCombo->SetDefault($settings['photoblog_album']);

        $photoblogLimitCombo =& Piwi::CreateWidget('Combo', 'photoblog_limit', _t('PHOO_PHOTOBLOG_LIMIT'));
        $photoblogLimitCombo->setContainerClass('oneline');
        $photoblogLimitCombo->AddOption('5', '5');
        $photoblogLimitCombo->AddOption('10', '10');
        $photoblogLimitCombo->AddOption('15', '15');
        $photoblogLimitCombo->AddOption('20', '20');
        $photoblogLimitCombo->SetDefault($settings['photoblog_limit']);

        // Images per Page
        $thumbnailLimitCombo =& Piwi::CreateWidget('Combo', 'thumbnail_limit', _t('PHOO_THUMBNAIL_LIMIT'));
        $thumbnailLimitCombo->setContainerClass('oneline');
        $thumbnailLimitCombo->AddOption(_t('PHOO_FULL_ALBUM'), '0');
        $thumbnailLimitCombo->AddOption('10', '10');
        $thumbnailLimitCombo->AddOption('20', '20');
        $thumbnailLimitCombo->AddOption('40', '40');
        $thumbnailLimitCombo->SetDefault($settings['thumbnail_limit']);

        $fieldset->Add($viewCombo);
        $fieldset->Add($thumbnailLimitCombo);
        $fieldset->Add($statusCombo);
        $fieldset->Add($albumsorderType);
        $fieldset->Add($photosorderType);
        $fieldset->Add($commCombo);
        $fieldset->Add($moderateCombo);
        $fieldset->Add($keepCombo);
        $fieldset->Add($exifCombo);
        $fieldset->Add($moblogAlbumCombo);
        $fieldset->Add($moblogLimitCombo);
        $fieldset->Add($photoblogAlbumCombo);
        $fieldset->Add($photoblogLimitCombo);
        $fieldset->SetDirection('vertical');
        $form->Add($fieldset);

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
        $buttonbox->PackStart($reset);
        $buttonbox->PackStart($save);

        $form->Add($buttonbox);

        $tpl->SetVariable('form', $form->Get());

        $tpl->ParseBlock('additional');
        return $tpl->Get();
    }

    /**
     * Applies modifications on blog settings
     *
     * @access  public
     */
    function SaveAdditionalSettings()
    {
        $this->gadget->CheckPermission('Settings');

        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('default_action', 'published', 'allow_comments', 'moblog_album',
                                       'moblog_limit', 'photoblog_album',  'photoblog_limit',
                                       'show_exif_info', 'keep_original', 'thumbnail_limit',
                                       'comment_status', 'albums_order_type', 'photos_order_type'), 'post');

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $model->SaveSettings(
                             $post['default_action'],
                             $post['published'],
                             $post['allow_comments'],
                             $post['moblog_album'],
                             $post['moblog_limit'],
                             $post['photoblog_album'],
                             $post['photoblog_limit'],
                             $post['show_exif_info'],
                             $post['keep_original'],
                             $post['thumbnail_limit'],
                             $post['comment_status'],
                             $post['albums_order_type'],
                             $post['photos_order_type']
                             );

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&action=AdditionalSettings');
    }

    /**
     * Import pictures in 'import' folder
     * 
     * @access  public
     * @return  string   XHTML template content
     */
    function Import()
    {
        $this->gadget->CheckPermission('Import');
        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('Import.html');
        $tpl->SetBlock('import');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('menubar', $this->MenuBar('Import'));
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $items = $model->GetItemsToImport();
        if (count($items) > 0) {
            $tpl->SetBlock('import/pictures');
            $tpl->SetVariable('ready_to_import', _t('PHOO_READY_TO_IMPORT', count($items)));
            $gadget =& Piwi::CreateWidget('HiddenEntry', 'gadget', 'Phoo');
            $tpl->SetVariable ('gadget_hidden', $gadget->Get());
            $action =& Piwi::CreateWidget('HiddenEntry', 'action', 'FinishImport');
            $tpl->SetVariable ('action_hidden', $action->Get());
            $tpl->SetVariable ('import_message', _t('PHOO_IMPORT_MESSAGE'));
            $albumcombo =& Piwi::CreateWidget('Combo', 'album', _t('PHOO_ALBUM'));
            $first = false;
            $albums = $model->GetAlbums('name', 'ASC');
            if (!Jaws_Error::IsError($albums) && !empty($albums)) {
                foreach ($albums as $a) {
                    if (!$first) {
                        $first = $a['id'];
                    }
                    $albumcombo->AddOption($a['name'], $a['id']);
                }
            }
            $albumcombo->SetDefault($first);
            $tpl->SetVariable ('albums_combo', $albumcombo->Get());
            $b =& Piwi::CreateWidget('Button', 'import_button', _t('PHOO_IMPORT'), STOCK_DOWN);
            $b->SetSubmit(true);
            $tpl->SetVariable ('import_button', $b->Get());
            $counter = 0;
            include_once JAWS_PATH . 'include/Jaws/Image.php';
            foreach ($items as $i) {
                $tpl->SetBlock('import/pictures/item');
                $tpl->SetVariable('thumb', BASE_SCRIPT . '?gadget=Phoo&amp;action=Thumb&amp;image='.$i);
                $tpl->SetVariable('filename', $i);
                $tpl->SetVariable('entryname', md5($i));
                $tpl->SetVariable('counter',(string)$counter);
                $tpl->ParseBlock('import/pictures/item');
                $counter++;
            }
            $tpl->ParseBlock('import/pictures');
        } else {
            $tpl->SetBlock('import/noitems');
            $tpl->SetVariable('no_items_to_import', _t('PHOO_NO_IMAGES_TO_IMPORT'));
            $tpl->SetVariable('message', _t('PHOO_IMPORT_INSTRUCTIONS'));
            $tpl->ParseBlock('import/noitems');
        }
        $tpl->ParseBlock('import');
        return $tpl->Get();
    }

    /**
     * Import selected images
     *
     * @access  public
     * @return  string   XHTML with the results of the importation
     */
    function FinishImport()
    {
        $this->gadget->CheckPermission('Import');
        $this->AjaxMe('script.js');

        $request =& Jaws_Request::getInstance();
        $post    = $request->get(array('album', 'images'), 'post');

        $tpl = new Jaws_Template('gadgets/Phoo/templates/');
        $tpl->Load('FinishImport.html');
        $tpl->SetBlock('finishimport');
        $tpl->SetVariable('menubar', $this->MenuBar('Import'));
        $tpl->SetVariable('importing', _t('PHOO_IMPORTING'));
        $tpl->SetVariable('album', $post['album']);
        $tpl->SetVariable('howmany', (string)count($post['images']));
        $tpl->SetVariable('indicator_image', 'gadgets/ControlPanel/images/indicator.gif');
        $tpl->SetVariable('ok_image', STOCK_OK);
        $tpl->SetVariable('finished', _t('PHOO_FINISHED'));
        $tpl->SetVariable('import_warning', _t('PHOO_IMPORTING_WARNING'));
        $counter = 0;
        foreach ($post['images'] as $image) {
            $tpl->SetBlock('finishimport/items');
            $tpl->SetVariable('counter', (string)$counter);
            $tpl->SetVariable('image', $image);
            $tpl->SetVariable('name',  md5($image));
            $tpl->ParseBlock('finishimport/items');
            $counter++;
        }
        $tpl->ParseBlock('finishimport');
        return $tpl->Get();
    }

}