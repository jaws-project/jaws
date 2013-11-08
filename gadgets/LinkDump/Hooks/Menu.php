<?php
/**
 * LinkDump - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    LinkDump
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Categories'),
                        'title' => _t('LINKDUMP_NAME'));

        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups();
        if (!Jaws_Error::IsError($groups)) {
            $max_size = 32;
            foreach ($groups as $group) {
                $title = _t('LINKDUMP_LINKS_ARCHIVE'). ' - '. $group['title'];
                $gid = empty($group['fast_url']) ? $group['id'] : $group['fast_url'];
                $url = $GLOBALS['app']->Map->GetURLFor('LinkDump', 'Category', array('id' => $gid));
                $urls[] = array('url'   => $url,
                                'title' => ($GLOBALS['app']->UTF8->strlen($title) > $max_size)?
                                            $GLOBALS['app']->UTF8->substr($title, 0, $max_size - 3) . '...' :
                                            $title);
            }
        }

        return $urls;
    }

}