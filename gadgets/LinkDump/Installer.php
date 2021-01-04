<?php
/**
 * LinkDump Installer
 *
 * @category    GadgetModel
 * @package     LinkDump
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('links_target', 'blank'),
        array('max_limit_count', '100'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageLinks',
        'ManageGroups',
        'UpdateProperties',
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
        $new_dir = ROOT_DATA_PATH . 'xml' . DIRECTORY_SEPARATOR;
        if (!$this->app->fileManagement::mkdir($new_dir)) {
            return new Jaws_Error(Jaws::t('ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', array(), 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (!empty($input_schema)) {
            $result = $this->installSchema($input_schema, $input_variables, 'schema.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed  True on Success and Jaws_Error on Failure
     */
    function Uninstall()
    {
        $tables = array('linkdump_links',
                        'linkdump_groups');
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
     * @return  mixed   True on Success and Jaws_Error on Failure
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.9.0', '<')) {
            $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('LinkDump', 'Display', 'Category', 'Groups');
                $layoutModel->EditGadgetLayoutAction('LinkDump', 'ShowCategories', 'Categories', 'Groups');
                $layoutModel->EditGadgetLayoutAction('LinkDump', 'ShowTagCloud', 'ShowTagCloud', 'TagCloud');
            }
        }

        if (version_compare($old, '1.0.0', '<')) {
            $this->gadget->registry->insert('recommended', ',Tags,');
        }

        if (version_compare($old, '1.1.0', '<')) {
            // do nothing
        }

        return true;
    }

}