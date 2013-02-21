<?php
/**
 * ComplexCaptcha
 *
 * @category   Captcha
 * @package    Policy
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
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
        if ($GLOBALS['app']->Registry->Get('complex_captcha', 'Policy', JAWS_COMPONENT_GADGET) != 'installed') {
            $schema = JAWS_PATH . 'gadgets/Policy/captchas/ComplexCaptcha/schema.xml';
            if (!file_exists($schema)) {
                Jaws_Error::Fatal($schema . " doesn't exists", __FILE__, __LINE__);
            }
            $result = $GLOBALS['db']->installSchema($schema);
            if (Jaws_Error::IsError($result)) {
                Jaws_Error::Fatal("Can't install ComplexCaptcha schema", __FILE__, __LINE__);
            }
            $GLOBALS['app']->Registry->NewKey('complex_captcha', 'installed', 'Policy', JAWS_COMPONENT_GADGET);
        }
    }

    /**
     * Returns an array with the captcha image field and a text entry so user can type
     *
     * @access  public
     * @return  array    Array indexed by captcha (the image entry) and entry (the input)
     */
    function Get($field, $entryid)
    {
        $key = $this->GetKey();
        $prefix = $this->GetPrefix();
        $img = $this->HexEncode(
            $GLOBALS['app']->Map->GetURLFor(
                'Policy',
                'Captcha',
                array('field' => $field, 'key' => $prefix . $key),
                false
            )
        );

        $entryid = isset($entryid)? $entryid : rand();
        $res = array();
        $res['label'] = _t('GLOBAL_CAPTCHA_CODE');
        $res['captcha'] =& Piwi::CreateWidget('Image', '', '');
        $res['captcha']->SetTitle(_t('GLOBAL_CAPTCHA_CODE'));
        $res['captcha']->SetID('captcha_img_'. $entryid);
        $res['captcha']->SetClass('captcha');
        $res['captcha']->SetSrc($img);
        $res['entry'] =& Piwi::CreateWidget('Entry', $prefix . $key, '');
        $res['entry']->SetID('captcha_'. $entryid);
        $res['entry']->SetStyle('direction: ltr;');
        $res['entry']->SetTitle(_t('GLOBAL_CAPTCHA_CASE_INSENSITIVE'));
        $res['description'] = _t('GLOBAL_CAPTCHA_CODE_DESC');
        return $res;
    }

    /**
     * Convert the string to an image so captcha can serve it
     *
     * @access  public
     * @param   string  $string Text to show
     * @return  string  String in HexCode
     */
    function HexEncode($string) 
    {
        $string = bin2hex($string);
        $res = '';
        for($i=0; $i<strlen($string); $i+=2) {
            $res .= '&#' . hexdec($string{$i} . $string{$i+1}) . ';';
        }
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
        $key   = '';
        $value = '';
        $prefix = $this->GetPrefix();
        foreach ($request->data['post'] as $k => $v) {
            if (substr($k, 0, strlen($prefix)) == $prefix) {
                $key = substr($k, 32);
                $value = $v; 
                break;
            } 
        }

        $captcha_value = $this->GetValue($key);
        $result = ($captcha_value !== false) && (strtolower($captcha_value) === strtolower($value));

        $this->RemoveKey($key);
        return $result;
    }

    /**
     * Remove a captcha key once it has been used (with success or failure)
     *
     * @access  public
     * @param   string  $key  Captcha key
     */
    function RemoveKey($key = null)
    {
        $params = array();
        // 10 minutes for cleantime
        $params['key'] = $key;
        $params['cleantime'] = date('Y-m-d H:i:s', time() - 600);
        $sql = "
            DELETE FROM [[captcha_complex]]
            WHERE [createtime] <= {cleantime}";
        if (!is_null($key)) {
            $sql .= ' OR [key] = {key}';
        }

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            Jaws_Error::Fatal("Can't remove keys", __FILE__, __LINE__);
        }
    }

    /**
     * Returns the prefix (we use it to know where the captcha came from)
     *
     * @access  private
     * @return  string    Prefix to use
     */
    function GetPrefix()
    {
        return md5(implode(Jaws_Utils::GetRemoteAddress()) . $GLOBALS['app']->getSiteURL());
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
        $params = array();
        $params['key'] = $key;
        $sql = "
            SELECT [value]
            FROM [[captcha_complex]]
            WHERE [key] = {key}";
        $result = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($result) || empty($result)) {
            $result = false;
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
        $key = uniqid(rand(0, 99999)) . time() . floor(microtime()*1000);

        $params = array();
        $params['key']   = $key;
        $params['value'] = $this->GenerateRandomValue();
        $params['createtime'] = $GLOBALS['db']->Date();

        $sql = "
            INSERT INTO [[captcha_complex]]
                ([key], [value], [createtime])
            VALUES
                ({key}, {value}, {createtime})";

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $key = '';
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, $result->getMessage());
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
        $key = str_replace($this->GetPrefix(), '', $key);
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