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
class Policy_Model_Admin_AntiSpam extends Jaws_Gadget_Model
{
    /**
     * Update  AntiSpam Settings
     *
     * @access  public
     * @param   bool    $filter
     * @param   string  $default_captcha
     * @param   string  $default_captcha_driver
     * @param   bool    $obfuscator
     * @return  bool    True on success and Jaws error on failure
     */
    function UpdateAntiSpamSettings($filter, $default_captcha, $default_captcha_driver, $obfuscator, $blocked_domains)
    {
        $this->gadget->registry->update('filter',                 $filter);
        $this->gadget->registry->update('default_captcha_status', $default_captcha);
        $this->gadget->registry->update('default_captcha_driver', $default_captcha_driver);
        $this->gadget->registry->update('obfuscator',             $obfuscator);
        $this->gadget->registry->update('blocked_domains',        "\n". trim($blocked_domains));

        // install captcha driver
        $objCaptcha = Jaws_Captcha::getInstance($default_captcha_driver);
        $objCaptcha->install();

        $GLOBALS['app']->Session->PushLastResponse(_t('POLICY_RESPONSE_ANTISPAM_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Get filters
     *
     * @access  public
     * @return  array Array with the filters names.
     */
    function GetFilters()
    {
        $result = array();
        $path = JAWS_PATH . 'gadgets/Policy/filters/';
        $adr = scandir($path);
        foreach ($adr as $file) {
            if (substr($file, -4) == '.php') {
                $result[$file] = substr($file, 0, -4);
            }
        }
        sort($result);
        return $result;
    }


    /**
     * Get captchas
     *
     * @access  public
     * @return  array Array with the captchas names.
     */
    function GetCaptchas()
    {
        $result = array();
        $path = JAWS_PATH. 'include/Jaws/Captcha/';
        $adr = scandir($path);
        foreach ($adr as $file) {
            if (substr($file, -4) == '.php') {
                $result[$file] = substr($file, 0, -4);
            }
        }
        sort($result);
        return $result;
    }


    /**
     * Get filters
     *
     * @access  public
     * @return  array Array with the obfuscators names.
     */
    function GetObfuscators()
    {
        $result = array();
        $path = JAWS_PATH . 'gadgets/Policy/obfuscators/';
        $adr = scandir($path);
        foreach ($adr as $file) {
            if (substr($file, -4) == '.php') {
                $result[$file] = substr($file, 0, -4);
            }
        }
        sort($result);
        return $result;
    }

    /**
     * Submit spam
     *
     * @access  public
     * @param   string  $permalink
     * @param   string  $type
     * @param   string  $author
     * @param   string  $author_email
     * @param   string  $author_url
     * @param   string  $content
     * @return  void
     */
    function SubmitSpam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        $filter = preg_replace('/[^[:alnum:]_\-]/', '', $this->gadget->registry->fetch('filter'));
        if ($filter == 'DISABLED' || !@include_once(JAWS_PATH . "gadgets/Policy/filters/$filter.php"))
        {
            return false;
        }

        static $objFilter;
        if (!isset($objFilter)) {
            $objFilter = new $filter();
        }

        $objFilter->SubmitSpam($permalink, $type, $author, $author_email, $author_url, $content);
    }

    /**
     * Submit ham
     *
     * @access  public
     * @param   string  $permalink
     * @param   string  $type
     * @param   string  $author
     * @param   string  $author_email
     * @param   string  $author_url
     * @param   string  $content
     * @return  void
     */
    function SubmitHam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        $filter = preg_replace('/[^[:alnum:]_\-]/', '', $this->gadget->registry->fetch('filter'));
        if ($filter == 'DISABLED' || !@include_once(JAWS_PATH . "gadgets/Policy/filters/$filter.php"))
        {
            return false;
        }

        static $objFilter;
        if (!isset($objFilter)) {
            $objFilter = new $filter();
        }

        $objFilter->SubmitHam($permalink, $type, $author, $author_email, $author_url, $content);
    }
}