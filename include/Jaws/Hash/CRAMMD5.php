<?php
/**
 * CRAM-MD5
 *
 * @category    Hash
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @based on    https://github.com/hn/dovecot-misc
 */
class Jaws_Hash_CRAMMD5
{
    /*
     *
     */
    static private function rhex($n) {
        $r = '';
        $hex_chr = "0123456789abcdef";
        for($j = 0; $j <= 3; $j++)
            $r .= $hex_chr[($n >> ($j * 8 + 4)) & 0x0F] . $hex_chr[($n >> ($j * 8)) & 0x0F];
        return $r;
    }

    /* zeroFill() is needed because PHP doesn't have a zero-fill
     * right shift operator like JavaScript's >>>
     */
    static private function zeroFill($a, $b) {
        $z = hexdec(80000000);
        if ($z & $a) {
            $a >>= 1;
            $a &= (~$z);
            $a |= 0x40000000;
            $a >>= ($b-1);
        } else {
            $a >>= $b;
        }
        return $a;
    }

    /* Bitwise rotate a 32-bit number to the left
     */
    static private function bit_rol($num, $cnt) {
        return ($num << $cnt) | (self::zeroFill($num, (32 - $cnt)));
    }

    /* Add integers, wrapping at 2^32
     */
    static private function safe_add($x, $y) {
        return (($x&0x7FFFFFFF) + ($y&0x7FFFFFFF)) ^ ($x&0x80000000) ^ ($y&0x80000000);
    }

    /* These functions implement the four basic operations the algorithm uses.
     */
    static private function md5_cmn($q, $a, $b, $x, $s, $t) {
        return self::safe_add(self::bit_rol(self::safe_add(self::safe_add($a, $q), self::safe_add($x, $t)), $s), $b);
    }
    static private function md5_ff($a, $b, $c, $d, $x, $s, $t) {
        return self::md5_cmn(($b & $c) | ((~$b) & $d), $a, $b, $x, $s, $t);
    }
    static private function md5_gg($a, $b, $c, $d, $x, $s, $t) {
        return self::md5_cmn(($b & $d) | ($c & (~$d)), $a, $b, $x, $s, $t);
    }
    static private function md5_hh($a, $b, $c, $d, $x, $s, $t) {
        return self::md5_cmn($b ^ $c ^ $d, $a, $b, $x, $s, $t);
    }
    static private function md5_ii($a, $b, $c, $d, $x, $s, $t) {
        return self::md5_cmn($c ^ ($b | (~$d)), $a, $b, $x, $s, $t);
    }

