<?php
/**
 * StaticPage - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    StaticPage
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Hooks_Menu extends Jaws_Gadget_Hook
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
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('StaticPage', 'Page'),
                        'title' => _t('STATICPAGE_NAME'));
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('StaticPage', 'GroupsList'),
                        'title' => _t('STATICPAGE_GROUPS_LIST'));
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('StaticPage', 'PagesTree'),
                        'title' => _t('STATICPAGE_PAGES_TREE'));

        //Load model
        $max_size = 32;
        $pModel  = $this->gadget->model->load('Page');
        $gModel  = $this->gadget->model->load('Group');
        $groups = $gModel->GetGroups(true);
        foreach($groups as $group) {
            $url   = $GLOBALS['app']->Map->GetURLFor(
                                            'StaticPage',
                                            'GroupPages',
                                            array('gid' => empty($group['fast_url'])?
                                                                 $group['id'] : $group['fast_url']));
            $urls[] = array('url'    => $url,
                            'title'  => '\\'. $group['title'],
                            'title2' => ($GLOBALS['app']->UTF8->strlen($group['title']) >= $max_size)?
                                         $GLOBALS['app']->UTF8->substr($group['title'], 0, $max_size).'...' :
                                         $group['title']);
            $pages = $pModel->GetPages($group['id']);
            foreach($pages as $page) {
                if ($page['published'] === true) {
                    $url   = $GLOBALS['app']->Map->GetURLFor(
                                                    'StaticPage',
                                                    'Pages',
                                                    array('gid' => empty($group['fast_url'])?
                                                                         $group['id'] : $group['fast_url'],
                                                          'pid' => empty($page['fast_url'])?
                                                                         $page['base_id'] : $page['fast_url']));
                    $urls[] = array('url'    => $url,
                                    'title'  => '\\'. $group['title'].'\\'. $page['title'],
                                    'title2' => ($GLOBALS['app']->UTF8->strlen($page['title']) >= $max_size)?
                                                 $GLOBALS['app']->UTF8->substr($page['title'], 0, $max_size).'...' :
                                                 $page['title']);
                }
            }
        }

        return $urls;
    }

}