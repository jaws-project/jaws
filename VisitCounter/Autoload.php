<?php
/**
 * Visit Counter Gadget - Autoload
 *
 * @category   GadgetAutoload
 * @package    VisitCounter
 * @author     Amir Mohammad Saied <amir@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class VisitCounterAutoload
{
    /**
     * Autoload load method
     *
     */
    function Execute()
    {
        if (!$GLOBALS['app']->IsAgentRobot()) {
            $this->AddVisitor();
        }
    }

    function AddVisitor()
    {
        $model = $GLOBALS['app']->LoadGadget('VisitCounter', 'Model');
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