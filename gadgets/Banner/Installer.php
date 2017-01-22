<?php
/**
 * Banner Installer
 *
 * @category    GadgetModel
 * @package     Banner
 */
class Banner_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageBanners',
        'ManageGroups',
        'BannersGrouping',
        'ViewReports'
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . $this->gadget->DataDirectory;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', array(), 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //registry keys.

        return true;
    }

    /**
     * Uninstall the gadget
     *
     * @access  public
     * @return  mixed    True on a successful install and Jaws_Error otherwise
     */
    function Uninstall()
    {
        $tables = array('banners',
                        'banners_groups');
        foreach ($tables as $table) {
            $result = Jaws_DB::getInstance()->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $this->gadget->title);
                return new Jaws_Error($errMsg);
            }
        }

        //registry keys

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   TRUE on success, or Jaws_Error
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.9.0', '<')) {
            // Update layout actions
            $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('Banner', 'Display', 'Banners', 'Banners');
            }
        }

        if (version_compare($old, '1.0.0', '<')) {
            // Update stored templates in database
            $bannersTable = Jaws_ORM::getInstance()->table('banners');
            $banners = $bannersTable->select('id:integer', 'template')->fetchAll();
            foreach ($banners as $banner) {
                $banner['template'] = str_replace(array('{', '}'), array('{{', '}}'), $banner['template']);
                $bannersTable->update(array('template'=>$banner['template']))->where('id', $banner['id'])->exec();
            }
        }

        return true;
    }

}