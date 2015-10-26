<?php
/**
 * Policy Gadget Admin
 *
 * @category   GadgetModel
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Model_Admin_AdvancedPolicies extends Jaws_Gadget_Model
{
    /**
     * Update Advanced Policies
     *
     * @access  public
     * @param   string  $password_complexity
     * @param   int     $password_bad_count
     * @param   int     $password_lockedout_time
     * @param   int     $password_max_age
     * @param   int     $password_min_length
     * @param   string  $login_captcha
     * @param   string  $login_captcha_driver
     * @param   string  $xss_parsing_level
     * @param   int     $session_idle_timeout
     * @param   int     $session_remember_timeout
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateAdvancedPolicies($password_complexity, $password_bad_count, $password_lockedout_time,
                                    $password_max_age, $password_min_length, $login_captcha, $login_captcha_driver,
                                    $xss_parsing_level, $session_idle_timeout, $session_remember_timeout)
    {
        $this->gadget->registry->update('password_complexity',     ($password_complexity=='yes')? 'yes' : 'no');
        $this->gadget->registry->update('password_bad_count',      (int)$password_bad_count);
        $this->gadget->registry->update('password_lockedout_time', (int)$password_lockedout_time);
        $this->gadget->registry->update('password_max_age',        (int)$password_max_age);
        $this->gadget->registry->update('password_min_length',     (int)$password_min_length);
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