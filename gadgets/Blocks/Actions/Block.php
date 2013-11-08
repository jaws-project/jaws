<?php
/**
 * Blocks Gadget
 *
 * @category   Gadget
 * @package    Blocks
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_Actions_Block extends Jaws_Gadget_Action
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
        $bModel = $this->gadget->model->load('Block');
        $blocks = $bModel->GetBlocks(true);
        if (!Jaws_Error::isError($blocks)) {
            $pblocks = array();
            foreach ($blocks as $block) {
                $pblocks[$block['id']] = $block['title'];
            }

            $result[] = array(
                'title' => _t('BLOCKS_BLOCK'),
                'value' => $pblocks
            );
        }

        return $result;
    }

    /**
     * Display a Block
     *
     * @access  public
     * @param   int     $id Block ID
     * @return  string  XHTML Template content
     */
    function Block($id = 0)
    {
        if (empty($id)) {
            $id = jaws()->request->fetch('id', 'get');
        }

        $tpl = $this->gadget->template->load('Blocks.html');
        if (!empty($id)) {
            $model = $this->gadget->model->load('Block');
            $block = $model->GetBlock($id);
            if (!Jaws_Error::IsError($block)) {
                $tpl->SetBlock('blocks');
                $tpl->SetVariable('id', $block['id']);
                $contents = $this->gadget->ParseText($block['contents']);
                $tpl->SetVariable('contents', $contents);
                if ($block['display_title']) {
                    $tpl->SetBlock('blocks/block_title');
                    $tpl->SetVariable('title', $block['title']);
                    $tpl->ParseBlock('blocks/block_title');
                }
                $tpl->ParseBlock('blocks');
            }
        }

        return $tpl->Get();
    }

}