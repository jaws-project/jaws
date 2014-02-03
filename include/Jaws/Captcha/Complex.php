<?php
/**
 * Complex captcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Captcha_Complex extends Jaws_Captcha
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

        $width = 15 * imagefontwidth (5);
        $height = 2.5 * imagefontheight (5);
        $im = imagecreatetruecolor ($width, $height);
        imagealphablending($im, true);
        $black = imagecolorallocatealpha($im, 0, 0, 0, 0);

        $rotated = imagecreatetruecolor(70, 70);
        $x = 0;
        $text_length = strlen($value);
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