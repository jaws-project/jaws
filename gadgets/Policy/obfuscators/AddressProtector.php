<?php
/**
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2020 Jaws Development Group
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
        $ap_dir = ROOT_DATA_PATH . 'cache/addressprotector';
        if (file_exists($ap_dir. '/' . md5($email . $name))) {
            $contents = Jaws_FileManagement_File::file_get_contents($ap_dir. '/'. md5($email . $name));
            $contents = '<a href="http://address-protector.com/' . $contents . '">' . $name . '</a>';
            return $contents;
        }

        Jaws_FileManagement_File::mkdir($ap_dir);
        if (!is_dir($ap_dir) || !Jaws_FileManagement_File::is_writable($ap_dir) || !(bool)ini_get('allow_url_fopen')) {
            $contents = str_replace(array('@', '.'), array('(at)', 'dot'), $email);
            return $contents;
        }

        $url = "http://address-protector.com/?mode=textencrypt&name=<name>&email=<email>";
        $url = str_replace('<name>', urlencode($name), $url);
        $url = str_replace('<email>', $email, $url);
        $contents = $this->getURL($url);
        if (empty($contents)) {
            $contents = str_replace(array('@', '.'), array('(at)', 'dot'), $email);
        }

        if (substr($contents, -1, 1) == "\n") {
            $contents = substr($contents, 0, -1);
        }             
        Jaws_FileManagement_File::file_put_contents($ap_dir. '/'. md5($email . $name), $contents);
        $contents = '<a href="http://address-protector.com/' . $contents . '">' . $name . '</a>';
        return $contents;
    }
}