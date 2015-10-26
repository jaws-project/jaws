<?php
/**
 * Simple captcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha_Simple extends Jaws_Captcha
{
    /**
     * Displays the captcha image
     *
     * @access  public
     * @param   int     $key    Captcha key
     * @return  mixed   Captcha raw image data
     */
    function image($key)
    {
        $value  = Jaws_Utils::RandomText();
        $result = $this->update($key, $value);
        if (Jaws_Error::IsError($result)) {
            $value = '';
        }

        $bg = dirname(__FILE__) . '/resources/simple.bg.png';
        $im = imagecreatefrompng($bg);
        imagecolortransparent($im, imagecolorallocate($im, 255, 255, 255));
        // Write it in a random position..
        $darkgray = imagecolorallocate($im, 0x10, 0x70, 0x70);
        $x = 5; 
        $y = 20;
        $text_length = strlen($value);
        for ($i = 0; $i < $text_length; $i++) {
            $fnt = rand(7,10);
            $y = rand(6, 10);
            imagestring($im, $fnt, $x, $y, $value{$i} , $darkgray);
            $x = $x + rand(15, 25);
        }

        header("Content-Type: image/png");

        ob_start();
        imagepng($im);
        $content = ob_get_contents();
        ob_end_clean();

        imagedestroy($im);
        return $content;
    }

}