    /* Calculate the first round of the MD5 algorithm
     */
    static private function md5_oneround($s, $io) {

        $s = str_pad($s, 64, chr(0x00));

        $x = array_fill(0, 16, 0);

        for($i = 0; $i < 64; $i++)
            $x[$i >> 2] |= (($io ? 0x36 : 0x5c) ^ ord($s[$i])) << (($i % 4) * 8);

        $a = $olda =  1732584193;
        $b = $oldb = -271733879;
        $c = $oldc = -1732584194;
        $d = $oldd =  271733878;

        $a = self::md5_ff($a, $b, $c, $d, $x[ 0], 7 , -680876936);
        $d = self::md5_ff($d, $a, $b, $c, $x[ 1], 12, -389564586);
        $c = self::md5_ff($c, $d, $a, $b, $x[ 2], 17,  606105819);
        $b = self::md5_ff($b, $c, $d, $a, $x[ 3], 22, -1044525330);
        $a = self::md5_ff($a, $b, $c, $d, $x[ 4], 7 , -176418897);
        $d = self::md5_ff($d, $a, $b, $c, $x[ 5], 12,  1200080426);
        $c = self::md5_ff($c, $d, $a, $b, $x[ 6], 17, -1473231341);
        $b = self::md5_ff($b, $c, $d, $a, $x[ 7], 22, -45705983);
        $a = self::md5_ff($a, $b, $c, $d, $x[ 8], 7 ,  1770035416);
        $d = self::md5_ff($d, $a, $b, $c, $x[ 9], 12, -1958414417);
        $c = self::md5_ff($c, $d, $a, $b, $x[10], 17, -42063);
        $b = self::md5_ff($b, $c, $d, $a, $x[11], 22, -1990404162);
        $a = self::md5_ff($a, $b, $c, $d, $x[12], 7 ,  1804603682);
        $d = self::md5_ff($d, $a, $b, $c, $x[13], 12, -40341101);
        $c = self::md5_ff($c, $d, $a, $b, $x[14], 17, -1502002290);
        $b = self::md5_ff($b, $c, $d, $a, $x[15], 22,  1236535329);

        $a = self::md5_gg($a, $b, $c, $d, $x[ 1], 5 , -165796510);
        $d = self::md5_gg($d, $a, $b, $c, $x[ 6], 9 , -1069501632);
        $c = self::md5_gg($c, $d, $a, $b, $x[11], 14,  643717713);
        $b = self::md5_gg($b, $c, $d, $a, $x[ 0], 20, -373897302);
        $a = self::md5_gg($a, $b, $c, $d, $x[ 5], 5 , -701558691);
        $d = self::md5_gg($d, $a, $b, $c, $x[10], 9 ,  38016083);
        $c = self::md5_gg($c, $d, $a, $b, $x[15], 14, -660478335);
        $b = self::md5_gg($b, $c, $d, $a, $x[ 4], 20, -405537848);
        $a = self::md5_gg($a, $b, $c, $d, $x[ 9], 5 ,  568446438);
        $d = self::md5_gg($d, $a, $b, $c, $x[14], 9 , -1019803690);
        $c = self::md5_gg($c, $d, $a, $b, $x[ 3], 14, -187363961);
        $b = self::md5_gg($b, $c, $d, $a, $x[ 8], 20,  1163531501);
        $a = self::md5_gg($a, $b, $c, $d, $x[13], 5 , -1444681467);
        $d = self::md5_gg($d, $a, $b, $c, $x[ 2], 9 , -51403784);
        $c = self::md5_gg($c, $d, $a, $b, $x[ 7], 14,  1735328473);
        $b = self::md5_gg($b, $c, $d, $a, $x[12], 20, -1926607734);

        $a = self::md5_hh($a, $b, $c, $d, $x[ 5], 4 , -378558);
        $d = self::md5_hh($d, $a, $b, $c, $x[ 8], 11, -2022574463);
        $c = self::md5_hh($c, $d, $a, $b, $x[11], 16,  1839030562);
        $b = self::md5_hh($b, $c, $d, $a, $x[14], 23, -35309556);
        $a = self::md5_hh($a, $b, $c, $d, $x[ 1], 4 , -1530992060);
        $d = self::md5_hh($d, $a, $b, $c, $x[ 4], 11,  1272893353);
        $c = self::md5_hh($c, $d, $a, $b, $x[ 7], 16, -155497632);
        $b = self::md5_hh($b, $c, $d, $a, $x[10], 23, -1094730640);
        $a = self::md5_hh($a, $b, $c, $d, $x[13], 4 ,  681279174);
        $d = self::md5_hh($d, $a, $b, $c, $x[ 0], 11, -358537222);
        $c = self::md5_hh($c, $d, $a, $b, $x[ 3], 16, -722521979);
        $b = self::md5_hh($b, $c, $d, $a, $x[ 6], 23,  76029189);
        $a = self::md5_hh($a, $b, $c, $d, $x[ 9], 4 , -640364487);
        $d = self::md5_hh($d, $a, $b, $c, $x[12], 11, -421815835);
        $c = self::md5_hh($c, $d, $a, $b, $x[15], 16,  530742520);
        $b = self::md5_hh($b, $c, $d, $a, $x[ 2], 23, -995338651);

        $a = self::md5_ii($a, $b, $c, $d, $x[ 0], 6 , -198630844);
        $d = self::md5_ii($d, $a, $b, $c, $x[ 7], 10,  1126891415);
        $c = self::md5_ii($c, $d, $a, $b, $x[14], 15, -1416354905);
        $b = self::md5_ii($b, $c, $d, $a, $x[ 5], 21, -57434055);
        $a = self::md5_ii($a, $b, $c, $d, $x[12], 6 ,  1700485571);
        $d = self::md5_ii($d, $a, $b, $c, $x[ 3], 10, -1894986606);
        $c = self::md5_ii($c, $d, $a, $b, $x[10], 15, -1051523);
        $b = self::md5_ii($b, $c, $d, $a, $x[ 1], 21, -2054922799);
        $a = self::md5_ii($a, $b, $c, $d, $x[ 8], 6 ,  1873313359);
        $d = self::md5_ii($d, $a, $b, $c, $x[15], 10, -30611744);
        $c = self::md5_ii($c, $d, $a, $b, $x[ 6], 15, -1560198380);
        $b = self::md5_ii($b, $c, $d, $a, $x[13], 21,  1309151649);
        $a = self::md5_ii($a, $b, $c, $d, $x[ 4], 6 , -145523070);
        $d = self::md5_ii($d, $a, $b, $c, $x[11], 10, -1120210379);
        $c = self::md5_ii($c, $d, $a, $b, $x[ 2], 15,  718787259);
        $b = self::md5_ii($b, $c, $d, $a, $x[ 9], 21, -343485551);

        $a = self::safe_add($a, $olda);
        $b = self::safe_add($b, $oldb);
        $c = self::safe_add($c, $oldc);
        $d = self::safe_add($d, $oldd);

        return self::rhex($a) . self::rhex($b) . self::rhex($c) . self::rhex($d);
    }

    /*
     *
     */
    static function hash($s) {
        if (strlen($s) > 64) $s=pack("H*", md5($s));
        return self::md5_oneround($s, 0) . self::md5_oneround($s, 1);
    }

}