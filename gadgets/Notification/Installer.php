<?php
/**
 * Notification Installer
 *
 * @category    GadgetModel
 * @package     Notification
 */
class Notification_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('processing', 'false'),
        array('last_update', '0'),
        array('queue_max_time', '1800'), // maximum time to execution an queue (seconds)
        array('email_pop_count', '100'),
        array('mobile_pop_count', '50'),
        array('configuration', ''), // array(gadget_name=>(0,1, driver_name))
    );

    /**
     * Default ACL value of the gadget frontend
     *
     * @var     bool
     * @access  protected
     */
    var $default_acl = true;

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'Settings',
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

        $this->gadget->event->insert('Notify');

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
        $tables = array(
            'notification_email', 'notification_mobile'
        );
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
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        return true;
    }

}