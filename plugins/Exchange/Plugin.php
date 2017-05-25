<?php
/**
 * Replaces a statement with another
 *
 * @category   Plugin
 * @package    Exchange
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Exchange_Plugin extends Jaws_Plugin
{
    var $friendly = false;
    var $version = '0.1.0';

    /**
     * List of excluded tags
     *
     * @var     string
     * @access  private
     */
    var $_ExcludeTags = array('style', 'script');

    /**
     * List of exchange formula
     *
     * @var     array
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
     * Performs the conversion
     *
     * @access  private
     * @param   string  $content        Input text
     * @param   string  $pattern        Subject to be replaced
     * @param   string  $replacement    Replacement text
     * @return  string  Converted text
     */
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
     * @param   string  $html       HTML to be parsed
     * @param   int     $reference  Action reference entity
     * @param   string  $action     Gadget action name
     * @param   string  $gadget     Gadget name
     * @return  string  Parsed content
     */
    function ParseText($html, $reference = 0, $action = '', $gadget = '')
    {
        $lang = $GLOBALS['app']->GetLanguage();
        foreach ($this->_ExchangeList as $exchange) {
            if (false !== strpos($exchange['language'], $lang)) {
                $html = $this->Preparing($html, $exchange['pattern'], $exchange['replacement']);
            }
        }
        return $html;
    }

}