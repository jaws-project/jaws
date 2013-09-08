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
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        'default_action'    => 'AlbumList',
        'thumbsize'         => '133x100',
        'mediumsize'        => '400x300',
        'moblog_album'      => '',
        'moblog_limit'      => '10',
        'photoblog_album'   => '',
        'photoblog_limit'   => '5',
        'allow_comments'    => 'true',
        'published'         => 'true',
        'plugabble'         => 'true',
        'show_exif_info'    => 'false',
        'keep_original'     => 'true',
        'thumbnail_limit'   => '0',
        'use_antispam'      => 'true',
        'comment_status'    => 'approved',
        'albums_order_type' => 'name',
        'photos_order_type' => 'id',
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
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