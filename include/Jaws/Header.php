<?php
/**
 * Class to modify the header of the browser
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * This is an abstract class, so construct does not exists
 */
class Jaws_Header
{
    /**
     * Redirects the browser to another url via HTTP Location method
     *
     * @param   string  $url            URL to move the location
     * @param   int     $status_code
     * @access  public
     */
    static function Location($url = '', $resource = '', $status_code = 302)
    {
        if (empty($url) || !preg_match('$^(http|https|ftp)://.*$i', $url)) {
            $url = $GLOBALS['app']->getSiteURL('/'). $url;
        }

        terminate($resource, $status_code, $url);
    }

    /**
     * Redirect to referrer page
     *
     * @access  public
     */
    static function Referrer()
    {
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $url = $_SERVER['HTTP_REFERER'];
        } else {
            $url = $GLOBALS['app']->getSiteURL('/');
        }

        $data = null;
        terminate($data, 302, $url);
    }

    /**
     * Redirects the browser to another url via HTTP Refresh method
     *
     * @param   string  $url     Url to redirect
     * @param   int     $timeout Timeout to redirect
     * @access  public
     */
    static function Refresh($url, $timeout = 0)
    {
        $timeout = is_numeric($timeout)? $timeout : 0;
        header('Refresh: '.$timeout.'; URL='.$url);
    }

    /**
     * Set expiration date
     *
     * Take a look at: http://www.php.net/manual/en/function.header.php for examples
     * @param   string  $date Date in format: Day, day Month Year Hour:Minutes:Seconds GMT
     * @access  public
     */
    static function Expire($date)
    {
        header('Expires: {$date}');
    }

    /**
     * Disables the cache of browser
     *
     * @access  public
     */
    static function DisableCache()
    {
        // HTTP/1.1
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);

        // HTTP/1.0
        header('Pragma: no-cache');
    }

    /**
     * Change the content disposition of the file and change its filename
     *
     * @param   string  $ctype  Content type
     * @param   string  $file   Filename
     * @access  public
     */
    static function ChangeContent($ctype, $file)
    {
        header('Content-type: '.$ctype);
        header('Content-Disposition: attachment; filename='.$file);
    }
}
