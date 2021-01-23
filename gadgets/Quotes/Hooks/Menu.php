<?php
/**
 * Quotes - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Quotes
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Execute()
    {
        $urls   = array();
        $urls[] = array('url'   => $this->gadget->urlMap('RecentQuotes'),
                        'title' => $this->gadget->title);

        $model  = $this->gadget->model->load('Groups');
        $groups = $model->GetGroups();
        if (!Jaws_Error::isError($groups)) {
            $max_size = 20;
            foreach ($groups as $group) {
                $url = $this->gadget->urlMap('ViewGroupQuotes', array('id' => $group['id']));
                $urls[] = array('url'   => $url,
                                'title' => (Jaws_UTF8::strlen($group['title']) > $max_size)?
                                            Jaws_UTF8::substr($group['title'], 0, $max_size).'...' :
                                            $group['title']);
            }
        }

        return $urls;
    }
}
