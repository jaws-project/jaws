<?php
/**
 * Returns a translated number to string
 *
 * @category   Plugin
 * @package    SpellNumber
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SpellNumber_Plugin extends Jaws_Plugin
{
    var $friendly = true;
    var $version = '0.1';

    /**
     * Overrides, Gets the WebControl of this plugin
     *
     * @access  public
     * @param   string  $textarea   The textarea
     * @return  string  XHTML WebControl
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
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
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
     * Converts number to text
     *
     * @access  public
     * @param   string  $num    The number
     * @return  string  Number string
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
     * Returns group name
     *
     * @access  public
     * @param   int $g_num  The number
     * @return  string  Group name
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
     * Returns digit name
     *
     * @access  public
     * @param   int $digit  Digit
     * @param   int $order  Order
     * @return  string  Digit name
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
     * Returns name of the two digit number
     *
     * @access  public
     * @param   int $digits  Digits
     * @return  string  Number name
     */
    function TwoDigitName($digits)
    {
        $td_str = 'PLUGINS_SPELLNUMBER_' . $digits;
        return _t($td_str);
    }

}