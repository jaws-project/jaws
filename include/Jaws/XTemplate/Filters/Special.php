<?php
/**
 * Template engine special registered filters
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_Filters_Special extends Jaws_XTemplate_Filters
{
    /**
     * Get map version of url if found
     *
     * @param   string   $gadget    Gadget name
     * @param   string   $action    Action name
     * @param   array    $params    Map parameters
     *
     * @return string
     */
    public static function urlmap($gadget, $action, ...$params)
    {
        $urlParams = array(
            'keys'  => array(),
            'vals'  => array(),
            'mixed' => array()
        );

        array_walk($params, function($val, $key) use (&$urlParams) {
            if (is_array($val)) {
                $urlParams['keys'] = array_merge($urlParams['keys'], array_keys($val));
                $urlParams['vals'] = array_merge($urlParams['vals'], array_values($val));
            } else {
                $urlParams['mixed'][] = $val;
            }
        });

        $urlParams['mixed'] = array_chunk(array_pad($urlParams['mixed'], round(count($urlParams['mixed'])/2)*2, null), 2);
        $urlParams['keys'] = array_merge($urlParams['keys'], array_column($urlParams['mixed'], 0));
        $urlParams['vals'] = array_merge($urlParams['vals'], array_column($urlParams['mixed'], 1));
        $urlParams = array_combine($urlParams['keys'], $urlParams['vals']);

        return Jaws::getInstance()->map->GetMappedURL(
            $gadget,
            $action,
            $urlParams
        );
    }

    /**
     * Get data url of given file/path
     *
     * @param   string  $suffix     suffix part of url
     * @param   bool    $rel_url    relative url
     * @param   bool    $base_data  use JAWS_BASE_DATA instead of ROOT_DATA_PATH
     *
     * @return string
     */
    public static function dataURL($suffixt = '', $rel_url = true, $base_data = false)
    {
        return Jaws::getInstance()->getDataURL($suffixt, $rel_url, $base_data);
    }

    /**
     * get session variables/attributes(user, global, gadget)
     *
     * @param   string    $var
     * @param   string    $component
     *
     * @return  string
     */
    public static function session($var, $component = '')
    {
        if (false !== $property = strstr($var, '.')) {
            $var = strstr($var, '.', true);
            $property = substr($property, 1);
            if (empty($component)) {
                $objKey = Jaws::getInstance()->session->$var;
            } else {
                $objKey = Jaws_Gadget::getInstance($component)->session->$var;
            }
            if (is_object($objKey) && property_exists($objKey, $property)) {
                return $objKey->$property;
            }

            return null;
        } elseif (empty($component)) {
            return Jaws::getInstance()->session->$var;
        } else {
            return Jaws::getInstance()->session->getAttribute($var, $component);
        }
    }

    /**
     * get registry on a gadget/task
     *
     * @param   string  $reg    Registry key name include gadget (ex. users.anon_register)
     * @param   int     $user   User Id (0: non-users value)
     *
     * @return  mixed
     */
    public static function registry($reg, $user = 0)
    {
        if (false === $gadget = strstr($reg, '.', true)) {
            return null;
        }

        $key = substr($reg, strlen($gadget) + 1);
        if (empty($user)) {
            return Jaws::getInstance()->registry->fetch($key, $gadget);
        } else {
            return Jaws::getInstance()->registry->fetchByUser($user, $key, $gadget);
        }
    }

    /**
     * get permission on a gadget/task
     *
     * @param   string  $acl        ACL key name include gadget (ex. Users.EditUserName)
     * @param   string  $subkey
     * @param   bool    $together   And/Or tasks permission result, default true
     *
     * @return  integer
     */
    public static function permission($acl, $subkey = '', $together = true)
    {
        if (Jaws::getInstance()->session->user->superadmin) {
            return 0xff;
        }

        if (false === $gadget = strstr($acl, '.', true)) {
            return 0;
        }

        $key = substr($acl, strlen($gadget) + 1);
        return Jaws::getInstance()->session->getPermission($gadget, $key, $subkey, $together);
    }

    /**
     * Calls layout::putGadget
     *
     * @param   string   $gadget    Gadget name
     * @param   string   $action    Action name
     * @param   array    $params    Map parameters
     *
     * @return string
     */
    public static function layout($gadget, $action, ...$params)
    {
        $privateAccess = Jaws::getInstance()->registry->fetch('global_website', 'Settings')== 'false';
        return Jaws::getInstance()->layout->PutGadget(
            $gadget,
            $action,
            $params,
            '',
            $privateAccess
        );
    }

    /**
     * get global variables(theme_url, data_url, .dir, .browser, ...)
     *
     * @param   string    $var
     *
     * @return  string
     */
    public static function global($var)
    {
        static $globalVariables = array();
        if (empty($globalVariables)) {
            $thisApp = Jaws::getInstance();
            $globalVariables['dir']      = Jaws::t('LANG_DIRECTION') == 'rtl'? 'rtl' : 'ltr';
            $globalVariables['.dir']     = Jaws::t('LANG_DIRECTION') == 'rtl'? '.rtl' : '';
            $globalVariables['site_url'] = Jaws_Utils::getBaseURL('/', false);
            $globalVariables['base_url'] = Jaws_Utils::getBaseURL('/');
            $globalVariables['requested_url'] = Jaws_Utils::getRequestURL();
            $globalVariables['base_script']   = BASE_SCRIPT;
            $globalVariables['data_url']    = $thisApp->getDataURL();
            $globalVariables['main_index']  = $thisApp->mainIndex? 'index' : '';
            $globalVariables['main_gadget'] = strtolower($thisApp->mainRequest['gadget']);
            $globalVariables['main_action'] = strtolower($thisApp->mainRequest['action']);
            // browser flag
            $browser = $thisApp->GetBrowserFlag();
            $globalVariables['.browser'] = empty($browser)? '' : ".$browser";
            // theme
            $theme = $thisApp->GetTheme();
            $globalVariables['theme_url'] = $theme['url'];
            // layout
            $layout = $thisApp->layout->GetLayoutName();
            $layout = @is_dir($theme['path']. '/'. $layout)? $layout : '';
            $globalVariables['main_layout'] = strtolower(str_replace('.', '_', $layout));
        }

        return array_key_exists($var, $globalVariables)? $globalVariables[$var] : '';
    }

    /**
     * Convenience function to translate strings
     *
     * @param   string   $input
     *
     * @return string
     */
    public static function t($input)
    {
        $args = func_get_args();
        array_shift($args);

        @list($string, $lang) = explode('|', $input);
        if ($component = strstr($string, '.', true)) {
            $string = substr($string, strlen($component) + 1);
        } else {
            $component = '';
        }

        $type = Jaws_Translate::TRANSLATE_GADGET;
        if ($component == 'global') {
             $type = Jaws_Translate::TRANSLATE_GLOBAL;
        }

        return Jaws_Translate::getInstance()->XTranslate(
            $lang,
            $type,
            $component,
            $string,
            $args
        );
    }

    /**
     * prints a variable in a human readable form to the jaws log specified
     *
     * @param   mixed   $input
     *
     * @return string
     */
    public static function log($input)
    {
        _log_var_dump($input);
        return '';
    }

}