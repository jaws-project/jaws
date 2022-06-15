<?php
/**
 * Logs Installer
 *
 * @category    GadgetModel
 * @package     Logs
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2008-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Logs_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('syslog', false),
        array(
            'syslog_format',
            '{time}|{status}|{apptype}|{priority}|{ip}|{domain}|{username}|{gadget}|{action}'
        ),
        array('log_priority_level', '6'),
        array('log_parameters', 'false'),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'DeleteLogs',
        'ExportLogs',
        'ManageSettings',
    );

    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Install listener for logging
        $this->gadget->event->insert('Log');
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
        $tables = array('logs');
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
        if (version_compare($old, '1.1.0', '<')) {
            Jaws_DB::getInstance()->truncateTable('logs');
            $result = $this->installSchema('1.2.0.xml', array(), '1.0.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.2.0', '<')) {
            // registry keys
            $this->gadget->registry->insert('syslog', false);
            $this->gadget->registry->insert(
                'syslog_format',
                '{time}|{status}|{apptype}|{priority}|{ip}|{domain}|{username}|{gadget}|{action}'
            );
        }

        if (version_compare($old, '1.3.0', '<')) {
            Jaws_DB::getInstance()->truncateTable('logs');
            $result = $this->installSchema('1.3.0.xml', array(), '1.2.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '1.4.0', '<')) {
            Jaws_DB::getInstance()->truncateTable('logs');
            $result = $this->installSchema('schema.xml', array(), '1.3.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
            // registry keys
            $this->gadget->registry->update(
                'syslog_format',
                '{time}|{status}|{apptype}|{priority}|{ip}|{domain}|{username}|{gadget}|{action}'
            );
        }

        return true;
    }

}