<?php
/**
 * Returns a direct link to a term in the glossary
 *
 * @category   Plugin
 * @package    GlossaGlossy
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class GlossaGlossy extends Jaws_Plugin
{

    /**
     * Main Constructor
     *
     * @access	public
     * @return  void
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
     * Overrides, Gets the WebControl of this plugin
     *
     * @access  public
     * @param   string  $textarea   The textarea
     * @return  string  XHTML WebControl
     */
    function GetWebControl($textarea)
    {
        $path = JAWS_PATH . 'gadgets/Glossary/Model.php';
        if (file_exists($path) && Jaws_Gadget::IsGadgetInstalled('Glossary')) {
            require_once $path;
            $glossarybutton =& Piwi::CreateWidget('Button', 'glossarybutton', '',
                                $GLOBALS['app']->getSiteURL('/plugins/GlossaGlossy/images/stock-glossary.png', true));
            $glossarybutton->AddEvent(ON_CLICK, "javascript: insertTags('$textarea', '[term]','[/term]','".
                                      _t('PLUGINS_GLOSSAGLOSSY_YOURTERM')."');");
            $glossarybutton->SetTitle(_t('PLUGINS_GLOSSAGLOSSY_ADD').' ALT+G');
            $glossarybutton->SetAccessKey('G');

            return $glossarybutton;
        }

        return '';
    }

    /**
     * Checks the string to see if parsing is required
     *
     * @access  public
     * @param   string  $html   Input HTML
     * @return  bool    Checking result
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
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
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