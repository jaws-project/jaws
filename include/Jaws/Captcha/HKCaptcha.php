<?php
/**
 * HKCaptcha
 *
 * @category    Captcha
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2010-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @based on    http://www.lagom.nl/linux/hkcaptcha/
 */
class Jaws_Captcha_HKCaptcha extends Jaws_Captcha
{
    /**
     * Get a distorted image
     *
     * @access  private
     * @param   resource    $tmpimg Temporary image resource
     * @return  resource    $img    Image resource
     * @return  void
     */
    private function warpedImage(&$tmpimg, &$img)
    {
        $numpoles = 3;
        $height = imagesy($img);
        $width  = imagesx($img);

        // make an array of poles AKA attractor points
        for ($i = 0; $i < $numpoles; ++$i) {
            do {
                $px[$i] = rand(0, $width);
            } while ($px[$i] >= $width*0.3 && $px[$i] <= $width*0.7);

            do {
                $py[$i] = rand(0, $height);
            } while ($py[$i] >= $height*0.3 && $py[$i] <= $height*0.7);

            $rad[$i] = rand($width*0.4, $width*0.8);
            $amp[$i] = -0.0001 * rand(0,9999) * 0.15 - 0.15;
        }

        // get img properties bgcolor
        $bgcol = imagecolorat($tmpimg, 1, 1);
        $iscale  = imagesy($tmpimg) / imagesy($img);

        // loop over $img pixels, take pixels from $tmpimg with distortion field
        for ($ix = 0; $ix < $width; ++$ix) {
            for ($iy = 0; $iy < $height; ++$iy) {
                $x = $ix;
                $y = $iy;
                for ($i = 0; $i < $numpoles; ++$i) {
                    $dx = $ix - $px[$i];
                    $dy = $iy - $py[$i];
                    if ($dx == 0 && $dy == 0) {
                        continue;
                    }

                    $r = sqrt($dx*$dx + $dy*$dy);
                    if ($r > $rad[$i]) {
                      continue;
                    }

                    $rscale = $amp[$i] * sin(3.14*$r/$rad[$i]);
                    $x += $dx*$rscale;
                    $y += $dy*$rscale;
                }

                $c = $bgcol;
                $x *= $iscale;
                $y *= $iscale;
                if ($x >= 0 && $x < imagesx($tmpimg) && $y >= 0 && $y < imagesy($tmpimg)) {
                    $c = imagecolorat($tmpimg, $x, $y);
                }

                imagesetpixel($img, $ix, $iy, $c);
            }
        }
    }

    /**
     * Displays the captcha image
     *
     * @access  public
     * @param   int     $key    Captcha key
     * @return  mixed   Captcha raw image data
     */
    function image($key = null)
    {
        $value  = Jaws_Utils::RandomText();
        $result = $this->update($key, $value);
        if (Jaws_Error::IsError($result)) {
            $value = '';
        }

        $width  = 15 * imagefontwidth(5);
        $height = 4 * imagefontheight(5);
        $font = dirname(__FILE__) . '/resources/comicbd.ttf';

        $tmpimg  = imagecreate($width*2, $height*2);
        $bgColor = imagecolorallocatealpha($tmpimg, 255, 255, 255, 127);
        $col = imagecolorallocate($tmpimg, 0, 0, 0);

        // init final image
        $img = imagecreate($width, $height);
        imagepalettecopy($img, $tmpimg);    
        imagecopy($img, $tmpimg, 0,0 ,0,0, $width, $height);

        // put text into $tmpimg
        $fsize = $height*0.6;
        $bb = imageftbbox($fsize, 0, $font, $value);
        $tx = $bb[4]-$bb[0];
        $ty = $bb[5]-$bb[1];
        $x = floor($width - $tx/2 - $bb[0]);
        $y = round($height - $ty/2 - $bb[1]);
        imagettftext($tmpimg, $fsize, 0, $x, $y, -$col, $font, $value);

        // warp text
        $this->warpedImage($tmpimg, $img);

        header("Content-Type: image/png");

        ob_start();
        imagepng($img);
        $content = ob_get_contents();
        ob_end_clean();

        imagedestroy($img);
        imagedestroy($tmpimg);
        return $content;
    }

}