<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_SelectImage extends Phoo_Actions_Admin_Default
{
    /**
     * Show the image selected from BrowsePhoo with some options to insert.
     *
     * @access  public
     * @return  string   XHTML with the image selected and it's options
     */
    function SelectImage()
    {
        $iGet = $this->gadget->request->fetch(array('image', 'album'), 'get');
        if (empty($iGet['image']) || empty($iGet['album'])) {
            return false;
        }

        $model = $this->gadget->model->load('Photos');
        $tpl = $this->gadget->template->loadAdmin('SelectImage.html');
        $tpl->SetBlock('ImageSelect');
        $tpl->SetVariable('page-title', _t('PLUGINS_PHOOINSERT_PHOTO_SELECT'));

        $dir = Jaws::t('LANG_DIRECTION');
        $tpl->SetVariable('.dir', ($dir == 'rtl')? '.' . $dir : '');

        $extraParams = '';
        $editor = $this->app->getEditor();
        if ($editor === 'TinyMCE') {
            $tpl->SetBlock('ImageSelect/script');
            $tpl->ParseBlock('ImageSelect/script');
        } elseif ($editor === 'CKEditor') {
            $getParams = $this->gadget->request->fetch(array('CKEditor', 'CKEditorFuncNum', 'langCode'), 'get');
            $extraParams = '&amp;CKEditor=' . $getParams['CKEditor'] .
                           '&amp;CKEditorFuncNum=' . $getParams['CKEditorFuncNum'] .
                           '&amp;langCode=' . $getParams['langCode'];

            $ckFuncIndex = $this->gadget->request->fetch('CKEditorFuncNum', 'get');
            $tpl->SetVariable('ckFuncIndex', $ckFuncIndex);
        }

        $image = $model->GetImageEntry($iGet['image']);
        if (Jaws_Error::IsError ($image)) {
            $this->gadget->session->push($image->GetMessage(), RESPONSE_ERROR);
            JawsHeader::Location ("admin.php?reqGadget=Phoo");
        }
        $album = $this->gadget->request->fetch('album', 'get');
        $post  = $this->gadget->request->fetch(array('date', 'album'), 'post');
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
            $tpl->SetBlock('ImageSelect/not_published');
            $tpl->SetVariable('not_published_label', _t('PHOO_NOT_PUBLISHED'));
            if (isset($r_album)){
                $tpl->SetVariable('album', $r_album);
            }
            $buttonbox->Add($submit);
            $tpl->SetVariable('button_bar',$buttonbox->Get());
            $tpl->ParseBlock('ImageSelect/not_published');
        } else {
            $tpl->SetBlock('ImageSelect/selected');
            $tpl->SetVariable('extra_params', $extraParams);
            $filename = $this->app->getDataURL('phoo/' . $image['image']);
            $title = (empty($image['title']))? '' : $image['title'];
            $desc = $image['description'];
            if (isset($r_album)){
                $tpl->SetVariable('album',$r_album);
            }
            $tpl->SetVariable('t_title',            _t('PHOO_PHOTO_TITLE'));
            $tpl->SetVariable('t_desc',             Jaws::t('DESCRIPTION'));
            $tpl->SetVariable('t_size',             _t('PHOO_SIZE'));
            $tpl->SetVariable('t_thumb',            _t('PHOO_THUMB'));
            $tpl->SetVariable('t_medium',           _t('PHOO_MEDIUM'));
            $tpl->SetVariable('insert_image_title', _t('PHOO_INSERTIMAGE'));
            $tpl->SetVariable('s_image',            $this->app->getDataURL('phoo/' . $image['medium']));
            $tpl->SetVariable('s_name',             $title);
            $tpl->SetVariable('s_desc',             $desc);
            $tpl->SetVariable('s_picture',          $image['id']);
            $tpl->SetVariable('s_album',            $r_album);

            if ($editor === 'TextArea') {
                $tpl->SetBlock('ImageSelect/selected/linked');
                $tpl->SetVariable('include_link', _t('PHOO_INCLUDE_LINK_TO_ALBUM'));
                $tpl->ParseBlock('ImageSelect/selected/linked');
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
            $tpl->SetVariable('button_bar',$buttonbox->Get());
            if ($this->gadget->registry->fetch('keep_original') == 'true') {
                $tpl->SetBlock('ImageSelect/selected/original');
                $tpl->SetVariable('t_original',_t('PHOO_ORIGINAL'));
                $tpl->ParseBlock('ImageSelect/selected/original');
            }
            $tpl->ParseBlock('ImageSelect/selected');
        }

        $tpl->ParseBlock('ImageSelect');
        return $tpl->Get();
    }

}