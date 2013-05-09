<?php
/**
 * StaticPage Installer
 *
 * @category    GadgetModel
 * @package     StaticPage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Installer extends Jaws_Gadget_Installer
{
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
        $variables['timestamp'] = $GLOBALS['db']->Date();

        $result = $this->installSchema('insert.xml', $variables, 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys
        $this->gadget->registry->insert('hide_title', 'true');
        $this->gadget->registry->insert('default_page', '1');
        $this->gadget->registry->insert('multilanguage', 'yes');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Uninstall()
    {
        $tables = array('static_pages_groups',
                        'static_pages_translation',
                        'static_pages');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('STATICPAGE_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // Registry keys
        $this->gadget->registry->delete('hide_title');
        $this->gadget->registry->delete('default_page');
        $this->gadget->registry->delete('multilanguage');

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function Upgrade($old, $new)
    {
        return true;
    }

}