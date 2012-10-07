<?php
/**
 * Get google page rank
 *
 * @category   Scripts
 * @package    Launcher
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class PageRank
{
    /**
     *
     */
    function saveFile($cache_file, $data)
    {
        if (!Jaws_Utils::file_put_contents($cache_file, serialize($data))) {
            return PEAR::raiseError("Fail to save stream with file_put_contents('$cache_file',...).");
        }

        return true;
    }

    /**
     *
     */
    function loadFile($cache_file)
    {
        if (false === $data = @file_get_contents($cache_file)) {
            return PEAR::raiseError("Fail to open '$cache_file', not found"); 
        }

        return unserialize($data);
    }

    /**
     * convert a string to a 32-bit integer
     */
    function StrToNum($Str, $Check, $Magic)
    {
        $Int32Unit = 4294967296; //2^32
        $length = strlen($Str);
        for ($i = 0; $i < $length; $i++) {
            $Check *= $Magic;
            //If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31),
            //  the result of converting to integer is undefined
            //  refer to http://www.php.net/manual/en/language.types.integer.php
            if ($Check >= $Int32Unit) {
                $Check = ($Check - $Int32Unit * (int) ($Check / $Int32Unit));
                //if the check less than -2^31
                $Check = ($Check < -2147483648) ? ($Check + $Int32Unit) : $Check;
            }
            $Check += ord($Str{$i});
        }
        return $Check;
    }

    /**
     * genearate a hash for a url
     */
    function HashURL($String)
    {
        $Check1 = $this->StrToNum($String, 0x1505, 0x21);
        $Check2 = $this->StrToNum($String, 0, 0x1003F);

        $Check1 >>= 2;  
        $Check1 = (($Check1 >> 4) & 0x3FFFFC0 ) | ($Check1 & 0x3F);
        $Check1 = (($Check1 >> 4) & 0x3FFC00 ) | ($Check1 & 0x3FF);
        $Check1 = (($Check1 >> 4) & 0x3C000 ) | ($Check1 & 0x3FFF);

        $T1 = (((($Check1 & 0x3C0) << 4) | ($Check1 & 0x3C)) <<2 ) | ($Check2 & 0xF0F );
        $T2 = (((($Check1 & 0xFFFFC000) << 4) | ($Check1 & 0x3C00)) << 0xA) | ($Check2 & 0xF0F0000 );

        return ($T1 | $T2);
    }

    /**
     * genearate a checksum for the hash string
     */
    function CheckHash($Hashnum)
    {
        $CheckByte = 0;
        $Flag = 0;

        $HashStr = sprintf('%u', $Hashnum);
        $length = strlen($HashStr);

        for ($i = $length - 1;  $i >= 0;  $i --) {
            $Re = $HashStr{$i};
            if (1 === ($Flag % 2)) {
                $Re += $Re;
                $Re = (int)($Re / 10) + ($Re % 10);
            }
            $CheckByte += $Re;
            $Flag ++;
        }

        $CheckByte %= 10;
        if (0 !== $CheckByte) {
            $CheckByte = 10 - $CheckByte;
            if (1 === ($Flag % 2) ) {
                if (1 === ($CheckByte % 2)) {
                    $CheckByte += 9;
                }
                $CheckByte >>= 1;
            }
        }

        return '7'.$CheckByte.$HashStr;
    }

    /**
     *
     */
    function getURL($url)
    {
        require_once 'HTTP/Request.php';
        $httpRequest = new HTTP_Request($url);
        $httpRequest->setMethod(HTTP_REQUEST_METHOD_GET);
        $resRequest  = $httpRequest->sendRequest();
        if (!PEAR::isError($resRequest) && $httpRequest->getResponseCode() == 200) {
            $data = $httpRequest->getResponseBody();
        } else {
            $data = @file_get_contents($url);
        }

        return $data;
    }

    /**
     *
     */
    function GetRank($url)
    {
        $rank = 'na';
        $ch   = $this->CheckHash($this->HashURL($url));
        $url  = urlencode($url);
        $rdata = $this->getURL("http://toolbarqueries.google.com/tbr?client=navclient-auto&ch=$ch&features=Rank&q=info:$url");
        if (!empty($rdata)) {
            $rank = trim(end(explode(':', $rdata)));
        }

        return $rank;
    }
}

function GooglePageRank()
{
    $cache_dir = JAWS_DATA . 'launcher' . DIRECTORY_SEPARATOR;
    if (!Jaws_Utils::mkdir($cache_dir)) {
        return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $cache_dir),
                              __FUNCTION__);
    }

    $url = $GLOBALS['app']->GetSiteURL('/');
    $file = $cache_dir . 'rank_' . md5($url);
    $timedif = time() - (file_exists($file)? @filemtime($file) : 0);

    $gRank = new PageRank();
    if ($timedif < 604800) { // a week
        //cache file is fresh
        $rank = $gRank->loadFile($file);
    } else {
        $rank = $gRank->GetRank($url);
        $gRank->saveFile($file, $rank);
    }

    unset($gRank);
    $theme = $GLOBALS['app']->GetTheme();
    if (is_dir($theme['path'] . 'PageRank')) {
        $pg_images = $theme['url'] .'PageRank/';
    } else {
        $pg_images = $GLOBALS['app']->getSiteURL('/gadgets/Launcher/images/PageRank/', true);
    }

    return "<img src='{$pg_images}pr$rank.gif' alt='$rank' />";
}