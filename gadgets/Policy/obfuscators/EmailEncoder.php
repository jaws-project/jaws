<?php
/**
 * Based on HideEmail function by Yuriy Horobey (SmiledSoft)
 * http://www.smiledsoft.com/demos/hideemail/index.shtml
 */

class EmailEncoder
{
    function Get($email, $name)
    {
        $code = '<a  href="'. $this->encText('mailto:', true). $this->encText($email, true).'">' . $name . '</a>';
        $javacode='<script language="JavaScript" type="text/JavaScript">';
        $i = 0;
        $code_l = Jaws_UTF8::strlen($code);
        while ($i < $code_l) {
            //get next part of code with random length from 15 to 20
            $len = rand(15, 20);
            if ($i + $len > $code_l) {
                $len = $code_l - $i;
            }
            $part = Jaws_UTF8::substr($code, $i, $len);
            $javacode .="document.write('$part');";
            $i += $len;
        }
        $javacode .= "</script>";
        return $javacode;
    }

    function encText($s, $dec=false)
    {
        $s = bin2hex($s);
        $res = '';
        $s_l = strlen($s);
        for ($i = 0; $i < $s_l; $i = $i + 2) {
            if ($dec) {
                $res .= '&#' . hexdec($s{$i} . $s{$i + 1}) . ';';
            } else {
                $res .= '%' . $s{$i} . $s{$i + 1};
            }
        }

        return $res;
    }
}