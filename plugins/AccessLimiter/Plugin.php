<?php
/**
 * Limits access to the content or part of the content for users and user groups
 *
 * @category   Plugin
 * @package    AccessLimiter
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2009-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class AccessLimiter_Plugin extends Jaws_Plugin
{
    var $friendly = false;
    var $version = '0.1';

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
     */
    function ParseText($html)
    {
        $blockPattern = '@\[limited\s*(users="(.*?)")?\s*(groups="(.*?)")?\](.*?)\[/limited\]@ism';
        $new_html = preg_replace_callback($blockPattern, array(&$this, 'Prepare'), $html);
        return $new_html;
    }

    /**
     * The preg_replace call back function
     *
     * @access  private
     * @param   string  $data   Matched strings from preg_replace_callback
     * @return  string  Gadget's action output or access message
     */
    function Prepare($data)
    {
        $users  = $data[2];
        $groups = $data[4];
        $content= &$data[5];

        if ($GLOBALS['app']->Session->Logged()) {
            if (!$GLOBALS['app']->Session->IsSuperAdmin()) {
                $users  = empty($users) ? array() : array_map('trim', explode(',', $users));
                $groups = empty($groups)? array() : array_map('trim', explode(',', $groups));

                static $user_groups;
                $user = $GLOBALS['app']->Session->GetAttribute('username');
                if (!isset($user_groups)) {
                    $user_groups = $GLOBALS['app']->Session->GetAttribute('groups');
                    $user_groups = array_values($user_groups);
                }

                if (!empty($users) || !empty($groups)) {
                    if ((empty($users)  || !in_array($user, $users)) &&
                        (empty($groups) || !count(array_intersect($groups, $user_groups))))
                    {
                        $tpl = new Jaws_Template();
                        $tpl->Load('AccessLimiter.html', 'plugins/AccessLimiter/Templates/');
                        $tpl->SetBlock('AccessLimiter');
                        $tpl->SetVariable('message', _t('GLOBAL_ERROR_ACCESS_DENIED'));
                        $tpl->ParseBlock('AccessLimiter');
                        return $tpl->Get();
                    }
                }
            }

            return $content;
        }

        $tpl = new Jaws_Template();
        $tpl->Load('AccessLimiter.html', 'plugins/AccessLimiter/Templates/');
        $tpl->SetBlock('AccessLimiter');
        $login_url    = $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox');
        $register_url = $GLOBALS['app']->Map->GetURLFor('Users', 'Registration');
        $tpl->SetVariable('message', _t('GLOBAL_ERROR_ACCESS_RESTRICTED', $login_url, $register_url));
        $tpl->ParseBlock('AccessLimiter');
        return $tpl->Get();
    }

}