<?php
/**
 * Math captcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha_Math extends Jaws_Captcha
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
     * Generate a random mathematics equation
     *
     * @access  private
     * @return  string  random mathematics equation
     */
    function randomEquation()
    {
        $fnum = mt_rand(1, 9);
        $snum = mt_rand(1, 9);
        $oprt = mt_rand(0, 2);
        switch ($oprt) {
            case 0:
                $equation = $fnum. '+'. $snum;
                $result = $fnum + $snum;
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
                $equation = $fnum. '-'. $snum;
                $result = $fnum - $snum;
                break;

            case 2:
                $equation = $fnum. '*'. $snum;
                $result = $fnum * $snum;
        }

        return array($equation, $result);
    }

    /**
     * Displays the captcha image
     *
     * @access  public
     */
    function image($key)
    {
        $value  = $this->randomEquation();
        $result = $this->update($key, $value[1]);
        if (Jaws_Error::IsError($result)) {
            $value = '';
        } else {
            $value = $value[0];
        }
        $value .= '=?';

        $bg = dirname(__FILE__) . '/resources/math.bg.png';
        $im = imagecreatefrompng($bg);
        imagecolortransparent($im, imagecolorallocate($im, 255, 255, 255));
        $font = dirname(__FILE__) . '/resources/comicbd.ttf';
        $grey = imagecolorallocate($im, 0x7f, 0x7f, 0x7f);
        // shadow
        imagettftext($im, 18, 0, 8, 22, $grey, $font, $value);
        // text
        imagettftext($im, 18, 0, 12, 24, $grey, $font, $value);

        header('Content-Type: image/png');
        imagepng($im);
        imagedestroy($im);
    }

}