<?php
/**
 * Math captcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2022 Jaws Development Group
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
     * Install captcha driver
     *
     * @access  public
     * @return  mixed   True on success otherwise Jaws_Error on failure
     */
    function install()
    {
        if (is_null($this->app->registry->fetch('captcha_math_offset', 'Policy'))) {
            $this->app->registry->insert('captcha_math_offset', 48, false, 'Policy');
        }

        return true;
    }

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
        $utf8_offset = $this->app->registry->fetch('captcha_math_offset', 'Policy');
        $operations = array(
            0 => '+',
            1 => '-',
            2 => '*'
        );

        switch ($oprt) {
            case 0:
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
                $result = $fnum - $snum;
                break;

            case 2:
                $result = $fnum * $snum;
        }

        $equation = Jaws_UTF8::chr($utf8_offset + $fnum). $operations[$oprt]. Jaws_UTF8::chr($utf8_offset + $snum);
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
        $value .= ' = ?';

        $bg = dirname(__FILE__) . '/resources/math.bg.png';
        $im = imagecreatefrompng($bg);
        imagecolortransparent($im, imagecolorallocate($im, 255, 255, 255));
        $font = dirname(__FILE__) . '/resources/courbd.ttf';
        $grey = imagecolorallocate($im, 0x7f, 0x7f, 0x7f);
        // shadow
        imagettftext($im, 18, 0, 8, 22, $grey, $font, $value);
        // text
        imagettftext($im, 18, 0, 11, 25, $grey, $font, $value);

        ob_start();
        imagepng($im);
        $content = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);

        header('Content-Type: image/png');
        return $content;
    }

}