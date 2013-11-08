<?php
/**
 * Blocks - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Blocks
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_Hooks_Menu extends Jaws_Gadget_Hook
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
        $urls = array();
        //Blocks model
        $model  = $this->gadget->model->load('Block');
        $blocks = $model->GetBlocks(true);
        if (!Jaws_Error::IsError($blocks)) {
            $max_size = 20;
            foreach ($blocks as $block) {
                $url = $GLOBALS['app']->Map->GetURLFor('Blocks', 'Block', array('id' => $block['id']));
                $urls[] = array('url'   => $url,
                                'title' => ($GLOBALS['app']->UTF8->strlen($block['title']) > $max_size)?
                                            $GLOBALS['app']->UTF8->substr($block['title'], 0, $max_size) . '...' :
                                            $block['title']);
            }
        }

        return $urls;
    }
}
