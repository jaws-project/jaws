<?php
/**
 * Jaws Upgrade Stage - From 0.8 to 0.9.0
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_08To0903 extends JawsUpgraderStage
{
    /**
     * Builds the upgader page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $tpl = new Jaws_Template(false);
        $tpl->Load('display.html', 'stages/08To0903/templates');
        $tpl->SetBlock('08To0903');

        $tpl->setVariable('lbl_info',  _t('UPGRADE_VER_INFO', '0.8', '0.9.0'));
        $tpl->setVariable('lbl_notes', _t('UPGRADE_VER_NOTES'));
        $tpl->SetVariable('next',      _t('GLOBAL_NEXT'));

        $tpl->ParseBlock('08To0903');
        return $tpl->Get();
    }

    /**
     * Does any actions required to finish the stage, such as DB queries.
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Run()
    {
        // Connect to database
        require_once JAWS_PATH . 'include/Jaws/DB.php';
        $GLOBALS['db'] = new Jaws_DB($_SESSION['upgrade']['Database']);
        if (Jaws_Error::IsError($GLOBALS['db'])) {
            _log(JAWS_LOG_DEBUG,"There was a problem connecting to the database, please check the details and try again");
            return new Jaws_Error(_t('UPGRADE_DB_RESPONSE_CONNECT_FAILED'), 0, JAWS_ERROR_WARNING);
        }

        // Create application
        include_once JAWS_PATH . 'include/Jaws.php';
        $GLOBALS['app'] = jaws();
        $GLOBALS['app']->Registry->Init();

        // Upgrading core gadgets
        $gadgets = array(
            'UrlMapper', 'Settings', 'ControlPanel', 'Components',
            'Layout', 'Users', 'Policy', 'Comments'
        );
        foreach ($gadgets as $gadget) {
            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                _log(JAWS_LOG_DEBUG,"There was a problem loading core gadget: ".$gadget);
                return $objGadget;
            }

            $installer = $objGadget->installer->load();
            if (Jaws_Error::IsError($installer)) {
                _log(JAWS_LOG_DEBUG,"There was a problem loading installer of core gadget: $gadget");
                return $installer;
            }

            if (Jaws_Gadget::IsGadgetInstalled($gadget)) {
                $result = $installer->UpgradeGadget();
            } else {
                if ($gadget == 'Comments') {
                    $result = $installer->InstallGadget(true);
                } else {
                    $result = $installer->InstallGadget();
                }
            }

            if (Jaws_Error::IsError($result)) {
                _log(JAWS_LOG_DEBUG,"There was a problem installing/upgrading core gadget: $gadget");
                return $result;
            }
        }


        // Upgrading layout actions for renamed gadgets
        _log(JAWS_LOG_DEBUG, 'Upgrading layout actions for renamed gadgets');
        $renamed_gadgets = array(
            'Chatbox'=>'Shoutbox',
            'RssReader'=>'FeedReader',
            'SimpleSite'=>'Sitemap'
        );

        // update layout table for renamed gadgets
        $sql = '
            UPDATE [[layout]] SET
                [gadget] = {new_name}
            WHERE [gadget] = {old_name}';
        $params = array();
        foreach ($renamed_gadgets as $old_name => $new_name) {
            $params['old_name'] = $old_name;
            $params['new_name'] = $new_name;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                _log(JAWS_LOG_ERROR, $res->getMessage());
                return new Jaws_Error($res->getMessage(), 0, JAWS_ERROR_ERROR);
            }
        }

        // update layout "RecentComments" action for Blog/Phoo gadgets
        $sql = '
            UPDATE [[layout]] SET
                [gadget] = {new_name},
                [action_filename] = {filename},
                [action_params] = {params}
            WHERE
                [gadget] = {old_name}
              AND
                [gadget_action] = {action}';

        $params = array();
        $params['new_name'] = 'Comments';
        $params['action']   = 'RecentComments';
        $params['filename'] = 'RecentComments';
        $gadgets = array('Blog', 'Phoo');
        foreach ($gadgets as $gadget) {
            $params['old_name'] = $gadget;
            $params['params']   = serialize(array($gadget, 0, 10));
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                _log(JAWS_LOG_ERROR, $res->getMessage());
                // do nothing
            }
        }

        return true;
    }

}