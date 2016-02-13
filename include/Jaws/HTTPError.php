<?php
/**
 * Show the Jaws Page not found message
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_HTTPError
{
    /**
     * Get HTTP status reponse
     *
     * @access  public
     * @param   int     $code       Status code
     * @param   string  $title      Reponse page title
     * @param   string  $message    Response message
     * @return  string  HTML template content
     */
    static function Get($code, $title = null, $message = null)
    {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        // Let everyone know a HTTP error has been happened
        $result = $GLOBALS['app']->Listener->Shout('HTTPError', 'HTTPError', $code, 'UrlMapper');
        if (!Jaws_Error::IsError($result) && !empty($result)) {
            $code = empty($result['code'])? $code : $result['code'];
        }

        switch ($code) {
            case 401:
                $realm = $GLOBALS['app']->Registry->fetch('realm', 'Settings');
                jaws()->http_response_code(401);
                // using invalid authentication type for avoid popup login box
                header('WWW-Authenticate: LoginBox realm="'. $realm. '"');

                $urlLogin = $GLOBALS['app']->Map->GetURLFor(
                    'Users',
                    'LoginBox',
                    array('referrer' => bin2hex(Jaws_Utils::getRequestURL(true)))
                );
                $title   = empty($title)? _t('GLOBAL_HTTP_ERROR_TITLE_401') : $title;
                $message = empty($message)? _t('GLOBAL_HTTP_ERROR_CONTENT_401', $urlLogin) : $message;
                break;

            case 403:
                jaws()->http_response_code(403);
                $title   = empty($title)? _t('GLOBAL_HTTP_ERROR_TITLE_403') : $title;
                $message = empty($message)? _t('GLOBAL_HTTP_ERROR_CONTENT_403') : $message;
                break;

            case 404:
                $uri = Jaws_XSS::filter(Jaws_Utils::getRequestURL(false));
                if (empty($message)) {
                    $message = _t('GLOBAL_HTTP_ERROR_CONTENT_404', $uri);
                }
                jaws()->http_response_code(404);
                $title = empty($title)? _t('GLOBAL_HTTP_ERROR_TITLE_404') : $title;
                break;

            case 410:
                jaws()->http_response_code(410);
                $title   = empty($title)? _t('GLOBAL_HTTP_ERROR_TITLE_410') : $title;
                $message = empty($message)? _t('GLOBAL_HTTP_ERROR_CONTENT_410') : $message;
                break;

            case 500:
                jaws()->http_response_code(500);
                $title   = empty($title)? _t('GLOBAL_HTTP_ERROR_TITLE_500') : $title;
                $message = empty($message)? _t('GLOBAL_HTTP_ERROR_CONTENT_500') : $message;
                break;

            case 503:
                jaws()->http_response_code(503);
                $title   = empty($title)? _t('GLOBAL_HTTP_ERROR_TITLE_503') : $title;
                $message = empty($message)? _t('GLOBAL_HTTP_ERROR_CONTENT_503') : $message;
                break;

            default:
                $title   = empty($title)? _t("GLOBAL_HTTP_ERROR_TITLE_$code") : $title;
                $message = empty($message)? _t("GLOBAL_HTTP_ERROR_CONTENT_$code") : $message;
        }

        // if current theme has a error code html file, return it, if not return the messages.
        $theme = $GLOBALS['app']->GetTheme();
        $site_name = $GLOBALS['app']->Registry->fetch('site_name', 'Settings');
        if (file_exists($theme['path'] . "$code.html")) {
            $tpl = new Jaws_Template();
            $tpl->Load("$code.html", $theme['path']);
            $tpl->SetBlock($code);

            //set global site config
            $direction = _t('GLOBAL_LANG_DIRECTION');
            $dir  = $direction == 'rtl' ? '.' . $direction : '';
            $brow = $GLOBALS['app']->GetBrowserFlag();
            $brow = empty($brow)? '' : '.'.$brow;

            $tpl->SetVariable('.dir', $dir);
            $tpl->SetVariable('.browser', $brow);
            $tpl->SetVariable('site-name',   $site_name);
            $tpl->SetVariable('site-title',  $site_name);
            $tpl->SetVariable('site-slogan', $GLOBALS['app']->Registry->fetch('site_slogan', 'Settings'));
            $tpl->SetVariable('site-author',      $GLOBALS['app']->Registry->fetch('site_author', 'Settings'));
            $tpl->SetVariable('site-copyright',   $GLOBALS['app']->Registry->fetch('copyright', 'Settings'));
            $tpl->SetVariable('site-description', $GLOBALS['app']->Registry->fetch('site_description', 'Settings'));

            $tpl->SetVariable('title',   $title);
            $tpl->SetVariable('content', $message);

            $tpl->ParseBlock($code);
            return $tpl->Get();
        }

        return "<div class=\"gadget_header\"><div class=\"gadget_title\"><h3>{$title}</h3></div></div>".
               "<div class=\"gadget_container\"><div class=\"content\">{$message}</div></div>";
    }

}