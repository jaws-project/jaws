<?php
/**
 * Blocks Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Blocks
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BlocksLayoutHTML
{
    /**
     * Loads layout actions
     *
     * @access  public
     * @return  array   Actions array
     */
    function LoadLayoutActions()
    {
        $model   = $GLOBALS['app']->LoadGadget('Blocks', 'Model');
        $blocks  = $model->GetBlocks(true);
        $actions = array();
        if (!Jaws_Error::isError($blocks)) {
            foreach ($blocks as $b) {
                $actions['Display(' . $b['id'] . ')'] = array(
                    'mode' => 'LayoutAction',
                    'name' => $b['title'],
                    'desc' => _t('BLOCKS_SHOW_BLOCK')
                );
            }
        }

        return $actions;
    }

    /**
     * Show a Block
     *
     * @access  public
     * @param   int     Block ID
     * @return  string  XHTML Template content
     */
    function Display($id)
    {
        $tpl = new Jaws_Template('gadgets/Blocks/templates/');
        $tpl->Load('Blocks.html');
        $model = $GLOBALS['app']->LoadGadget('Blocks', 'Model');
        $block = $model->GetBlock($id);
        if (!Jaws_Error::IsError($block)) {
            $tpl->SetBlock('blocks');
            $tpl->SetVariable('id', $block['id']);
            $contents = Jaws_Gadget::ParseText($block['contents'], 'Blocks');
            $tpl->SetVariable('contents', $contents);
            if ($block['display_title']) {
                $tpl->SetBlock('blocks/block_title');
                $tpl->SetVariable('title', $block['title']);
                $tpl->ParseBlock('blocks/block_title');
            }
            $tpl->ParseBlock('blocks');
        }

        return $tpl->Get();
    }
}