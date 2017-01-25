<?php
/**
 * Replaces [block=#n] with a proper content of specified block id
 *
 * @category   Plugin
 * @package    BlockImport
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class BlockImport_Plugin extends Jaws_Plugin
{
    var $friendly = false;
    var $version = '0.1';

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html       HTML to be parsed
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Parsed content
     */
    function ParseText($html, $reference = 0, $action = '', $gadget = '')
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
            $objBlocks = Jaws_Gadget::getInstance('Blocks')->action->load('Block');
            $result = $objBlocks->Block($blockID);
            if (!Jaws_Error::isError($result)) {
                return $result;
            }
        }

        return '';
    }

}