<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetAdmin
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Actions_Admin_Albums extends Phoo_Actions_Admin_Default
{

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

        $action      = jaws()->request->fetch('action', 'get');
        $description = jaws()->request->fetch('description', 'post', false);

        $tpl = $this->gadget->template->loadAdmin('EditAlbum.html');
        $tpl->SetBlock('edit_album');
        $tpl->SetVariable('base_script', BASE_SCRIPT);
        $tpl->SetVariable('menubar', $this->MenuBar(isset($action) ? $action : ''));

        $tpl->SetVariable('action', 'SaveNewAlbum');

        $name =& Piwi::CreateWidget('Entry', 'name');
        $name->SetStyle('width: 100%;');
        $tpl->SetVariable('name', _t('PHOO_ALBUM_NAME'));
        $tpl->SetVariable('name_field', $name->get());

        // Allow Comments
        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        if ($this->gadget->registry->fetch('allow_comments') === 'true') {
            $selected = true;
        } else {
            $selected = false;
        }
        $comments->AddOption(_t('PHOO_ALLOW_COMMENTS'), '1', null, $selected);
        $tpl->SetVariable('allow_comments_field', $comments->get());

        // Status
        $statCombo =& Piwi::CreateWidget('Combo', 'published');
        $statCombo->setId('published');
        $statCombo->AddOption(_t('PHOO_HIDDEN'), '0');
        $statCombo->AddOption(_t('PHOO_PUBLISHED'), '1');
        if ($this->gadget->registry->fetch('published') === 'true') {
            $published = true;
        } else {
            $published = false;
        }
        $statCombo->SetDefault($published);
        $tpl->SetVariable('status', _t('PHOO_STATUS'));
        $tpl->SetVariable('status_field', $statCombo->get());

        $desc = isset($description) ? $description : '';
        $editor =& $GLOBALS['app']->LoadEditor('Phoo', 'description', $desc, false);
        $editor->setLabel(_t('PHOO_ALBUM_DESC'));
        $editor->_Container->setStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'text-align: right;' : 'text-align: left;');
        $editor->TextArea->setStyle('width: 100%;');
        $editor->SetWidth('100%');
        // FIXME: Ugly hack to set rows in editor
        $editor->TextArea->SetRows(5);
        $tpl->SetVariable('description', $editor->get());

        // Groups
        $tpl->SetVariable('lbl_group', _t('GLOBAL_GROUPS'));
        $gModel = $this->gadget->model->load('Groups');
        $groups = $gModel->GetGroups();
        foreach ($groups as $group) {
            $tpl->SetBlock('edit_album/group');
            $tpl->SetVariable('gid', $group['id']);
            $tpl->SetVariable('lbl_group', $group['name']);
            $tpl->ParseBlock('edit_album/group');
        }

        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, 'history.go(-1)');
        $tpl->SetVariable('cancel', $cancel->Get());
        $save =& Piwi::CreateWidget('Button', 'save', _t('PHOO_SAVE_CHANGES'), STOCK_SAVE);
        $save->SetSubmit(true);
        $tpl->SetVariable('save', $save->Get());

        $tpl->ParseBlock('edit_album');
        return $tpl->Get();
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
        $post        = jaws()->request->fetch(array('name', 'allow_comments:array', 'published'), 'post');
        $description = jaws()->request->fetch('description', 'post', false);

        $model = $this->gadget->model->loadAdmin('Albums');
        $album = $model->NewAlbum($post['name'], $description, isset($post['allow_comments'][0]), $post['published']);
        if (!Jaws_Error::IsError($album)) {
            $agModel = $this->gadget->model->loadAdmin('AlbumGroup');
            $groups = jaws()->request->fetch('groups:array');
            if (!empty($groups)) {
                foreach ($groups as $group) {
                    $insertData = array();
                    $insertData['album'] = $album;
                    $insertData['group'] = $group;
                    $agModel->AddAlbumGroup($insertData);
                }
            }
        }
        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo&album='.$album);
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
        $model = $this->gadget->model->load('Albums');

        $get = jaws()->request->fetch(array('action', 'album'), 'get');
        $id  = (int)$get['album'];
        $album = $model->GetAlbumInfo($id);
        if (Jaws_Error::IsError($album) || empty($album)) {
            ///FIXME the error msg never has a chance to show
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo');
        }

        $tpl = $this->gadget->template->loadAdmin('EditAlbum.html');
        $tpl->SetBlock('edit_album');
        $tpl->SetVariable('base_script', BASE_SCRIPT . '?gadget=Phoo');
        $tpl->SetVariable('menubar', $this->MenuBar($get['action']));

        $tpl->SetVariable('action', 'SaveEditAlbum');
        $albumid =& Piwi::CreateWidget('HiddenEntry', 'album', $album['id']);
        $tpl->SetVariable('album', $albumid->Get());

        $name =& Piwi::CreateWidget('Entry', 'name', $album['name']);
        $name->SetStyle('width: 100%;');
        $tpl->SetVariable('name', _t('PHOO_ALBUM_NAME'));
        $tpl->SetVariable('name_field', $name->get());

        // Allow Comments
        $comments =& Piwi::CreateWidget('CheckButtons', 'allow_comments');
        if ($album['allow_comments'] === true) {
            $selected = true;
        } else {
            $selected = false;
        }
        $comments->AddOption(_t('PHOO_ALLOW_COMMENTS'), '1', null, $selected);
        $tpl->SetVariable('allow_comments_field', $comments->get());

        // Status
        $tpl->SetVariable('status', _t('PHOO_STATUS'));
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
        $tpl->SetVariable('status', _t('PHOO_STATUS'));
        $tpl->SetVariable('status_field', $statCombo->get());

        $editor =& $GLOBALS['app']->LoadEditor('Phoo', 'description', $album['description'], false);
        $editor->setLabel(_t('PHOO_ALBUM_DESC'));
        $editor->_Container->setStyle(_t('GLOBAL_LANG_DIRECTION')=='rtl'?'text-align: right;' : 'text-align: left;');
        $editor->TextArea->setStyle('width: 100%;');
        $editor->SetWidth('100%');
        // FIXME: Ugly hack to set rows in editor
        $editor->TextArea->SetRows(5);
        $tpl->SetVariable('description', $editor->get());

        // Groups
        $tpl->SetVariable('lbl_group', _t('GLOBAL_GROUPS'));
        $gModel = $this->gadget->model->load('Groups');
        $agModel = $this->gadget->model->load('AlbumGroup');
        $currentGroups = $agModel->GetAlbumGroupsID($id);
        $groups = $gModel->GetGroups();
        foreach ($groups as $group) {
            $tpl->SetBlock('edit_album/group');
            $tpl->SetVariable('gid', $group['id']);
            $tpl->SetVariable('lbl_group', $group['name']);
            if (in_array($group['id'], $currentGroups)) {
                $tpl->SetBlock('edit_album/group/selected_group');
                $tpl->ParseBlock('edit_album/group/selected_group');
            }
            $tpl->ParseBlock('edit_album/group');
        }

        $cancel =& Piwi::CreateWidget('Button', 'cancel', _t('GLOBAL_CANCEL'), STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "gotoLocation({$get['album']})");
        $tpl->SetVariable('cancel', $cancel->Get());
        $save =& Piwi::CreateWidget('Button', 'save', _t('PHOO_SAVE_CHANGES'), STOCK_SAVE);
        $save->SetSubmit(true);
        $tpl->SetVariable('save', $save->Get());

        $tpl->ParseBlock('edit_album');

        return $tpl->Get();
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

        $post        = jaws()->request->fetch(array('name', 'album', 'allow_comments:array', 'published'), 'post');
        $description = jaws()->request->fetch('description', 'post', false);

        $id = (int)$post['album'];
        $model = $this->gadget->model->loadAdmin('Albums');
        $result = $model->UpdateAlbum($id, $post['name'], $description, isset($post['allow_comments'][0]), $post['published']);

        // AlbumGroup
        if (!Jaws_Error::IsError($result)) {
            $agModel = $this->gadget->model->loadAdmin('AlbumGroup');
            $agModel->DeleteAlbum($id);
            $groups = jaws()->request->fetch('groups:array');
            foreach ($groups as $group) {
                $insertData = array();
                $insertData['album'] = $id;
                $insertData['group'] = $group;
                $agModel->AddAlbumGroup($insertData);
            }
        }

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

        $album = (int)jaws()->request->fetch('album', 'get');
        $model = $this->gadget->model->loadAdmin('Albums');
        $foo = $model->DeleteAlbum($album);

        // AlbumGroup
        if (!Jaws_Error::IsError($foo)) {
            $agModel = $this->gadget->model->loadAdmin('AlbumGroup');
            $agModel->DeleteAlbum($album);
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Phoo');
    }

}