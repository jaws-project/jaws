<?php
/**
 * Replaces [block=#n] with a proper content of specified block id
 *
 * @category   Plugin
 * @package    BlockImport
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BlockImport extends Jaws_Plugin
{
    /**
     * Main Constructor
     *
     * @access  public
     * @return  void
     */
    function BlockImport()
    {
        $this->_Name = 'BlockImport';
        $this->_Description = _t('PLUGINS_BLOCKIMPORT_DESCRIPTION');
        $this->_Example = "[Block=#1]";
        $this->_IsFriendly = false;
        $this->_Version = '0.1';
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
     */
    function ParseText($html)
    {
        $blockPattern = '@\[block=#(.*?)\]@ism';
        $new_html = preg_replace_callback($blockPattern, array(&$this, 'Prepare'), $html);
        return $new_html;
    }

    /**
     * The preg_replace call back function
     *
     * @access  private
     * @param   string  $data   Matched strings from preg_replace_callback
     * @return  string  Block content or blank text
     */
    function Prepare($data)
    {
        $blockID = isset($data[1])? $data[1] : '';
        if (Jaws_Gadget::IsGadgetInstalled('Blocks') && !empty($blockID)) {
            $layoutBlocks = $GLOBALS['app']->loadGadget('Blocks', 'LayoutHTML');
            $result = $layoutBlocks->Display($blockID);
            if (!Jaws_Error::isError($result)) {
                return $result;
            }
        }

        return '';
    }

}