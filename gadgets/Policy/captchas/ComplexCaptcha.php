<?php
/**
 * ComplexCaptcha
 *
 * @category    Captcha
 * @package     Policy
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class ComplexCaptcha
{
    /**
     * Constructor
     *
     * @access  public
     */
    function ComplexCaptcha()
    {
        // If not installed try to install it
        if ($GLOBALS['app']->Registry->Get('complex_captcha', 'Policy') != 'installed') {
            $GLOBALS['app']->Registry->NewKey('complex_captcha', 'installed', 'Policy');
        }
    }

    /**
     * Returns an array with the captcha image field and a text entry so user can type
     *
     * @access  public
     * @return  array    Array indexed by captcha (the image entry) and entry (the input)
     */
    function Get($field)
    {
        $key = $this->GetKey();
        $imgSrc = $GLOBALS['app']->Map->GetURLFor(
            'Policy',
            'Captcha',
            array('field' => $field, 'key' => $key)
        );

        $res = array();
        $res['key'] =& Piwi::CreateWidget('HiddenEntry', 'captcha_key', $key);
        $res['key']->SetID("captcha_key_$key");
        $res['label'] = _t('GLOBAL_CAPTCHA_CODE');
        $res['captcha'] =& Piwi::CreateWidget('Image', '', '');
        $res['captcha']->SetTitle(_t('GLOBAL_CAPTCHA_CODE'));
        $res['captcha']->SetID("captcha_image_$key");
        $res['captcha']->SetClass('captcha');
        $res['captcha']->SetSrc($imgSrc);
        $res['entry'] =& Piwi::CreateWidget('Entry', 'captcha_value', '');
        $res['entry']->SetID("captcha_value_$key");
        $res['entry']->SetStyle('direction: ltr;');
        $res['entry']->SetTitle(_t('GLOBAL_CAPTCHA_CASE_INSENSITIVE'));
        $res['description'] = _t('GLOBAL_CAPTCHA_CODE_DESC');
        return $res;
    }

    /**
     * Check if a captcha key is valid
     *
     * @access  public
     * @param   bool     Valid/Not Valid
     */
    function Check()
    {
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('captcha_key', 'captcha_value'), 'post');
        list($key, $value) = array_values($post);

        $captcha_value = $this->GetValue($key);
        $result = ($captcha_value !== false) && (strtolower($captcha_value) === strtolower($value));

        $this->Delete($key);
        return $result;
    }

    /**
     * Remove a captcha key once it has been used (with success or failure)
     *
     * @access  public
     * @param   string  $key  Captcha key
     */
    function Delete($key = 0)
    {
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        $tblCaptcha->delete()->where('id', $key)->or()->where('updatetime', time() - 600, '<');
        $result = $tblCaptcha->exec();
    }

    /**
     * Get the real value of a captcha by a given key
     *
     * @access  public
     * @param   string  $key    Captcha key
     * @return  string  Captcha value
     */
    function GetValue($key)
    {
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        $result = $tblCaptcha->select('result')->where('id', $key)->getOne();
        if (Jaws_Error::IsError($result) || empty($result)) {
            $result = '';
        }

        return $result;
    }

    /**
     * Get the key of the current captcha (it creates the captcha and then returns its key)
     *
     * @access  public
     * @return  string  Captcha's key
     */
    function GetKey()
    {
        $value = $this->GenerateRandomValue();
        $tblCaptcha = Jaws_ORM::getInstance()->table('captcha');
        $tblCaptcha->insert(array('result' => $value, 'updatetime' => time()));
        $key = $tblCaptcha->exec();
        if (Jaws_Error::IsError($key)) {
            $key = '';
        }

        return $key;
    }

    /**
     * Create the random string that user will see in the browser
     *
     * @access  private
     * @return  string    random string
     */
    function GenerateRandomValue($lenght = 5, $use_lower_case = true, $use_upper_case = true, $use_numbers = false)
    {
        $string = "";
        $lower_case = "abcdefghijklmnopqrstuvwxyz";
        $upper_case = "ABCDEFGHIJKLMNPQRSTUVWXYZ";
        $numbers = "01234567890";
        $possible = "";
        if ($use_lower_case) {
            $possible .= $lower_case;
        }
        if ($use_upper_case) {
            $possible .= $upper_case;
        }
        if ($use_numbers) {
            $possible .= $numbers;
        }

        for ($i = 1; $i <= $lenght; $i++) {
            $string .= substr($possible, rand(0, strlen($possible)-1), 1);
        }
        return $string;
    }

    /**
     * Displays the captcha image
     *
     * @access  public
     */
    function Image($key)
    {
        //--------------------------------------------------------------------------
        $contrast      = 100; // A value between 0 and 100
        $contrast      = 1.3 * (255 * ($contrast / 100.0));
        $num_polygons  = 3;  // Number of triangles to draw
        $num_ellipses  = 3;  // Number of ellipses to draw
        $num_lines     = 3;  // Number of lines to draw
        $num_dots      = 0;  // Number of dots to draw
        $min_thickness = 2;  // Minimum thickness in pixels of lines
        $max_thickness = 8;  // Maximum thickness in pixles of lines
        $min_radius    = 10; // Minimum radius in pixels of ellipses
        $max_radius    = 30; // Maximum radius in pixels of ellipses
        $object_alpha  = 95; // A value between 0 and 127
        //--------------------------------------------------------------------------
        $value = $this->GetValue($key);
        $text_length = strlen($value);

        $width = 15 * imagefontwidth (5);
        $height = 2.5 * imagefontheight (5);
        $im = imagecreatetruecolor ($width, $height);
        imagealphablending($im, true);
        $black = imagecolorallocatealpha($im, 0, 0, 0, 0);

        $rotated = imagecreatetruecolor(70, 70);
        $x = 0;
        for ($i = 0; $i < $text_length; $i++) {
            $buffer = imagecreatetruecolor(20, 20);
            $buffer2 = imagecreatetruecolor(40, 40);
            // Get a random color
            $red = mt_rand(0,255);
            $green = mt_rand(0,255);
            $blue = 255 - sqrt($red * $red + $green * $green);
            $color = imagecolorallocate($buffer, $red, $green, $blue);
            // Create character
            imagestring($buffer, 5, 0, 0, $value[$i], $color);
            // Resize character
            imagecopyresized($buffer2, $buffer, 0, 0, 0, 0, 25 + mt_rand(0,12), 25 + mt_rand(0,12), 20, 20);
            // Rotate characters a little
            $rotated = imagerotate($buffer2, mt_rand(-25, 25),imagecolorallocatealpha($buffer2,0,0,0,0));
            imagecolortransparent($rotated, imagecolorallocatealpha($rotated,0,0,0,0));
            // Move characters around a little
            $y = mt_rand(1, 3);
            $x += mt_rand(2, 6);
            imagecopymerge($im, $rotated, $x, $y, 0, 0, 40, 40, 100);
            $x += 22;
            imagedestroy($buffer);
            imagedestroy($buffer2);
        }

        // Draw polygons
        if ($num_polygons > 0) for ($i = 0; $i < $num_polygons; $i++) {
            $vertices = array (
                mt_rand(-0.25*$width,$width*1.25),mt_rand(-0.25*$width,$width*1.25),
                mt_rand(-0.25*$width,$width*1.25),mt_rand(-0.25*$width,$width*1.25),
                mt_rand(-0.25*$width,$width*1.25),mt_rand(-0.25*$width,$width*1.25));
            $color = imagecolorallocatealpha($im, mt_rand(0,$contrast), mt_rand(0,$contrast), mt_rand(0,$contrast), $object_alpha);
            imagefilledpolygon($im, $vertices, 3, $color);
        }

        // Draw random circles
        if ($num_ellipses > 0) for ($i = 0; $i < $num_ellipses; $i++) {
            $x1 = mt_rand(0,$width);
            $y1 = mt_rand(0,$height);
            $color = imagecolorallocatealpha($im, mt_rand(0,$contrast), mt_rand(0,$contrast), mt_rand(0,$contrast), $object_alpha);
            imagefilledellipse($im, $x1, $y1, mt_rand($min_radius,$max_radius), mt_rand($min_radius,$max_radius), $color);
        }

        // Draw random lines
        if ($num_lines > 0) for ($i = 0; $i < $num_lines; $i++) {
            $x1 = mt_rand(-$width*0.25,$width*1.25);
            $y1 = mt_rand(-$height*0.25,$height*1.25);
            $x2 = mt_rand(-$width*0.25,$width*1.25);
            $y2 = mt_rand(-$height*0.25,$height*1.25);
            $color = imagecolorallocatealpha($im, mt_rand(0,$contrast), mt_rand(0,$contrast), mt_rand(0,$contrast), $object_alpha);
            imagesetthickness($im, mt_rand($min_thickness,$max_thickness));
            imageline($im, $x1, $y1, $x2, $y2 , $color);
        }

        // Draw random dots
        if ($num_dots > 0) for ($i = 0; $i < $num_dots; $i++) {
            $x1 = mt_rand(0,$width);
            $y1 = mt_rand(0,$height);
            $color = imagecolorallocatealpha($im, mt_rand(0,$contrast), mt_rand(0,$contrast), mt_rand(0,$contrast),$object_alpha);
            imagesetpixel($im, $x1, $y1, $color);
        }

        header("Content-Type: image/png");
        imagepng($im);
        imagedestroy($im);
    }

}