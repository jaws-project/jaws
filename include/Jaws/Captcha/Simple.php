<?php
/**
 * Simple captcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha_Simple extends Jaws_Captcha
{
    /**
     * Install captcha driver
     *
     * @access  public
     * @return  mixed   True on success otherwise Jaws_Error on failure
     */
    function install()
    {
        if (is_null($this->app->registry->fetch('captcha_simple_collection', 'Policy'))) {
            $this->app->registry->insert(
                'captcha_simple_collection',
                'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                false,
                'Policy'
            );
        }

        return true;
    }

    /**
     * Displays the captcha image
     *
     * @access  public
     * @param   int     $key    Captcha key
     * @return  mixed   Captcha raw image data
     */
    function image($key)
    {
        $collection = $this->app->registry->fetch('captcha_simple_collection', 'Policy');
        $text = Jaws_Utils::RandomText(6, array('collection' => $collection));
        $result = $this->update($key, $text);
        if (Jaws_Error::IsError($result)) {
            $value = '';
        }
        $fake = Jaws_Utils::RandomText(6, array('collection' => $collection));

        $text = implode('', array_map('Jaws_UTF8::chr', Jaws_Bidi::utf8Bidi($text, 'AL')));
        $fake = implode('', array_map('Jaws_UTF8::chr', Jaws_Bidi::utf8Bidi($fake, 'AL')));

        // create image
        $im = imagecreatetruecolor(120, 28);
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
        imagecolortransparent($im, imagecolorallocate($im, 255, 255, 255));
        $font = dirname(__FILE__) . '/resources/courbd.ttf';

        // shadow
        $shadow = imagecolorallocate($im, 0xbb, 0xbb, 0xbb);
        imagettftext($im, 18, 0, 8, 19, $shadow, $font, $fake);
        // text
        $color = imagecolorallocate($im, 0x55, 0x55, 0x55);
        imagettftext($im, 18, 0, 11, 22, $color, $font, $text);

        ob_start();
        imagepng($im);
        $content = ob_get_contents();
        ob_end_clean();
        imagedestroy($im);

        header('Content-Type: image/png');
        return $content;
    }

}