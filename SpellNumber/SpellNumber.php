<?php
/**
 * Plugin that returns the terslated number to string
 *
 * @category   Plugin
 * @package    SpellNumber
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Plugin that replaces all the [number] tags with translated number to string
 *
 * @see Jaws_Plugin
 */
require_once JAWS_PATH . 'include/Jaws/Plugin.php';

class SpellNumber extends Jaws_Plugin
{
    /**
     * Main Constructor
     *
     * @access  public
     */
    function SpellNumber()
    {
        $this->_Name = 'SpellNumber';
        $this->_Description = _t('PLUGINS_SPELLNUMBER_DESCRIPTION');
        $this->_Example = '[number]1386[/number]';
        $this->_IsFriendly = true;
        $this->_Version = '0.1';
    }

    /**
     * Overrides, Get the WebControl of this plugin
     *
     * @access  public
     * @return  object The HTML WebControl
     */
    function GetWebControl($textarea)
    {
        $button =& Piwi::CreateWidget('Button', 'addspellnumber', '',
                        $GLOBALS['app']->getSiteURL('/plugins/SpellNumber/images/stock-spell_number.png', true));
        $button->SetTitle(_t('PLUGINS_SPELLNUMBER_ADD').' ALT+N');
        $button->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[number]','[/number]','');");
        $button->SetAccessKey('N');

        return $button;
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html Html to Parse
     * @return  string
     */
    function ParseText($html)
    {
        $howMany = preg_match_all('#\[number\](.*?)\[/number\]#si', $html, $matches);
        for ($i = 0; $i < $howMany; $i++) {
            $match_text = $matches[1][$i];
            //How many?
            $new_text = $this->NumberToText($match_text);
            $pattern = '#\[number\]'.$match_text.'\[/number\]#si';
            $html = preg_replace($pattern, $new_text, $html);
        }

    return $html;
    }

    /**
     * NumberToText
     *
     * @access  public
     * @param   string  $num number to parse
     * @return  string
     */
    function NumberToText($num)
    {
        $ret_str = '';
        while ($num !='' )
        {
            $part = (int)((strlen($num)-1)/3);
            $sub_len = strlen($num) - $part*3;
            if ($sub_len  == 0) $sub_len = 3;
            $sub_num = substr($num, 0, $sub_len);
            $num = substr($num, $sub_len);

            $ret_sub_str = '';
            while ($sub_num != '')
            {
                while (($sub_num != '') && ($sub_num[0] == '0'))
                {
                    if (strlen($sub_num) == 1) {
                        $sub_num = '';
                    } else {
                        $sub_num = substr($sub_num, 1);
                    }
                }
                $sub_len = strlen($sub_num);
                switch ($sub_len) {
                case 3:
                    $ret_sub_str = $this->DigitName($sub_num[0], 2);
                    $sub_num = substr($sub_num, 1);
                    break;
                case 2:
                    if ($sub_num[0] == '1') {
                        $ret_sub_str = $ret_sub_str.
                                       (($ret_sub_str=='')?'':(_t('PLUGINS_SPELLNUMBER_SEPARATOR').' ')).
                                       $this->TwoDigitName($sub_num);
                        $sub_num = '';
                    } else  {
                        $ret_sub_str = $ret_sub_str.
                                       (($ret_sub_str=='')?'':(_t('PLUGINS_SPELLNUMBER_SEPARATOR').' ')).
                                       $this->DigitName($sub_num[0], 1);
                        $sub_num = substr($sub_num, 1);
                    }
                    break;
                case 1:
                    $ret_sub_str = $ret_sub_str.
                                   (($ret_sub_str=='')?'':(_t('PLUGINS_SPELLNUMBER_SEPARATOR').' ')).
                                   $this->DigitName($sub_num, 0);
                    $sub_num = '';
                    break;
                default:
                    $sub_num = '';
                }
            }
            $ret_str = $ret_str.
                       ((($ret_sub_str!='') && ($ret_str != ''))?(_t('PLUGINS_SPELLNUMBER_SEPARATOR').' ') : '').
                       $ret_sub_str.
                       (($ret_sub_str=='') ? '' : (' ' . $this->GroupName($part)));
        }
        if ($ret_str=='') {
            $ret_str = _t('PLUGINS_SPELLNUMBER_0');
        }

        return $ret_str;
    }

    /**
     * @access  public
     * @param   int
     * @return  string
     */
    function GroupName($g_num)
    {
        if ($g_num == 0) {
            return '';
        }
        
        $g_str = '';
        while ($g_num > 0) {
            $g_str = $g_str . '000';
            $g_num = $g_num - 1;
        }
        $g_str = 'PLUGINS_SPELLNUMBER_1' . $g_str;

        return _t($g_str);
    }

    /**
     * @access  public
     * @param   int $digit
     * @param   int $order
     * @return  string
     */
    function DigitName($digit, $order)
    {
        $d_str = 'PLUGINS_SPELLNUMBER_' . $digit;
        while ($order > 0) {
            $d_str = $d_str . '0';
            $order = $order - 1;
        }

    return _t($d_str);
    }

    /**
     * @access  public
     * @param   int
     * @return  string
     */
    function TwoDigitName($digits)
    {
        $td_str = 'PLUGINS_SPELLNUMBER_' . $digits;
        return _t($td_str);
    }
}
?>
