<?php
/**
 * Policy Gadget Admin
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Model_Admin_AdvancedPolicies extends Jaws_Gadget_Model
{
    /**
     * Update Advanced Policies
     *
     * @access  public
     * @param   string  $passwd_complexity
     * @param   int     $passwd_bad_count
     * @param   int     $passwd_lockedout_time
     * @param   int     $passwd_max_age
     * @param   int     $passwd_min_length
     * @param   string  $login_captcha
     * @param   string  $login_captcha_driver
     * @param   string  $xss_parsing_level
     * @param   int     $session_idle_timeout
     * @param   int     $session_remember_timeout
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateAdvancedPolicies($passwd_complexity, $passwd_bad_count, $passwd_lockedout_time,
                                    $passwd_max_age, $passwd_min_length, $login_captcha, $login_captcha_driver,
                                    $xss_parsing_level, $session_idle_timeout, $session_remember_timeout)
    {
        $this->gadget->registry->update('passwd_complexity',     ($passwd_complexity=='yes')? 'yes' : 'no');
        $this->gadget->registry->update('passwd_bad_count',      (int)$passwd_bad_count);
        $this->gadget->registry->update('passwd_lockedout_time', (int)$passwd_lockedout_time);
        $this->gadget->registry->update('passwd_max_age',        (int)$passwd_max_age);
        $this->gadget->registry->update('passwd_min_length',     (int)$passwd_min_length);
        $this->gadget->registry->update('login_captcha_status',  $login_captcha);
        $this->gadget->registry->update('login_captcha_driver',  $login_captcha_driver);
        $this->gadget->registry->update('xss_parsing_level',     ($xss_parsing_level=='paranoid')? 'paranoid' : 'normal');
        $this->gadget->registry->update('session_idle_timeout',     (int)$session_idle_timeout);
        $this->gadget->registry->update('session_remember_timeout', (int)$session_remember_timeout);

        // install captcha driver
        $objCaptcha = Jaws_Captcha::getInstance($login_captcha_driver);
        $objCaptcha->install();

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_ADVANCED_POLICIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }
}