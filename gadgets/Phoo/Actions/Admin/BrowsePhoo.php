<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright   2004-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_BrowsePhoo extends Phoo_Actions_Admin_Default
{
    /**
     * Browse across albums and images from Phoo
     *
     * @access  public
     * @return  string   XHTML with the list of albums and images appropiate for navigation
     */
    function BrowsePhoo()
    {
        $pModel = $this->gadget->model->load('Photos');
        $aModel = $this->gadget->model->load('Albums');
        $tpl = $this->gadget->template->loadAdmin('PhooBrowse.html');
        $tpl->SetBlock('phoo_browse');
        $tpl->SetVariable('page-title', Jaws::t('PLUGINS.PHOOINSERT.PHOTO_GALLERY'));

        $dir = Jaws::t('LANG_DIRECTION');
        $tpl->SetVariable('.dir', ($dir == 'rtl')? '.' . $dir : '');

        $album   = $this->gadget->request->fetch('album', 'get');
        $post    = $this->gadget->request->fetch(array('date', 'album:array'), 'post');
        $albums  = $aModel->GetAlbums('createtime','ASC');

        // TODO set default value for change page address to correct location after uploading image
        $extraParams = '&amp;';
        $editor = $this->app->getEditor();
        if ($editor === 'CKEditor') {
            $extraParams = $this->gadget->request->fetch('extra_params');
            if(empty($extraParams)) {
            $getParams = $this->gadget->request->fetch(array('CKEditor', 'CKEditorFuncNum', 'langCode'), 'get');
            $extraParams = '&amp;CKEditor='.$getParams['CKEditor'].
                           '&amp;CKEditorFuncNum='.$getParams['CKEditorFuncNum'].
                           '&amp;langCode='.$getParams['langCode'];
            }
        }

        if ($this->gadget->GetPermission('AddPhotos') && count($albums)>0) {
            $tpl->SetBlock("phoo_browse/upload_photo");
            $tpl->SetVariable('base_script', BASE_SCRIPT);
            $tpl->SetVariable('extra_params', $extraParams);
            $tpl->SetVariable('lbl_file_upload', $this::t('UPLOAD_PHOTO'));

            $uploadfile =& Piwi::CreateWidget('FileEntry', 'photo1', '');
            $uploadfile->SetID('photo1');
            $tpl->SetVariable('lbl_filename', $this::t('IMAGE_LABEL'));
            $tpl->SetVariable('uploadfile', $uploadfile->Get());

            $btnSave =& Piwi::CreateWidget('Button', 'btn_upload_file', $this::t('UPLOAD_PHOTOS'), STOCK_SAVE);
            $btnSave->AddEvent(ON_CLICK, "javascript:uploadPhoto();");
            $tpl->SetVariable('btn_upload_file', $btnSave->Get());
            $tpl->ParseBlock("phoo_browse/upload_photo");
        }

        if (!Jaws_Error::IsError($albums) && !empty($albums)) {
            $objDate = Jaws_Date::getInstance();
            $tpl->SetBlock ("phoo_browse/photos");
            $tpl->SetVariable('extra_params', $extraParams);

            $datecombo =& Piwi::CreateWidget('Combo', 'date');
            $datecombo->SetStyle('width: 200px;');
            $datecombo->AddOption ('&nbsp;','');
            $mindate = $pModel->GetMinDate();
            if ($mindate) {
                $maxdate = $pModel->GetMaxDate();
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
            $tpl->SetVariable('date', Jaws::t('DATE'));
            $datecombo->SetDefault(isset($post['date']) ? $post['date'] : null);
            $datecombo->AddEvent (new JSEvent (ON_CHANGE, "selectAllAlbums(); this.form.submit();"));
            $tpl->SetVariable("date_combo",$datecombo->Get());

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
                $o->SetTitle($this::t('NUM_PHOTOS_ALBUM', $a['howmany']) . ' / '.
                            $this::t('ALBUM_CREATION_DATE'). ' '.$objDate->Format($a['createtime']));
                $albumcombo->_options[$a['id']] = $o;
            }

            // r_album = request album
            if (isset($post['album'])) {
                $r_album = $post['album'];
            } else {
                $r_album = isset($album) ? $album : $firstAlbum;
            }

            $tpl->SetVariable('incompleteFields', Jaws::t('ERROR_INCOMPLETE_FIELDS'));
            // Use for uploading image
            if (is_array($r_album)) {
                $tpl->SetVariable('defaultAlbum', $r_album[0]);
            } else {
                $tpl->SetVariable('defaultAlbum', $r_album);
            }


            $albumcombo->SetDefault($r_album);
            $albumcombo->AddEvent (new JSEvent (ON_CHANGE, "document.album_form.submit();"));
            $tpl->SetVariable('albums', $this::t('ALBUMS'));
            $tpl->SetVariable('albums_combo', $albumcombo->Get());

            // Ugly hack to convert $r_album to array...
            if (!empty($r_album) && !is_array($r_album)) {
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
                    $album = $pModel->GetAlbumImages($albumId, null, $day, $month, $year);
                    if (!Jaws_Error::IsError($album) && !empty($album)) {
                        if ((isset($album['images']) && !is_array($album['images'])) &&
                           (count($album['images']) == 0) && (checkdate($month, 1, $year))) {
                            continue;
                        }

                        $tpl->SetBlock ('phoo_browse/photos/albums');
                        $tpl->SetVariable ('title', $album['name']);
                        $tpl->SetVariable ('description',$this->gadget->plugin->parseAdmin($album['description']));
                        $tpl->SetVariable ('createtime', $objDate->Format($album['createtime']));

                        if ((isset($album['images']) && is_array($album['images'])) &&(count($album['images']) > 0)) {
                            // Show photos
                            foreach ($album['images'] as $img) {
                                $imgData = Jaws_Image::getimagesize(ROOT_DATA_PATH . 'phoo/' . $img['thumb']);
                                $tpl->SetBlock ('phoo_browse/photos/albums/item');
                                $tpl->SetVariable ('url',
                                                 "admin.php?reqGadget=Phoo&amp;reqAction=SelectImage&amp;".
                                                 "image={$img["id"]}&amp;album={$albumId}". $extraParams);
                                if (Jaws_Error::IsError($imgData)) {
                                    $tpl->SetVariable('thumb',  'images/unknown.png');
                                    $tpl->SetVariable('width',  60);
                                    $tpl->SetVariable('height', 60);
                                    $tpl->SetBlock('phoo_browse/photos/albums/item/notfound');
                                    $tpl->SetVariable('notfound', $this::t('NOT_FOUND'));
                                    $tpl->ParseBlock('phoo_browse/photos/albums/item/notfound');
                                } else {
                                    $tpl->SetVariable('thumb',  $this->app->getDataURL('phoo/' . $img['thumb']));
                                    $tpl->SetVariable('width',  $imgData[0]);
                                    $tpl->SetVariable('height', $imgData[1]);
                                }
                                $tpl->SetVariable('name',   $img['name']);
                                $tpl->SetVariable('album',  $img['albumid']);
                                if ($img['published'] == false) {
                                    $tpl->SetBlock('phoo_browse/photos/albums/item/notpublished');
                                    $tpl->SetVariable('notpublished', $this::t('NOT_PUBLISHED'));
                                    $tpl->ParseBlock('phoo_browse/photos/albums/item/notpublished');
                                }
                                $tpl->ParseBlock('phoo_browse/photos/albums/item');
                            }
                        } else {
                            $tpl->SetBlock('phoo_browse/photos/albums/nophotos');
                            $tpl->SetVariable('message', $this::t('ALBUM_EMPTY'));
                            $tpl->ParseBlock('phoo_browse/photos/albums/nophotos');
                        }
                        $tpl->ParseBlock('phoo_browse/photos/albums');
                    }
                }
            }

            // Get failures
            $failures = $this->gadget->session->failures;
            if (is_array($failures) && count($failures) > 0) {
                foreach ($failures as $f) {
                    $tpl->SetBlock('phoo_browse/photos/failures');
                    $tpl->SetVariable('message', $f);
                    $tpl->ParseBlock('phoo_browse/photos/failures');
                }
            }

            // Delete key
            $this->app->session->deleteAttribute('failures');
            $tpl->ParseBlock('phoo_browse/photos');
        } else {
            $tpl->SetBlock('phoo_browse/noalbums');
            $tpl->SetVariable('message', $this::t('EMPTY_ALBUMSET'));
            $tpl->ParseBlock('phoo_browse/noalbums');
        }

        // clear pushed message
        $this->gadget->session->pop();

        $tpl->ParseBlock('phoo_browse');
        return $tpl->Get();
    }

}