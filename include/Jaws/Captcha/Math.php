<?php
/**
 * Math captcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha_Simple extends Jaws_Captcha
{
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
                $equation = $fnum. '+'. $snum;
                $result = $fnum + $snum;
                break;

            case 1:
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
        // Write it in a random position..
        $text_length = strlen($value);
        $darkgray = imagecolorallocate($im, 0x10, 0x70, 0x70);
        $x = 5;
        $y = 20;
        for ($i = 0; $i < $text_length; $i++) {
            $fnt = rand(7,10);
            $y = rand(6, 10);
            imagestring($im, $fnt, $x, $y, $value{$i} , $darkgray);
            $x = $x + rand(15, 25);
        }

        header("Content-Type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

}