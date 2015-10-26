<?php
/**
 * LinkDump - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    LinkDump
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
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
        $urls[] = array('url'   => $this->gadget->urlMap('Categories'),
                        'title' => $this->gadget->title);

        $model = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups();
        if (!Jaws_Error::IsError($groups)) {
            $max_size = 32;
            foreach ($groups as $group) {
                $title = _t('LINKDUMP_LINKS_ARCHIVE'). ' - '. $group['title'];
                $gid = empty($group['fast_url']) ? $group['id'] : $group['fast_url'];
                $url = $this->gadget->urlMap('Category', array('id' => $gid));
                $urls[] = array('url'   => $url,
                                'title' => (Jaws_UTF8::strlen($title) > $max_size)?
                                            Jaws_UTF8::substr($title, 0, $max_size - 3) . '...' :
                                            $title);
            }
        }

        return $urls;
    }

}