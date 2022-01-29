<?php
/**
 * Phoo - Tags gadget hook
 *
 * @category    GadgetHook
 * @package     Phoo
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Hooks_Tags extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with the results of a tag content
     *
     * @access  public
     * @param   string  $action     Action name
     * @param   array   $references Array of References
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($action, $references)
    {
        if(empty($action) || !is_array($references) || empty($references)) {
            return false;
        }

        $result = Jaws_ORM::getInstance()
            ->table('phoo_image')
            ->select('id:integer', 'filename', 'fast_url', 'title', 'description', 'updatetime')
            ->where('id', $references, 'in')
            ->and()
            ->where('published', true)
            ->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $photos = array();
        foreach ($result as $r) {
            $photo = array();
            $photo['title']   = $r['title'];
            $photo['url']     = $this->gadget->urlMap('Photo', array('photo' => $r['id']));
            $photo['outer']   = false;
            $photo['image']   = $this->app->getDataURL(
                'phoo/' . $this->gadget->model->load('Common')->GetThumbPath($r['filename'])
            );
            $photo['snippet'] = $r['description'];
            $photo['date']    = $date->ToISO($r['updatetime']);
            $photos[$r['id']] = $photo;
        }

        return $photos;
    }

}