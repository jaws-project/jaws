<?php
/**
 * Forums - Sitemap hook
 *
 * @category    GadgetHook
 * @package     Forums
 * @author      ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Hooks_Sitemap extends Jaws_Gadget_Hook
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
                'title'  => $this::t('TITLE'),
                'url'    => $this->gadget->urlMap('Forums', array(), array('absolute' => true))
            ),
            'levels' => array(),
            'items'  => array()
        );
        if ($data_type == 0) {
            $gModel = $this->gadget->model->load('Groups');
            $groups = $gModel->GetGroups(true);
            if (Jaws_Error::IsError($groups)) {
                return $groups;
            }

            foreach ($groups as $group) {
                $result['levels'][] = array(
                    'id'     => $group['id'],
                    'title'  => $group['title'],
                );
            }
        } elseif ($data_type == 1 || $data_type == 2) {
            $gModel = $this->gadget->model->load('Groups');
            $groups = $gModel->GetGroups(true);
            if (Jaws_Error::IsError($groups)) {
                return $groups;
            }
            foreach ($groups as $group) {
                $result['levels'][] = array(
                    'id'     => $group['id'],
                    'parent' => $group['id'],
                    'title'  => $group['title'],
                    'lastmod'=> null,
                    'url'    => $this->gadget->urlMap(
                        'Group',
                        array('gid' => $group['id']),
                        array('absolute' => true)
                    ),
                );
            }

            if ($data_type == 2) {
                $pModel = $this->gadget->model->load('Forums');
                $tModel = $this->gadget->model->load('Topics');
                $forums  = $pModel->GetForums(false, true, true);
                if (Jaws_Error::IsError($forums)) {
                    return $forums;
                }
                foreach ($forums as $forum) {
                    $result['items'][] = array(
                        'id'        => $forum['id'],
                        'parent'    => $forum['gid'],
                        'title'     => $forum['title'],
                        'lastmod'   => null,
                        'url'       => $this->gadget->urlMap(
                            'Topics',
                            array('fid' => $forum['id']),
                            array('absolute' => true)
                        ),
                    );

                    // Get all published topics
                    $topics = $tModel->GetTopics($forum['id'], true);
                    foreach ($topics as $topic) {
                        $result['items'][] = array(
                            'id'        => $topic['id'],
                            'parent'    => $forum['gid'],
                            'title'     => $topic['subject'],
                            'lastmod'   => null,
                            'url'       => $this->gadget->urlMap(
                                'Posts',
                                array('fid' => $forum['id'], 'tid' => $topic['id']),
                                array('absolute' => true)
                            ),
                        );
                    }
                }
            }
        }
        return $result;
    }

}