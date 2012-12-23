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
class Phoo_Actions_Admin_SelectImage extends PhooAdminHTML
{
    /**
     * Show the image selected from BrowsePhoo with some options to insert.
     *
     * @access  public
     * @return  string   XHTML with the image selected and it's options
     */
    function SelectImage()
    {
        $request =& Jaws_Request::getInstance();
        $iGet = $request->get(array('image', 'album'), 'get');
        if (empty($iGet['image']) || empty($iGet['album'])) {
            return false;
        }

        $model = $GLOBALS['app']->LoadGadget('Phoo', 'AdminModel');
        $t = new Jaws_Template('gadgets/Phoo/templates/');
        $t->Load('SelectImage.html');
        $t->SetBlock('ImageSelect');

        $GLOBALS['app']->LoadPlugin('PhooInsert');
        $t->SetVariable('page-title', _t('PLUGINS_PHOOINSERT_PHOTO_SELECT'));

        $dir = _t('GLOBAL_LANG_DIRECTION');
        $t->SetVariable('.dir', ($dir == 'rtl')? '.' . $dir : '');

        $extraParams = '';
        $editor = $GLOBALS['app']->GetEditor();
        if ($editor === 'TinyMCE') {
            $t->SetBlock('ImageSelect/script');
            $t->ParseBlock('ImageSelect/script');
        } elseif ($editor === 'CKEditor') {
            $getParams = $request->get(array('CKEditor', 'CKEditorFuncNum', 'langCode'), 'get');
            $extraParams = '&amp;CKEditor=' . $getParams['CKEditor'] .
                           '&amp;CKEditorFuncNum=' . $getParams['CKEditorFuncNum'] .
                           '&amp;langCode=' . $getParams['langCode'];

            $ckFuncIndex = $request->get('CKEditorFuncNum', 'get');
            $t->SetVariable('ckFuncIndex', $ckFuncIndex);
        }

        $image = $model->GetImageEntry($iGet['image']);
        if (Jaws_Error::IsError ($image)) {
            $GLOBALS['app']->Session->PushLastResponse($image->GetMessage(), RESPONSE_ERROR);
            JawsHeader::Location ("admin.php?gadget=Phoo&action=Admin");
        }
        $request =& Jaws_Request::getInstance();
        $album   = $request->get('album', 'get');
        $post    = $request->get(array('date', 'album'), 'post');
        if (isset($post['album'])) {
            $r_album = $post['album'];
        } else {
            $r_album = isset($album) ? $album : null;
        }
        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetClass('hbox');
        $submit =& Piwi::CreateWidget('Button', 'other_pic_button', _t('PHOO_SELECT_OTHER_IMAGE'), STOCK_LEFT);
        $submit->SetSubmit();
        if (empty($image)) {
            $t->SetBlock('ImageSelect/not_published');
            $t->SetVariable('not_published_label', _t('PHOO_NOT_PUBLISHED'));
            if (isset($r_album)){
                $t->SetVariable('album', $r_album);
            }
            $buttonbox->Add($submit);
            $t->SetVariable('button_bar',$buttonbox->Get());
            $t->ParseBlock('ImageSelect/not_published');
        } else {
            $t->SetBlock('ImageSelect/selected');
            $t->SetVariable('extra_params', $extraParams);
            $filename = $GLOBALS['app']->getDataURL('phoo/' . $image['image']);
            $title = (empty($image['title']))? '' : $image['title'];
            $desc = $image['description'];
            if (isset($r_album)){
                $t->SetVariable('album',$r_album);
            }
            $t->SetVariable('t_title',            _t('PHOO_PHOTO_TITLE'));
            $t->SetVariable('t_desc',             _t('GLOBAL_DESCRIPTION'));
            $t->SetVariable('t_size',             _t('PHOO_SIZE'));
            $t->SetVariable('t_thumb',            _t('PHOO_THUMB'));
            $t->SetVariable('t_medium',           _t('PHOO_MEDIUM'));
            $t->SetVariable('insert_image_title', _t('PHOO_INSERTIMAGE'));
            $t->SetVariable('s_image',            $GLOBALS['app']->getDataURL('phoo/' . $image['medium']));
            $t->SetVariable('s_name',             $title);
            $t->SetVariable('s_desc',             $desc);
            $t->SetVariable('s_picture',          $image['id']);
            $t->SetVariable('s_album',            $r_album);

            if ($editor === 'TextArea') {
                $t->SetBlock('ImageSelect/selected/linked');
                $t->SetVariable('include_link', _t('PHOO_INCLUDE_LINK_TO_ALBUM'));
                $t->ParseBlock('ImageSelect/selected/linked');
            }

            $insert_pic =& Piwi::CreateWidget('Button', 'insert_pic__button', _t('PHOO_INSERTIMAGE'), STOCK_SAVE);
            $insert_pic->AddEvent(ON_CLICK, "insertImage('$filename', 
                                                         this.form.s_title.value, 
                                                         this.form.s_desc.value, 
                                                         this.form.s_size.value, 
                                                         this.form.linked.value, 
                                                         '$editor');");
            $buttonbox->Add($submit);
            $buttonbox->Add($insert_pic);
            $t->SetVariable('button_bar',$buttonbox->Get());
            if ($this->gadget->GetRegistry('keep_original') == 'true') {
                $t->SetBlock('ImageSelect/selected/original');
                $t->SetVariable('t_original',_t('PHOO_ORIGINAL'));
                $t->ParseBlock('ImageSelect/selected/original');
            }
            $t->ParseBlock('ImageSelect/selected');
        }

        $t->ParseBlock('ImageSelect');
        return $t->Get();
    }

}