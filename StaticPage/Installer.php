<?php
/**
 * StaticPage Installer
 *
 * @category    GadgetModel
 * @package     StaticPage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
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
        $this->gadget->AddRegistry('hide_title', 'true');
        $this->gadget->AddRegistry('default_page', '1');
        $this->gadget->AddRegistry('multilanguage', 'yes');

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
        $this->gadget->DelRegistry('hide_title');
        $this->gadget->DelRegistry('default_page');
        $this->gadget->DelRegistry('multilanguage');

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
        if (version_compare($old, '0.8.1', '<')) {
            $result = $this->installSchema('0.8.1.xml', '', "$old.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.8.0', '<')) {
            $sql = '
                SELECT [page_id], [title], [fast_url], [published], [show_title], [content]
                FROM [[static_page]]';
            $pages = $GLOBALS['db']->queryAll($sql);
            if (Jaws_Error::IsError($pages)) {
                return $pages;
            }

            $site_language = $this->gadget->GetRegistry('site_language', 'Settings');
            foreach ($pages as $page) {
                $result = $this->AddPage($page['title'], 0, $page['fast_url'], $page['show_title'],
                                         $page['content'], $site_language, $page['published']);
                if (Jaws_Error::IsError($result)) {
                    return $result;
                }
            }

            $result = $GLOBALS['db']->dropTable('static_page');
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/StaticPage/Properties', 'true');

            // Registry keys
            $this->gadget->AddRegistry('multilanguage', 'yes');

            $GLOBALS['app']->Session->PopLastResponse(); // emptying all responses message
        }

        if (version_compare($old, '0.8.3', '<')) {
            $result = $this->installSchema('0.8.3.xml', '', "0.8.1.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $result = $this->InsertGroup('General', 'general', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/StaticPage/ManageGroups',  'false');

            $layoutModel = $GLOBALS['app']->loadGadget('Layout', 'AdminModel');
            if (!Jaws_Error::isError($layoutModel)) {
                $layoutModel->EditGadgetLayoutAction('StaticPage', 'Display', 'PagesList');
            }
        }

        if (version_compare($old, '0.8.4', '<')) {
            $result = $this->installSchema('schema.xml', '', "0.8.3.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/StaticPage/PublishPages',         'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/StaticPage/ManagePublishedPages', 'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/StaticPage/ModifyOthersPages',    'false');
        }

        return true;
    }

}