<?php
/**
 * Plugin that returns a direct link to a term in the glossary
 *
 * @category   Plugin
 * @package    GlossaGlossy
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Plugin that replaces all the [term] tags with term links
 *
 * @see Plugin
 */
require_once JAWS_PATH . 'include/Jaws/Plugin.php';

class GlossaGlossy extends Jaws_Plugin
{

    /**
     * Main Constructor
     *
     * @access	public
     */
    function GlossaGlossy()
    {
        $this->_Name = 'GlossaGlossy';
        $this->_Description = _t('PLUGINS_GLOSSAGLOSSY_DESCRIPTION');
        $this->_Example = '[term]jaws[/term]';
        $this->_IsFriendly = true;
        $this->_Version = '0.3';
    }

    /**
     * Overrides, Get the WebControl of this plugin
     *
     * @access	public
     * @return  string The HTML WebControl
     */
    function GetWebControl($textarea)
    {
        $path = JAWS_PATH . 'gadgets/Glossary/Model.php';
        if (file_exists($path) && Jaws_Gadget::IsGadgetInstalled('Glossary')) {
            require_once $path;

            $controlbox =& Piwi::CreateWidget('HBox');
            $controlbox->SetSpacing(0);

            $glossarybutton =& Piwi::CreateWidget('Button', 'glossarybutton', '',
                                $GLOBALS['app']->getSiteURL('/plugins/GlossaGlossy/images/stock-glossary.png', true));
            $glossarybutton->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[term]','[/term]','".
                                      _t('PLUGINS_GLOSSAGLOSSY_YOURTERM')."');");
            $glossarybutton->SetTitle(_t('PLUGINS_GLOSSAGLOSSY_ADD').' ALT+G');
            $glossarybutton->SetAccessKey('G');

            $controlbox->PackStart($glossarybutton);

            return $controlbox;
        }

        return '';
    }

    /**
     * A simple parser to determine needs a complex one
     *
     * @access  public
     * @param   string  $html   HTML to parse
     * @return  string
     */
    function NeedParsing($html)
    {
        if (stripos($html, '[term]') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Overrides, Parses the text
     *
     * @access	public
     * @param   string  $html Html to Parse
     * @return  string
     */
    function ParseText($html)
    {
        if (!$this->NeedParsing($html)) {
            return $html;
        }

        $path = JAWS_PATH . 'gadgets/Glossary/Model.php';
        if (file_exists($path) && Jaws_Gadget::IsGadgetInstalled('Glossary')) {
            require_once $path;

            $howMany = preg_match_all('#\[term\](.*?)\[/term\]#si', $html, $matches);

            for ($i = 0; $i < $howMany; $i++) {
                $match_text = $matches[1][$i];
                //How many?
                if ($term = GlossaryModel::GetTermByTerm(strip_tags($match_text))) {
                    $new_text = "<acronym title=\"".str_replace(array('[term]', '[/term]'),
                                                                '', 
                                                                strip_tags($term['description'])). "\">$match_text</acronym>";
                    $url = $GLOBALS['app']->Map->GetURLFor('Glossary',
                                                           'ViewTerm',
                                                           array('term' => empty($term['fast_url'])? $term['id'] : $term['fast_url']));
                    $new_text = "<a href=\"{$url}\">$new_text</a>";
                } else {
                    $new_text = $match_text;
                }
                $html = str_replace('[term]'.$match_text.'[/term]', $new_text, $html);
            }
        } else {
            //FIXME: Simon says we need a regexp
            $html = str_replace(array('[term]', '[/term]'), '', $html);
        }

        return $html;
    }

}