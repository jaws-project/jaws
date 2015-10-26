<?php
/**
 * Faq Installer
 *
 * @category    GadgetModel
 * @package     Faq
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'AddQuestion',
        'EditQuestion',
        'DeleteQuestion',
        'ManageCategories',
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

        $variables = array();
        $variables['timestamp'] = Jaws_DB::getInstance()->date();

        $result = $this->installSchema('insert.xml', $variables, 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

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
        $tables = array('faq',
                        'faq_category');
        foreach ($tables as $table) {
            $result = Jaws_DB::getInstance()->dropTable($table);
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
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Upgrade($old, $new)
    {
        // Update layout actions
        $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
        if (!Jaws_Error::isError($layoutModel)) {
            $layoutModel->EditGadgetLayoutAction('Faq', 'ListCategories', 'ListCategories', 'Category');
        }

        return true;
    }

}