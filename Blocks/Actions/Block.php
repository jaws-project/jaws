<?php
/**
 * Blocks Gadget
 *
 * @category   Gadget
 * @package    Blocks
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_Actions_Block extends Jaws_Gadget_HTML
{
    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function BlockLayoutParams()
    {
        $result = array();
        $bModel = $GLOBALS['app']->LoadGadget('Blocks', 'Model');
        $blocks = $bModel->GetBlocks(true);
        if (!Jaws_Error::isError($blocks)) {
            $pblocks = array();
            foreach ($blocks as $block) {
                $pblocks[$block['id']] = $block['title'];
            }

            $result[] = array(
                'title' => _t('BLOCKS_SHOW_BLOCK'),
                'value' => $pblocks
            );
        }

        return $result;
    }

    /**
     * Display a Block
     *
     * @access  public
     * @param   int     Block ID
     * @return  string  XHTML Template content
     */
    function Block($id)
    {
        $tpl = new Jaws_Template('gadgets/Blocks/templates/');
        $tpl->Load('Blocks.html');
        $model = $GLOBALS['app']->LoadGadget('Blocks', 'Model');
        $block = $model->GetBlock($id);
        if (!Jaws_Error::IsError($block)) {
            $tpl->SetBlock('blocks');
            $tpl->SetVariable('id', $block['id']);
            $contents = $this->gadget->ParseText($block['contents'], 'Blocks');
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