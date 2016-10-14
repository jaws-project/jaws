<?php
/**
 * Blocks - Search gadget hook
 *
 * @category   GadgetHook
 * @package    Blocks
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets the gadget's search fields
     *
     * @access  public
     * @return  array   array of search fields
     */
    function GetOptions() {
        return array(
            'blocks' => array('title', 'contents'),
        );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $table  Table name
     * @param   object  $objORM Jaws_ORM instance object
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($table, &$objORM)
    {
        $objORM->table('blocks');
        $objORM->select('id', 'title', 'contents', 'updatetime');
        $objORM->loadWhere('search.terms');
        $result = $objORM->orderBy('createtime desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $date = Jaws_Date::getInstance();
        $blocks = array();
        foreach ($result as $r) {
            $block = array();
            $block['title']   = $r['title'];
            $block['url']     = $this->gadget->urlMap('Block', array('id' => $r['id']));
            $block['image']   = 'gadgets/Blocks/Resources/images/logo.png';
            $block['snippet'] = $r['contents'];
            $block['date']    = $date->ToISO($r['updatetime']);
            $blocks[] = $block;
        }

        return $blocks;
    }

}