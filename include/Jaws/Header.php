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
     * @param   string  $url URL to move the location
     * @param   bool    $addSiteURL
     * @param   int     $statusCode
     * @access  public
     */
    function Location($url = '', $statusCode = 302)
    {
        if (isset($GLOBALS['app']->Session)) {
            $GLOBALS['app']->Session->Synchronize();
        }

        if (empty($url) || !preg_match('$^(http|https|ftp)://.*$i', $url)) {
            $url = $GLOBALS['app']->getSiteURL('/'). $url;
        }

        if ($statusCode == 301) {
            header('HTTP/1.1 301 Moved Permanently');
        } else {
            header('HTTP/1.1 302 Found');
        }

        header('Location: '.$url);
        exit;
    }

    /**
     * Redirect to referrer page
     *
     * @access  public
     */
    function Referrer()
    {
        if (isset($GLOBALS['app']->Session)) {
            $GLOBALS['app']->Session->Synchronize();
        }

        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $url = $_SERVER['HTTP_REFERER'];
        } else {
            $url = $GLOBALS['app']->getSiteURL('/');
        }

        header('HTTP/1.1 302 Found');
        header('Location: '.$url);
        exit;
    }

    /**
     * Redirects the browser to another url via HTTP Refresh method
     *
     * @param   string  $url     Url to redirect
     * @param   int     $timeout Timeout to redirect
     * @access  public
     */
    function Refresh($url, $timeout = 0)
    {
        $timeout = is_numeric($timeout)? $timeout : 0;
        header('Refresh: '.$timeout.'; URL='.$url);
    }

    /**
     * Change the status to 404
     *
     * @access  public
     */
    function ChangeTo404()
    {
        header('Status: 404 Not Found');
    }

    /**
     * Set expiration date
     *
     * Take a look at: http://www.php.net/manual/en/function.header.php for examples
     * @param   string  $date Date in format: Day, day Month Year Hour:Minutes:Seconds GMT
     * @access  public
     */
    function Expire($date)
    {
        header('Expires: {$date}');
    }

    /**
     * Disables the cache of browser
     *
     * @access  public
     */
    function DisableCache()
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
    function ChangeContent($ctype, $file)
    {
        header('Content-type: '.$ctype);
        header('Content-Disposition: attachment; filename='.$file);
    }
}
