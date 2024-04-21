<?php
/**
 * Blocks Gadget
 *
 * @category   Gadget
 * @package    Blocks
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2024 Jaws Development Group
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
                'title' => $this::t('BLOCK'),
                'value' => $pblocks
            );

            $result[] = array(
                'title' => $this::t('DISPLAY_TYPE'),
                'value' => array(
                    0 => $this::t('DISPLAY_TYPE_0'),
                    1 => $this::t('DISPLAY_TYPE_1'),
                ),
            );
        }

        return $result;
    }

    /**
     * Display a Block
     *
     * @access  public
     * @param   int $id             Block ID
     * @param   int $displayType
     * @return  string  XHTML Template content
     */
    function Block($id = 0, $displayType = 0)
    {
        if (empty($id)) {
            $id = $this->gadget->request->fetch('id', 'get');
        }

        $assigns = array();
        $block = $this->gadget->model->load('Block')->GetBlock($id);
        if (!Jaws_Error::IsError($block) && !empty($block)) {
            $assigns['block'] = $block;
        }

        if ($this->app->requestedActionMode === 'normal') {
            $tFilename = 'Blocks.html';
        } else {
            $tFilename = 'Blocks' . (int)$displayType . '.html';
        }
        return $this->gadget->template->xLoad($tFilename)->render($assigns);
    }

}