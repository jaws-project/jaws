<?php
/**
 * Finished Stage
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @copyright  2005-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_Finished extends JawsUpgrader
{
    /**
     * Builds the upgrader page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        require_once ROOT_JAWS_PATH . 'include/Jaws.php';
        $jawsApp = Jaws::getInstance();
        $jawsApp->loadPreferences(array('language' => $_SESSION['upgrade']['language']), false);

        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/Finished/templates');
        $tpl->SetBlock('Finished');

        $base_url = $jawsApp->getSiteURL();
        $tpl->setVariable('lbl_info',    $this->t('FINISH_INFO'));
        $tpl->setVariable('lbl_choices', $this->t('FINISH_CHOICES', "$base_url/", "$base_url/admin.php"));
        $tpl->setVariable('lbl_thanks',  $this->t('FINISH_THANKS'));
        $tpl->SetVariable('move_log',    $this->t('FINISH_MOVE_LOG'));

        $tpl->ParseBlock('Finished');
        return $tpl->Get();
    }
}