<?php
/**
 * Class to convert to different encodings
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_EncodeConvert
{
    /**
     * Return the encoding used in the string
     *
     * @param   string  $string  String to use
     * @return  string  String encoding
     * @access  public
     *
     */
    function Detect($string)
    {
        return mb_detect_encoding($string);
    }

    /**
     * Detect the encoding that HTTP is using
     *
     * @return  string  Returns the encoding that HTTP is using
     * @access  public
     */
    function DetectHTTP()
    {
        return mb_http_input();
    }

    /**
     * Convert a UTF8 string to ISO 8859-1
     *
     * @param   string  $string  String to decode
     * @return  string  Converted string
     * @access  public
     */
    function UTF8ToISO8859($string)
    {
        return utf8_decode($string);
    }

    /**
     * Convert UTF8 data to HTML data
     * FROM: http://www.php.net/utf8_decode
     *
     * @param   string Decode the UTF8 data and converts it to HTML(entities)
     * @return  string Converted string
     * @access  public
     */
    function UTF8ToHTML($string)
    {
        $retstr = '';
        $length = count($string);
        for ($p = 0; $p < $length; $p++) {
            $c = substr($string, $p, 1);
            $c1 = ord($c);
            if ($c1>>5 == 6) {
                // 110x xxxx, 110 prefix for 2 bytes unicode
                $p++;
                $t = substr($string, $p, 1);
                $c2 = ord($t);
                $c1 &= 31; // remove the 3 bit two bytes prefix
                $c2 &= 63; // remove the 2 bit trailing byte prefix
                $c2 |=(($c1 & 3) << 6); // last 2 bits of c1 become first 2 of c2
                $c1 >>= 2; // c1 shifts 2 to the right
                $n = dechex($c1).dechex($c2);
                $retstr .= sprintf("&#%03d;", hexdec($n));
            } else {
                $retstr .= $c;
            }
        }
        return $retstr;
    }

}