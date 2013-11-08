<?php
/**
 * Visit Counter Gadget - Autoload
 *
 * @category   GadgetAutoload
 * @package    VisitCounter
 * @author     Amir Mohammad Saied <amir@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounter_Hooks_Autoload extends Jaws_Gadget_Hook
{
    /**
     * Autoload function
     *
     * @access  private
     * @return  void
     */
    function Execute()
    {
        if (!$GLOBALS['app']->IsAgentRobot()) {
            $this->AddVisitor();
        }
    }

    /**
     * Adds a new visitor
     *
     * @access  public
     * @return  void
     */
    function AddVisitor()
    {
        $model = $this->gadget->model->load('Visitors');
        $days = $model->GetCookiePeriod();
        if (!$GLOBALS['app']->Session->GetCookie('VisitCounter')) {
            $res = $model->AddVisitor($_SERVER['REMOTE_ADDR'], true);
            if (!Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->SetCookie('VisitCounter', true, 60 * 24 * $days);
            }
        } else {
            $model->AddVisitor($_SERVER['REMOTE_ADDR'], false);
        }
    }

}