<?php
/**
 * Text math captcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha_TextMath extends Jaws_Captcha
{
    /**
     * Captcha entry label
     * @var string
     */
    var $_label = 'GLOBAL_CAPTCHA_QUESTION';

    /**
     * Captcha entry description
     * @var string
     */
    var $_description = 'GLOBAL_CAPTCHA_QUESTION_DESC';

    /**
     * Returns an array with the captcha text entry so user can type
     *
     * @access  public
     * @return  array    Array indexed by captcha (the text entry) and entry (the input)
     */
    function get()
    {
        $value  = $this->randomEquation();
        $key    = $this->insert($value[1]);
        $title  = $value[2];
        $value  = $value[0]. '=?';

        $res = array();
        $res['key'] =& Piwi::CreateWidget('HiddenEntry', 'captcha_key', $key);
        $res['key']->SetID("captcha_key_$key");
        $res['captcha'] =& Piwi::CreateWidget('Label', $value);
        $res['captcha']->setTitle($title);
        $res['entry'] =& Piwi::CreateWidget('Entry', 'captcha_value', '');
        $res['entry']->SetID("captcha_value_$key");
        $res['entry']->SetStyle('direction: ltr;');
        $res['entry']->SetTitle(_t('GLOBAL_CAPTCHA_CASE_INSENSITIVE'));
        $res['label'] =& Piwi::CreateWidget('Label', _t($this->_label).':', $res['entry']);
        $res['description'] = _t($this->_description);
        return $res;
    }

    /**
     * Generate a random mathematic equation
     *
     * @access  private
     * @return  string  random mathematic equation
     */
    function randomEquation()
    {
        $fnum = mt_rand(1, 9);
        $snum = mt_rand(1, 9);
        $oprt = mt_rand(0, 2);
        switch ($oprt) {
            case 0:
                $result = $fnum + $snum;
                $equation = $fnum. '+'. $snum;
                $title = _t('POLICY_CAPTCHA_MATH_PLUS', $fnum, $snum);
                break;

            case 1:
                // exchange value of variables
                if ($fnum < $snum) {
                    list($fnum, $snum) = array($snum, $fnum);
                }
                $result = $fnum - $snum;
                $equation = $fnum. '-'. $snum;
                $title = _t('POLICY_CAPTCHA_MATH_MINUS', $fnum, $snum);
                break;

            case 2:
                $result = $fnum * $snum;
                $equation = $fnum. '*'. $snum;
                $title = _t('POLICY_CAPTCHA_MATH_MULTIPLY', $fnum, $snum);
        }

        return array($equation, $result, $title);
    }

}