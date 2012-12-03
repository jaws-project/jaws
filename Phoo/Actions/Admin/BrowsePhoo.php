<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_BrowsePhoo extends PhooAdminHTML
{
    /**
     * Browse across albums and images from Phoo
     *
     * @access  public
     * @return  string   XHTML with the list of albums and images appropiate for navigation
     */
    function BrowsePhoo()
    {
        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('PhooBrowse.html');
        $t->SetBlock('phoo_browse');

        $GLOBALS['app']->LoadPlugin('PhooInsert');
        $t->SetVariable('page-title', _t('PLUGINS_PHOOINSERT_PHOTO_GALLERY'));

        $dir = _t('GLOBAL_LANG_DIRECTION');
        $t->SetVariable('.dir', ($dir == 'rtl')? '.' . $dir : '');

        $request =& Jaws_Request::getInstance();
        $album   = $request->get('album', 'get');
        $post    = $request->get(array('date', 'album'), 'post');
        $albums  = $model->GetAlbums('createtime','ASC');

        // TODO set default value for change page address to correct location after uploading image
        $extraParams = '&amp;';
        $editor = $GLOBALS['app']->GetEditor();
        if ($editor === 'CKEditor') {
            $extraParams = $request->get('extra_params');
            if(empty($extraParams)) {
            $getParams = $request->get(array('CKEditor', 'CKEditorFuncNum', 'langCode'), 'get');
            $extraParams = '&amp;CKEditor='.$getParams['CKEditor'].
                           '&amp;CKEditorFuncNum='.$getParams['CKEditorFuncNum'].
                           '&amp;langCode='.$getParams['langCode'];
            }
        }

        if ($this->GetPermission('AddPhotos') && count($albums)>0) {
            $t->SetBlock("phoo_browse/upload_photo");
            $t->SetVariable('base_script', BASE_SCRIPT);
            $t->SetVariable('extra_params', $extraParams);
            $t->SetVariable('lbl_file_upload', _t('PHOO_UPLOAD_PHOTO'));

            $uploadfile =& Piwi::CreateWidget('FileEntry', 'photo1', '');
            $uploadfile->SetID('photo1');
            $t->SetVariable('lbl_filename', _t('PHOO_IMAGE_LABEL'));
            $t->SetVariable('uploadfile', $uploadfile->Get());

            $btnSave =& Piwi::CreateWidget('Button', 'btn_upload_file', _t('PHOO_UPLOAD_PHOTOS'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, "javascript: uploadPhoto();");
            $t->SetVariable('btn_upload_file', $btnSave->Get());
            $t->ParseBlock("phoo_browse/upload_photo");
        }

        if (!Jaws_Error::IsError($albums) && !empty($albums)) {
            $objDate = $GLOBALS['app']->loadDate();
            $t->SetBlock ("phoo_browse/photos");
            $t->SetVariable('extra_params', $extraParams);

            $datecombo =& Piwi::CreateWidget('Combo', 'date');
            $datecombo->SetStyle('width: 200px;');
            $datecombo->AddOption ('&nbsp;','');
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
                        if ($year == $maxdateArray[2])
                                $mstart = $maxdateArray[0];
                        else 
                                $mstart = 12;
                        if ($year == $mindateArray[2])
                                $mend = $mindateArray[0];
                        else 
                                $mend = 1;
                    }
                    for ($m = $mstart; $m >= $mend; $m--) {
                        $month = $objDate->MonthString ($m);
                        $datecombo->AddOption ("{$month} {$year}","{$m}/{$year}");
                    }
                }
            }
            $t->SetVariable("date",_t("GLOBAL_DATE"));
            $datecombo->SetDefault(isset($post['date']) ? $post['date'] : null);
            $datecombo->AddEvent (new JSEvent (ON_CHANGE, "selectAllAlbums(); this.form.submit();"));
            $t->SetVariable("date_combo",$datecombo->Get());

            $albumcombo =& Piwi::CreateWidget('Combo', 'album[]');
            $albumcombo->SetID('albums');
            $albumcombo->SetStyle('width: 200px;');
            $albumcombo->SetSize(12);
            $albumcombo->SetMultiple(true);
            
            $firstAlbum = null;
            foreach ($albums as $a) {
                if (is_null($firstAlbum)) {
                    $firstAlbum = $a['id'];
                }
                // FIXME: Ugly hack to add title to albumcombo
                $o =& Piwi::CreateWidget('ComboOption', $a['id'], $a['name']);
                $o->SetTitle(_t('PHOO_NUM_PHOTOS_ALBUM', $a['howmany']) . ' / '.
                            _t('PHOO_ALBUM_CREATION_DATE'). ' '.$objDate->Format($a['createtime']));
                $albumcombo->_options[$a['id']] = $o;
            }

            // r_album = request album
            if (isset($post['album'])) {
                $r_album = $post['album'];
            } else {
                $r_album = isset($album) ? $album : $firstAlbum;
            }

            $t->SetVariable('incompleteFields', _t('GLOBAL_ERROR_INCOMPLETE_FIELDS'));
            // Use for uploading image
            if (is_array($r_album)) {
                $t->SetVariable('defaultAlbum', $r_album[0]);
            } else {
                $t->SetVariable('defaultAlbum', $r_album);
            }


            $albumcombo->SetDefault($r_album);
            $albumcombo->AddEvent (new JSEvent (ON_CHANGE, "document.album_form.submit();"));
            $t->SetVariable('albums', _t('PHOO_ALBUMS'));
            $t->SetVariable('albums_combo', $albumcombo->Get());

            // Ugly hack to convert $r_album to array...
            if (!empty($r_album) && !is_array($r_album)) {
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
                    if (!Jaws_Error::IsError($album) && !empty($album)) {
                        if ((isset($album['images']) && !is_array($album['images'])) &&
                           (count($album['images']) == 0) && (checkdate($month, 1, $year))) {
                            continue;
                        }

                        $t->SetBlock ('phoo_browse/photos/albums');
                        $t->SetVariable ('title', $album['name']);
                        $t->SetVariable ('description',$this->ParseText($album['description'], 'Phoo'));
                        $t->SetVariable ('createtime', $objDate->Format($album['createtime']));

                        if ((isset($album['images']) && is_array($album['images'])) &&(count($album['images']) > 0)) {
                            // Show photos
                            foreach ($album['images'] as $img) {
                                $imgData = Jaws_Image::get_image_details(JAWS_DATA . 'phoo/' . $img['thumb']);
                                $t->SetBlock ('phoo_browse/photos/albums/item');
                                $t->SetVariable ('url',
                                                 "admin.php?gadget=Phoo&amp;action=SelectImage&amp;".
                                                 "image={$img["id"]}&amp;album={$albumId}". $extraParams);
                                if (Jaws_Error::IsError($imgData)) {
                                    $t->SetVariable('thumb',  'images/unknown.png');
                                    $t->SetVariable('width',  60);
                                    $t->SetVariable('height', 60);
                                    $t->SetBlock('phoo_browse/photos/albums/item/notfound');
                                    $t->SetVariable('notfound', _t('PHOO_NOT_FOUND'));
                                    $t->ParseBlock('phoo_browse/photos/albums/item/notfound');
                                } else {
                                    $t->SetVariable('thumb',  $GLOBALS['app']->getDataURL('phoo/' . $img['thumb']));
                                    $t->SetVariable('width',  $imgData[0]);
                                    $t->SetVariable('height', $imgData[1]);
                                }
                                $t->SetVariable('name',   $img['name']);
                                $t->SetVariable('album',  $img['albumid']);
                                if ($img['published'] == false) {
                                    $t->SetBlock('phoo_browse/photos/albums/item/notpublished');
                                    $t->SetVariable('notpublished', _t('PHOO_NOT_PUBLISHED'));
                                    $t->ParseBlock('phoo_browse/photos/albums/item/notpublished');
                                }
                                $t->ParseBlock('phoo_browse/photos/albums/item');
                            }
                        } else {
                            $t->SetBlock('phoo_browse/photos/albums/nophotos');
                            $t->SetVariable('message', _t('PHOO_ALBUM_EMPTY'));
                            $t->ParseBlock('phoo_browse/photos/albums/nophotos');
                        }
                        $t->ParseBlock('phoo_browse/photos/albums');
                    }
                }
            }

            // Get failures
            $failures = $GLOBALS['app']->Session->GetAttribute('failures');
            if (is_array($failures) && count($failures) > 0) {
                foreach ($failures as $f) {
                    $t->SetBlock('phoo_browse/photos/failures');
                    $t->SetVariable('message', $f);
                    $t->ParseBlock('phoo_browse/photos/failures');
                }
            }

            // Delete key
            $GLOBALS['app']->Session->DeleteAttribute('failures');
            $t->ParseBlock('phoo_browse/photos');
        } else {
            $t->SetBlock('phoo_browse/noalbums');
            $t->SetVariable('message', _t('PHOO_EMPTY_ALBUMSET'));
            $t->ParseBlock('phoo_browse/noalbums');
        }

        // clear pushed message
        $GLOBALS['app']->Session->PopLastResponse();

        $t->ParseBlock('phoo_browse');
        return $t->Get();
    }

}