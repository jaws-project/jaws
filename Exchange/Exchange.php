<?php
/**
 * Replace a statement with another.
 *
 * @category   Plugin
 * @package    Exchange
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Plugin that replace a statement with another
 *
 * @see Jaws_Plugin
 */
require_once JAWS_PATH . 'include/Jaws/Plugin.php';

class Exchange extends Jaws_Plugin
{
    /**
     * list of exclude tags
     */
    var $_ExcludeTags = array('style', 'script');

    /**
     * list of exchanges formula
     *
     * @access  private
     */
    var $_ExchangeList = array(array('language'    => 'fa',
                                     'pattern'     => '/([0-9])/e',
                                     'replacement' => "pack('C*', 0xDB, 0xB0 + '\$1')"),
                               array('language'    => 'ar',
                                     'pattern'     => '/([0-9])/e',
                                     'replacement' => "pack('C*', 0xD9, 0xA0 + '\$1')"),
                              );

    /**
     * Main Constructor
     *
     * @access  public
     */
    function Exchange()
    {
        $this->_Name = 'Exchange';
        $this->_Description = _t('PLUGINS_EXCHANGE_DESCRIPTION');
        $this->_Example = 'replace 1357 with ۱۳۵۷ in persian(farsi) language';
        $this->_IsFriendly = false;
        $this->_Version = '0.1.0';
    }

    function Preparing(&$content, $pattern, $replacement)
    {
        $pos = 0;
        while (true) {
            if ($pos >= strlen($content)) {
                break;
            }

            $bgn = strpos($content, '<', $pos);
            $end = strpos($content, '>', $pos);
            if ($bgn === false) {
                $text = substr($content, $pos, strlen($content) - $pos);
                $text = preg_replace($pattern, $replacement, $text);
                $content = substr_replace($content, $text, $pos, strlen($content) - $pos);
                $pos = strlen($content);
                continue;
            }

            if (($end === false) || ($end < $bgn)) {
                break;
            }

            $tagName = trim(substr($content, $bgn+1, $end - $bgn - 1));
            $fKey = array_search($tagName, $this->_ExcludeTags);
            if ($fKey !==false) {
                $tagNameEnd = strpos($content, '</'.$this->_ExcludeTags[$fKey], $bgn);
                if ($tagNameEnd === false) {
                    break;
                }
                $pos = $tagNameEnd;
                continue;
            }

            if ($pos < $bgn) {
                $text = substr($content, $pos, $bgn - $pos);
                $text = preg_replace($pattern, $replacement, $text);
                $content = substr_replace($content, $text, $pos, $bgn - $pos);
                $pos = strlen($text) + $pos;
                continue;
            }

            $pos = $end + 1;
        }

        return $content;
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html Html to Parse
     * @return  string  The parsed html
     */
    function ParseText($text)
    {
        $lang = $GLOBALS['app']->GetLanguage();
        foreach ($this->_ExchangeList as $exchange) {
            if (false !== strpos($exchange['language'], $lang)) {
                $text = $this->Preparing($text, $exchange['pattern'], $exchange['replacement']);
            }
        }
        return $text;
    }
}