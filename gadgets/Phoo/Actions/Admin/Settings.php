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
class Phoo_Actions_Admin_Settings extends Phoo_Actions_Admin_Default
{
    /**
     * Displays phoo settings administration panel
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function AdditionalSettings()
    {
        $this->gadget->CheckPermission('Settings');
        $tpl = $this->gadget->template->loadAdmin('AdditionalSettings.html');
        $tpl->SetBlock('additional');

        // Header
        $tpl->SetVariable('menubar',$this->MenuBar('AdditionalSettings'));

        $form =& Piwi::CreateWidget('Form', BASE_SCRIPT, 'post');
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'reqGadget', 'Phoo'));
        $form->Add(Piwi::CreateWidget('HiddenEntry', 'reqAction', 'SaveAdditionalSettings'));

        include_once ROOT_JAWS_PATH . 'include/Jaws/Widgets/FieldSet.php';
        $fieldset = new Jaws_Widgets_FieldSet($this::t('ADDITIONAL_SETTINGS'));

        // Save Button
        $save =& Piwi::CreateWidget('Button', 'save', $this::t('SAVE_SETTINGS'), STOCK_SAVE);
        $save->SetSubmit();

        // Reset Button
        $reset =& Piwi::CreateWidget('Button', 'reset', Jaws::t('RESET'), STOCK_RESET);
        $reset->setReset(true);

        $sModel = $this->gadget->model->load('Settings');
        $aModel = $this->gadget->model->load('Albums');
        $settings = $sModel->GetSettings();

        // Default View
        $tpl->SetVariable('label', $this::t('DEFAULT_VIEW'));
        $viewCombo =& Piwi::CreateWidget('Combo', 'default_action');
        $viewCombo->setContainerClass('oneline');
        $viewCombo->SetTitle($this::t('DEFAULT_VIEW'));
        $viewCombo->AddOption($this::t('ALBUM_LIST'), 'Albums');
        $viewCombo->AddOption($this::t('PHOTOBLOG'), 'PhotoblogPortrait');
        $viewCombo->SetDefault($settings['default_action']);

        // Default status
        $statusCombo =& Piwi::CreateWidget('Combo', 'published');
        $statusCombo->setContainerClass('oneline');
        $statusCombo->SetTitle($this::t('DEFAULT_STATUS'));
        $statusCombo->AddOption($this::t('PUBLISHED'), 'true');
        $statusCombo->AddOption($this::t('HIDDEN'), 'false');
        $statusCombo->SetDefault($settings['published']);

        // Albums Order type
        $albumsorderType =& Piwi::CreateWidget('Combo', 'albums_order_type');
        $albumsorderType->setContainerClass('oneline');
        $albumsorderType->SetTitle($this::t('ALBUMS_ORDER_TYPE'));
        $albumsorderType->AddOption($this::t('ORDER_TYPE_BY_CREATETIME') . ' &darr;', 'createtime');
        $albumsorderType->AddOption($this::t('ORDER_TYPE_BY_CREATETIME') . ' &uarr;', 'createtime desc');
        $albumsorderType->AddOption($this::t('ORDER_TYPE_BY_NAME') . ' &darr;', 'name');
        $albumsorderType->AddOption($this::t('ORDER_TYPE_BY_NAME') . ' &uarr;', 'name desc');
        $albumsorderType->AddOption($this::t('ORDER_TYPE_BY_ID') . ' &darr;', 'id');
        $albumsorderType->AddOption($this::t('ORDER_TYPE_BY_ID') . ' &uarr;', 'id desc');
        $albumsorderType->SetDefault($settings['albums_order_type']);

        // Photos Order type
        $photosorderType =& Piwi::CreateWidget('Combo', 'photos_order_type');
        $photosorderType->setContainerClass('oneline');
        $photosorderType->SetTitle($this::t('PHOTOS_ORDER_TYPE'));
        $photosorderType->AddOption($this::t('ORDER_TYPE_BY_CREATETIME') . ' &darr;', 'createtime');
        $photosorderType->AddOption($this::t('ORDER_TYPE_BY_CREATETIME') . ' &uarr;', 'createtime desc');
        $photosorderType->AddOption($this::t('ORDER_TYPE_BY_NAME') . ' &darr;', 'title');
        $photosorderType->AddOption($this::t('ORDER_TYPE_BY_NAME') . ' &uarr;', 'title desc');
        $photosorderType->AddOption($this::t('ORDER_TYPE_BY_ID') . ' &darr;', 'id');
        $photosorderType->AddOption($this::t('ORDER_TYPE_BY_ID') . ' &uarr;', 'id desc');
        $photosorderType->SetDefault($settings['photos_order_type']);

        // Comments
        $commCombo =& Piwi::CreateWidget('Combo', 'allow_comments');
        $commCombo->setContainerClass('oneline');
        $commCombo->SetTitle($this::t('COMMENTS'));
        $commCombo->AddOption(Jaws::t('ENABLED'), 'true');
        $commCombo->AddOption(Jaws::t('DISABLED'), 'false');
        $commCombo->SetDefault($settings['allow_comments']);

        // Moderate comments
        $moderateCombo =& Piwi::CreateWidget('Combo', 'comment_status');
        $moderateCombo->setContainerClass('oneline');
        $moderateCombo->SetTitle($this::t('MODERATE_COMMENTS'));
        $moderateCombo->AddOption(Jaws::t('YESS'), 'waiting');
        $moderateCombo->AddOption(Jaws::t('NOO'), 'approved');
        $moderateCombo->SetDefault($settings['comment_status']);

        // Keep original
        $keepCombo =& Piwi::CreateWidget('Combo', 'keep_original');
        $keepCombo->setContainerClass('oneline');
        $keepCombo->SetTitle($this::t('KEEP_ORIGINAL'));
        $keepCombo->AddOption(Jaws::t('ENABLED'), 'true');
        $keepCombo->AddOption(Jaws::t('DISABLED'), 'false');
        $keepCombo->SetDefault($settings['keep_original']);

        // Show EXIF info
        $exifCombo =& Piwi::CreateWidget('Combo', 'show_exif_info');
        $exifCombo->setContainerClass('oneline');
        $exifCombo->SetTitle($this::t('SHOW_EXIF_INFO'));
        $exifCombo->AddOption(Jaws::t('ENABLED'), 'true');
        $exifCombo->AddOption(Jaws::t('DISABLED'), 'false');
        $exifCombo->SetDefault($settings['show_exif_info']);

        // Moblog
        $moblogLimitCombo =& Piwi::CreateWidget('Combo', 'moblog_limit', $this::t('MOBLOG_LIMIT'));
        $moblogLimitCombo->setContainerClass('oneline');
        $moblogLimitCombo->AddOption('5', '5');
        $moblogLimitCombo->AddOption('10', '10');
        $moblogLimitCombo->AddOption('15', '15');
        $moblogLimitCombo->AddOption('20', '20');
        $moblogLimitCombo->SetDefault($settings['moblog_limit']);

        // Photoblog
        $albums = $aModel->GetAlbums('name', 'ASC');
        $photoblogAlbumCombo =& Piwi::CreateWidget('Combo', 'photoblog_album', $this::t('PHOTOBLOG_ALBUM'));
        $photoblogAlbumCombo->setContainerClass('oneline');
        $photoblogAlbumCombo->AddOption('&nbsp;', '');
        if (!Jaws_Error::IsError($albums)) {
            $date = Jaws_Date::getInstance();
            foreach ($albums as $a) {
                // FIXME: Ugly hack to add title to photoblogAlbumCombo
                $o =& Piwi::CreateWidget('ComboOption', $a['name'], $a['name']);
                $o->SetTitle($this::t('NUM_PHOTOS_ALBUM', $a['howmany']) . ' / '.
                $this::t('ALBUM_CREATION_DATE') . ': ' . $date->Format($a['createtime']));
                $photoblogAlbumCombo->_options[$a['name']] = $o;
            }
        }
        $photoblogAlbumCombo->SetDefault($settings['photoblog_album']);

        $photoblogLimitCombo =& Piwi::CreateWidget('Combo', 'photoblog_limit', $this::t('PHOTOBLOG_LIMIT'));
        $photoblogLimitCombo->setContainerClass('oneline');
        $photoblogLimitCombo->AddOption('5', '5');
        $photoblogLimitCombo->AddOption('10', '10');
        $photoblogLimitCombo->AddOption('15', '15');
        $photoblogLimitCombo->AddOption('20', '20');
        $photoblogLimitCombo->SetDefault($settings['photoblog_limit']);

        // Images per Page
        $thumbnailLimitCombo =& Piwi::CreateWidget('Combo', 'thumbnail_limit', $this::t('THUMBNAIL_LIMIT'));
        $thumbnailLimitCombo->setContainerClass('oneline');
        $thumbnailLimitCombo->AddOption($this::t('FULL_ALBUM'), '0');
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
        $fieldset->Add($moblogLimitCombo);
        $fieldset->Add($photoblogAlbumCombo);
        $fieldset->Add($photoblogLimitCombo);
        $fieldset->SetDirection('vertical');
        $form->Add($fieldset);

        $buttonbox =& Piwi::CreateWidget('HBox');
        $buttonbox->SetStyle(Jaws::t('LANG_DIRECTION')=='rtl'?'float: left;' : 'float: right;');
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

        $post    = $this->gadget->request->fetch(array('default_action', 'published', 'allow_comments', 'moblog_album',
            'moblog_limit', 'photoblog_album',  'photoblog_limit',
            'show_exif_info', 'keep_original', 'thumbnail_limit',
            'comment_status', 'albums_order_type', 'photos_order_type'), 'post');

        $model = $this->gadget->model->loadAdmin('Settings');
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

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=Phoo&reqAction=AdditionalSettings');
    }

}