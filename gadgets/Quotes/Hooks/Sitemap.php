<?php
/**
 * Quotes - Sitemap hook
 *
 * @category    GadgetHook
 * @package     Quotes
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Hooks_Sitemap extends Jaws_Gadget_Hook
{
    /**
     * Fetch items can be included in sitemap
     *
     * @access  public
     * @param   int     $data_type      Data type
     * @param   int     $updated_time   Last updated time
     *          (0: first level of categories, 1: all levels of categories, 2: flatted all items)
     * @return  mixed   Array of data otherwise Jaws_Error
     */
    function Execute($data_type = 0, $updated_time = 0)
    {
        $result = array(
            '/' => array(
                'id'     => 0,
                'parent' => 0,
                'title'  => _t('QUOTES_TITLE'),
                'url'    => $this->gadget->urlMap('RecentQuotes', array(), true)
            ),
            'levels' => array(),
            'items'  => array()
        );
        if ($data_type == 0) {
            $gModel = $this->gadget->model->load('Groups');
            $categories = $gModel->GetGroups();
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }

            foreach ($categories as $category) {
                $result['levels'][] = array(
                    'id'     => $category['id'],
                    'title'  => $category['title'],
                );
            }
        } elseif ($data_type == 1 || $data_type == 2) {
            $gModel = $this->gadget->model->load('Groups');
            $categories = $gModel->GetGroups();
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }
            foreach ($categories as $category) {
                $cat = $category['id'];
                $result['levels'][] = array(
                    'id'     => $category['id'],
                    'parent' => $category['id'],
                    'title'  => $category['title'],
                    'lastmod'=> null,
                    'url'    => $this->gadget->urlMap('ViewGroupQuotes', array('id' => $cat), true),
                );
            }

            if ($data_type == 2) {
                $pModel = $this->gadget->model->load('Quotes');
                $pages  = $pModel->GetQuotes();
                if (Jaws_Error::IsError($pages)) {
                    return $pages;
                }
                foreach ($pages as $page) {
                    $entry = empty($page['fast_url']) ? $page['id'] : $page['fast_url'];
                    $result['items'][] = array(
                        'id'      => $page['id'],
                        'parent'  => $page['gid'],
                        'title'   => $page['title'],
                        'lastmod' => $page['updatetime'],
                        'url'     => $this->gadget->urlMap('ViewQuote', array('id' => $entry), true),
                    );
                }
            }
        }
        return $result;
    }

}