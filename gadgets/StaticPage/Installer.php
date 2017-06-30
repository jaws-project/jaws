<?php
/**
 * StaticPage Installer
 *
 * @category    GadgetModel
 * @package     StaticPage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('hide_title', 'true'),
        array('group_pages_limit', 10),
        array('default_page', '1'),
        array('multilanguage', 'yes'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'AddPage',
        'EditPage',
        'DeletePage',
        'PublishPages',
        'ManagePublishedPages',
        'ModifyOthersPages',
        'ManageGroups',
        'Properties'
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

        $this->gadget->acl->insert('AccessGroup', 1, true);
        $this->gadget->acl->insert('ManageGroup', 1, true);
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
     * @return  mixed   True on Success or Jaws_Error on failure
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '1.0.0', '<')) {
            // set dynamic ACLs of groups
            $gModel = $this->gadget->model->load('Group');
            $groups = $gModel->GetGroups();
            foreach ($groups as $group) {
                $this->gadget->acl->insert('AccessGroup', $group['id'], true);
                $this->gadget->acl->insert('ManageGroup', $group['id'], true);
            }
        }

        if (version_compare($old, '1.1.0', '<')) {
            $this->gadget->registry->insert('recommended', ',Tags,');
        }

        if (version_compare($old, '1.2.0', '<')) {
            $this->gadget->registry->insert('group_pages_limit', 10);
        }

        if (version_compare($old, '1.3.0', '<')) {
            // do nothing
        }

        return true;
    }

}