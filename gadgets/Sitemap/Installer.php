<?php
/**
 * Sitemap Installer
 *
 * @category    GadgetModel
 * @package     Sitemap
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('sitemap_default_priority', '0.5'),
        array('sitemap_default_frequency', Sitemap_Info::SITEMAP_CHANGE_FREQ_WEEKLY),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'PingSite',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Install()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'sitemap' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('SITEMAP_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Uninstall()
    {
        $result = $GLOBALS['db']->dropTable('sitemap');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('SITEMAP_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            return new Jaws_Error($errMsg, $gName);
        }

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Upgrade($old, $new)
    {
        $result = $this->installSchema('schema.xml', '', '0.7.0.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Update layout actions
        $layoutModel = Jaws_Gadget::getInstance('Layout')->model->loadAdmin('Layout');
        if (!Jaws_Error::isError($layoutModel)) {
            $layoutModel->EditGadgetLayoutAction('Sitemap', 'Show', 'Show', 'Show');
            $layoutModel->EditGadgetLayoutAction('Sitemap', 'ShowWithoutTop', 'ShowWithoutTop', 'Show');
            $layoutModel->EditGadgetLayoutAction('Sitemap', 'TopMenu', 'TopMenu', 'Show');
            $layoutModel->EditGadgetLayoutAction('Sitemap', 'ShowTwoLevels', 'ShowTwoLevels', 'Show');
            $layoutModel->EditGadgetLayoutAction('Sitemap', 'ShowThreeLevels', 'ShowThreeLevels', 'Show');
            $layoutModel->EditGadgetLayoutAction('Sitemap', 'DisplayLevel', 'DisplayLevel', 'Show');
            $layoutModel->EditGadgetLayoutAction('Sitemap', 'Breadcrumb', 'Breadcrumb', 'Breadcrumb');
        }

        return true;
    }

}