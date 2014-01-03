<?php
/**
 * Phoo Installer
 *
 * @category    GadgetModel
 * @package     Phoo
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2014 Jaws Development Group
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
        array('default_action', 'AlbumList'),
        array('thumbsize', '133x100'),
        array('mediumsize', '400x300'),
        array('moblog_limit', '10'),
        array('photoblog_album', ''),
        array('photoblog_limit', '5'),
        array('allow_comments', 'true'),
        array('published', 'true'),
        array('show_exif_info', 'false'),
        array('keep_original', 'true'),
        array('thumbnail_limit', '0'),
        array('use_antispam', 'true'),
        array('comment_status', 'approved'),
        array('albums_order_type', 'name'),
        array('photos_order_type', 'id'),
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
        'ManageGroups',
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
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', null, 'schema.xml', true);
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
        $this->gadget->event->insert('UpdateComment');

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
                        'phoo_image_album',
                        'phoo_group',
                        'phoo_album_group');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
                return new Jaws_Error($errMsg);
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
        if (version_compare($old, '1.0.0', '<')) {
            $result = $this->installSchema('schema.xml', '', '0.9.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // set default group for albums
            $table = Jaws_ORM::getInstance()->table('phoo_album');
            $albums = $table->select('id:integer')->fetchColumn();
            if (Jaws_Error::IsError($albums)) {
                return $albums;
            }

            $table = Jaws_ORM::getInstance()->table('phoo_album_group');
            foreach ($albums as $album) {
                $table->insert(array('album' => $album, 'group' => 1))->exec();
            }

            $this->gadget->registry->delete('plugabble');
            $this->gadget->acl->insert('ManageGroups');
        }

        return true;
    }

}