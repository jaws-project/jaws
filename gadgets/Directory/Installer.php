<?php
/**
 * Directory Installer
 *
 * @category    GadgetModel
 * @package     Directory
 */
class Directory_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('items_per_page', '12'),
        array('files_limit', '20'),
        array('order_type', '1'),
        array('thumbnail_size', '128x128'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageComments',
        array('UploadFiles', '', false),
        'PublishFiles',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $new_dir = ROOT_DATA_PATH . 'directory';
        if (!$this->app->fileManagement::mkdir($new_dir)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error otherwise
     */
    function Uninstall()
    {
        $tables = array('directory');
        foreach ($tables as $table) {
            $result = Jaws_DB::getInstance()->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $errMsg = Jaws::t('ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
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
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '1.1.0', '<')) {
            $result = $this->installSchema('1.1.0.xml', array(), '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.2.0', '<')) {
            $result = $this->installSchema('1.2.0.xml', array(), '1.1.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.3.0', '<')) {
            $result = $this->installSchema('1.3.0.xml', array(), '1.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.4.0', '<')) {
            $result = $this->installSchema('1.4.0.xml', array(), '1.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.5.0', '<')) {
            $result = $this->installSchema('schema.xml', array(), '1.4.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.6.0', '<')) {
            // ACL keys
            $this->gadget->acl->insert('UploadFiles', '', false);
        }

        if (version_compare($old, '1.7.0', '<')) {
            // nothing
        }

        if (version_compare($old, '1.8.0', '<')) {
            $objORM = Jaws_ORM::getInstance()->beginTransaction();
            $objORM->table('directory');
            // change unknown type to 99
            $result = $objORM->update(array('file_type' => 99))->where('file_type', 1)->exec();
            if (Jaws_Error::IsError($result)) {
                return Jaws_Error::raiseError(
                    $result->getMessage(),
                    __FUNCTION__
                );
            }
            // set file type 1 for folders
            $result = $objORM->update(array('file_type' => 1))->where('is_dir', true)->exec();
            if (Jaws_Error::IsError($result)) {
                return Jaws_Error::raiseError(
                    $result->getMessage(),
                    __FUNCTION__
                );
            }

            //commit transaction
            $objORM->commit();
        }

        return true;
    }

}