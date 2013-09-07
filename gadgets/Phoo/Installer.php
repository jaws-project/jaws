<?php
/**
 * Phoo Installer
 *
 * @category    GadgetModel
 * @package     Phoo
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'AddPhotos',
        'DeletePhotos',
        'ManagePhotos',
        'ModifyOthersPhotos',
        'ManageComments',
        'ManageAlbums',
        'Settings',
        'Import',
    );

    /**
     * Install the gadget
     *
     * @access  public
     * @param   string  $input_schema       Schema file path
     * @param   array   $input_variables    Schema variables
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Install($input_schema = '', $input_variables = array())
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'phoo' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('PHOO_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (!empty($input_schema)) {
            $result = $this->installSchema($input_schema, $input_variables, 'schema.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        // Install listener for update comment
        $GLOBALS['app']->Listener->AddListener($this->gadget->name, 'UpdateComment');

        // Registry keys
        $this->gadget->registry->insert('default_action',    'AlbumList');
        $this->gadget->registry->insert('thumbsize',         '133x100');
        $this->gadget->registry->insert('mediumsize',        '400x300');
        $this->gadget->registry->insert('moblog_album',      '');
        $this->gadget->registry->insert('moblog_limit',      '10');
        $this->gadget->registry->insert('photoblog_album',   '');
        $this->gadget->registry->insert('photoblog_limit',   '5');
        $this->gadget->registry->insert('allow_comments',    'true');
        $this->gadget->registry->insert('published',         'true');
        $this->gadget->registry->insert('plugabble',         'true');
        $this->gadget->registry->insert('show_exif_info',    'false');
        $this->gadget->registry->insert('keep_original',     'true');
        $this->gadget->registry->insert('thumbnail_limit',   '0');
        $this->gadget->registry->insert('use_antispam',      'true');
        $this->gadget->registry->insert('comment_status',    'approved');
        $this->gadget->registry->insert('albums_order_type', 'name');
        $this->gadget->registry->insert('photos_order_type', 'id');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Uninstall()
    {
        $tables = array('phoo_album',
                        'phoo_image',
                        'phoo_image_album');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('PHOO_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error onFailure
     */
    function Upgrade($old, $new)
    {
        // Update layout actions
        $layoutModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel', 'Layout');
        if (!Jaws_Error::isError($layoutModel)) {
            $layoutModel->EditGadgetLayoutAction('Phoo', 'AlbumList', 'AlbumList', 'Albums');
            $layoutModel->EditGadgetLayoutAction('Phoo', 'Random', 'Random', 'Random');
            $layoutModel->EditGadgetLayoutAction('Phoo', 'Moblog', 'Moblog', 'Moblog');
        }

        return true;
    }

}