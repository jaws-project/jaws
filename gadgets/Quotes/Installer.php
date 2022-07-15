<?php
/**
 * Quotes Installer
 *
 * @category    GadgetModel
 * @package     Quotes
 */
class Quotes_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('last_entries_limit', '10'),
        array('last_entries_view_mode', '0'),
        array('last_entries_view_type', '0'),
        array('last_entries_show_title', 'true'),
        array('last_entries_view_random', 'false'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'ManageQuotes',
        'ManageCategories',
        'ClassificationRestricted',
        'ClassificationConfidential',
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
        $tables = array('quotes',
                        'quotes_groups');
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
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        // Update layout actions
        $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
        if (!Jaws_Error::isError($layoutModel)) {
            $layoutModel->EditGadgetLayoutAction('Quotes', 'Display', 'Display', 'Quotes');
            $layoutModel->EditGadgetLayoutAction('Quotes', 'RecentQuotes', 'RecentQuotes', 'Quotes');
        }

        return true;
    }

}