<?php
/**
 * Text math captcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha_TextMath extends Jaws_Captcha
{
    /**
     * Captcha entry label
     *
     * @var     string
     * @access  private
     */
    var $_label = 'GLOBAL_CAPTCHA_QUESTION';

    /**
     * Captcha entry description
     *
     * @var     string
     * @access  private
     */
    var $_description = 'GLOBAL_CAPTCHA_QUESTION_DESC';

    /**
     * Returns an array with the captcha text entry
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
        $res['key']   = $key;
        $res['text']  = $value;
        $res['label'] = _t($this->_label);
        $res['title'] = $title;
        $res['description'] = _t($this->_description);
        return $res;
    }

    /**
     * Generate a random mathematics equation
     *
     * @access  private
     * @return  string  random mathematics equation
     */
    private function randomEquation()
    {
        $fnum = mt_rand(1, 9);
        $snum = mt_rand(1, 9);
        $oprt = mt_rand(0, 2);

        // string numbers
        $objPlugin = Jaws_Plugin::getInstance('SpellNumber');
        if (!Jaws_Error::isError($objPlugin)) {
            $fsnum = $objPlugin->ParseText("[number]{$fnum}[/number]");
            $ssnum = $objPlugin->ParseText("[number]{$snum}[/number]");
        } else {
            $fsnum = $fnum;
            $ssnum = $snum;
        }

        switch ($oprt) {
            case 0:
                $result = $fnum + $snum;
                $equation = $fnum. '+'. $snum;
                $title = _t('POLICY_CAPTCHA_MATH_PLUS', $fsnum, $ssnum);
                break;

            case 1:
                // first & second numbers must different
                while ($fnum == $snum) {
                    $snum = mt_rand(1, 9);
                }

                // exchange value of variables
                if ($fnum < $snum) {
                    list($fnum, $snum) = array($snum, $fnum);
                }
                $result = $fnum - $snum;
                $equation = $fnum. '-'. $snum;
                $title = _t('POLICY_CAPTCHA_MATH_MINUS', $fsnum, $ssnum);
                break;

            case 2:
                $result = $fnum * $snum;
                $equation = $fnum. '*'. $snum;
                $title = _t('POLICY_CAPTCHA_MATH_MULTIPLY', $fsnum, $ssnum);
        }

        return array($equation, $result, $title);
    }

}