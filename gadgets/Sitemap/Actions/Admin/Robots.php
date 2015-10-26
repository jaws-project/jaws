<?php
/**
 * Sitemap Admin Gadget
 *
 * @category   GadgetAdmin
 * @package    Sitemap
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2014-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Actions_Admin_Robots extends Sitemap_Actions_Admin_Default
{
    /**
     * Edit website robots.txt
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Robots()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Robots.html');
        $tpl->SetBlock('robots');

        $tpl->SetVariable('menubar', $this->MenuBar('Robots'));

        $currentRobots = $this->gadget->registry->fetch('robots.txt');
        $robots =& Piwi::CreateWidget('TextArea', 'robots', $currentRobots);
        $robots->SetRows(12);
        $robots->SetStyle('width:300px');
        $tpl->SetVariable('lbl_robots', _t('SITEMAP_ROBOTS_TXT_CONTENT'));
        $tpl->SetVariable('robots', $robots->Get());

        $save =& Piwi::CreateWidget('Button',
                                    'save',
                                    _t('GLOBAL_SAVE'),
                                    STOCK_SAVE);
        $save->AddEvent(ON_CLICK, "javascript:updateRobots();");
        $tpl->SetVariable('save', $save->Get());

        $tpl->ParseBlock('robots');
        return $tpl->Get();
    }


    /**
     * Update Robots content
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateRobots()
    {
        $robots = jaws()->request->fetch('robots', 'post');
        $result = $this->gadget->registry->update('robots.txt', $robots);
        if (!$result || Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_ROBOTS_NOT_SAVED'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ROBOTS_SAVED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }
}