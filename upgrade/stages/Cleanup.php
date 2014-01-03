<?php
/**
 * Cleanup files & directories Stage
 *
 * @category    Application
 * @package     UpgradeStage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_Cleanup extends JawsUpgraderStage
{
    /**
     * Builds the upgrader page.
     *
     * @access  public
     * @return  string      A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        // Create application
        include_once JAWS_PATH . 'include/Jaws.php';
        $GLOBALS['app'] = jaws();
        $GLOBALS['app']->loadPreferences(array('language' => $_SESSION['upgrade']['language']), false);

        $tpl = new Jaws_Template(false);
        $tpl->Load('display.html', 'stages/Cleanup/templates');

        _log(JAWS_LOG_DEBUG,"Preparing cleanup stage");
        $tpl->SetBlock('cleanup');
        $tpl->setVariable('lbl_info', _t('UPGRADE_CLEANUP_INFO'));

        $cleanup_required = false;
        $cleanup_items = @file_get_contents(JAWS_PATH. 'upgrade/stages/Cleanup/folders.txt');
        $cleanup_items = array_filter(explode("\n", $cleanup_items));
        foreach ($cleanup_items as $item) {
            if (file_exists(JAWS_PATH. $item)) {
                $cleanup_required = true;
                $tpl->SetBlock('cleanup/item');
                $tpl->setVariable('type', '1');
                $tpl->setVariable('item_path', $item);
                $tpl->ParseBlock('cleanup/item');
            }
        }

        $cleanup_items = @file_get_contents(JAWS_PATH. 'upgrade/stages/Cleanup/files.txt');
        $cleanup_items = array_filter(explode("\n", $cleanup_items));
        foreach ($cleanup_items as $item) {
            if (file_exists(JAWS_PATH. $item)) {
                $cleanup_required = true;
                $tpl->SetBlock('cleanup/item');
                $tpl->setVariable('type', '0');
                $tpl->setVariable('item_path', $item);
                $tpl->ParseBlock('cleanup/item');
            }
        }

        if (!$cleanup_required) {
            $tpl->SetBlock('cleanup/not_required');
            $tpl->setVariable('message', _t('UPGRADE_CLEANUP_NOT_REQUIRED'));
            $tpl->ParseBlock('cleanup/not_required');
        }

        $tpl->SetVariable('next', _t('GLOBAL_NEXT'));
        $tpl->ParseBlock('cleanup');
        return $tpl->Get();
    }

    /**
     * Does any actions required to finish the stage.
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Run()
    {
        $cleanup_error = false;
        $cleanup_items = @file_get_contents(JAWS_PATH. 'upgrade/stages/Cleanup/folders.txt');
        $cleanup_items = array_filter(explode("\n", $cleanup_items));
        foreach ($cleanup_items as $item) {
            if (file_exists(JAWS_PATH. $item)) {
                if (!Jaws_Utils::Delete(JAWS_PATH. $item)) {
                    $cleanup_error = true;
                }
            }
        }

        $cleanup_items = @file_get_contents(JAWS_PATH. 'upgrade/stages/Cleanup/files.txt');
        $cleanup_items = array_filter(explode("\n", $cleanup_items));
        foreach ($cleanup_items as $item) {
            if (file_exists(JAWS_PATH. $item)) {
                if (!Jaws_Utils::Delete(JAWS_PATH. $item)) {
                    $cleanup_error = true;
                }
            }
        }

        if ($cleanup_error) {
            return Jaws_Error::raiseError(_t('UPGRADE_CLEANUP_ERROR_PERMISSION'), 0, JAWS_ERROR_WARNING);
        }

        return true;
    }

}