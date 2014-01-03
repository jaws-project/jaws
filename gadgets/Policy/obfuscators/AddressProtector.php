<?php
/**
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class AddressProtector
{
    /**
     *
     */
    function getURL($url)
    {
        require_once PEAR_PATH. 'HTTP/Request.php';
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
    function Get($email, $name)
    {
        $ap_dir = JAWS_DATA . 'cache' . DIRECTORY_SEPARATOR . 'addressprotector';
        if (file_exists($ap_dir. DIRECTORY_SEPARATOR . md5($email . $name))) {
            $contents = file_get_contents($ap_dir. DIRECTORY_SEPARATOR. md5($email . $name));
            $contents = '<a href="http://address-protector.com/' . $contents . '">' . $name . '</a>';
            return $contents;
        }

        Jaws_Utils::mkdir($ap_dir);
        if (!is_dir($ap_dir) || !Jaws_Utils::is_writable($ap_dir) || !(bool)ini_get('allow_url_fopen')) {
            $contents = str_replace(array('@', '.'), array('(at)', 'dot'), $email);
            return $contents;
        }

        $url = "http://address-protector.com/?mode=textencrypt&name=<name>&email=<email>";
        $url  = str_replace('<name>', urlencode($name), $url);
        $url = str_replace('<email>', $email, $url);
        $contents = $this->getURL($url);
        if (empty($contents)) {
            $contents = str_replace(array('@', '.'), array('(at)', 'dot'), $email);
        }

        if (substr($contents, -1, 1) == "\n") {
            $contents = substr($contents, 0, -1);
        }             
        file_put_contents($ap_dir. DIRECTORY_SEPARATOR. md5($email . $name), $contents);
        $contents = '<a href="http://address-protector.com/' . $contents . '">' . $name . '</a>';
        return $contents;
    }
}