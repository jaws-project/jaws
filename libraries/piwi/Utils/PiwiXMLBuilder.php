<?php
/*
 * PiwiXMLBuilder.php - Class that will help building the piwiXML for each gadget
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
class PiwiXMLBuilder
{
    /*
     * XML data
     *
     * @var      string  $_Data
     * @see      OpenElement (), AddAttribute (), CloseElement ()
     * @access   private
     */
    var $_Data;

    /*
     * Flag that determinates if there are child elements
     *
     * @var      boolean  $_HowManyChilds
     * @access   private
     */
    var $_HowManyChilds;

    /*
     * Last opened tag
     *
     * @var      string   $_LastTag
     * @access   private
     */
    var $_LastTag;

    /*
     * Last closed tag
     *
     * @var      string   $_LastClosedTag
     * @access   private
     */
    var $_LastClosedTag;


    /*
     * Save the space for indentation
     *
     * @var      string  $_Indentation
     * @access   private
     */
    var $_Indentation;

    /*
     * Element has attributes
     *
     * @var      string  $_HasAttributes
     * @access   private
     */
    var $_HasAttributes = false;

    /*
     * Element has text
     *
     * @var      string  $_HasText
     * @access   private
     */
    var $_HasText = false;


    /*
     * Public constructor
     *
     * @access   public
     */
    function __construct()
    {
        $this->_Data = '';
        $this->_Indentation = 0;
    }


    /*
     * Open a new element (tag)
     *
     * @param     string  $element Element to open
     * @param     boolean $short_tag Flag that determinates if the tag should end with '/>'.
     * @param     boolean $is_root Flag that determinates if the element is the root
     * @access    public
     */
    function OpenElement($element, $short_tag = false, $is_root = false)
    {
        $this->_HasAttributes = false;
        $this->_LastTag = $element;
        $this->_HasText = false;

        if (!$short_tag) {
            $this->_Data.= str_repeat('|', $this->_Indentation)."<piwi:".$element.">\n";
        } else {
            $this->_Data.= str_repeat('|', $this->_Indentation)."<piwi:".$element." />\n";
        }

        $this->_Indentation++;

        if ($is_root) {
            $this->addAttribute('xmlns:piwi', 'http://piwi-project.sf.net/xmlschema');
        }
    }


    /*
     * Close an element
     *
     * @param     string  $element Element to close
     * @access    public
     */
    function closeElement($element)
    {
        $this->_LastClosedTag = $element;
        if ($this->_Indentation > 1) {
            $this->_Indentation--;
            if ($this->_LastTag == $element) {
                if (substr($this->_Data, -3) != "/>\n") {
                    $this->_Data = substr($this->_Data, 0, -1);
                    $this->_Data.= "</piwi:".$element.">\n";
                }
            } else {
                $this->_Data.= str_repeat('|', $this->_Indentation)."</piwi:".$element.">\n";
            }
        } else {
            if ($this->_LastTag == $element) {
                if (!$this->_HasText) {
                    $this->_Data = substr($this->_Data, 0, -2);
                    $this->_Data = $this->_Data." />\n";
                } else {
                    $this->_Data = substr($this->_Data, 0, -1);
                    $this->_Data.= "</piwi:".$element.">\n";
                }
            } else {
                $this->_Data.= "</piwi:".$element.">\n";
            }
        }
    }


    /*
     * Add attribute
     *
     * @param     string  $attname   Name of the attribute
     * @param     string  $attvalue  Value of the attribute
     * @access    public
     */
    function addAttribute($attname, $attvalue)
    {
        if (substr($this->_Data, -3) == "/>\n") {
            $this->_Data = substr($this->_Data, 0, -3);
            $this->_Data.= $attname."=\"".$attvalue."\" />\n";
        } else {
            $this->_Data = substr($this->_Data, 0, -2);
            $this->_Data.= " ".$attname."=\"".$attvalue."\">\n";
        }
        $this->_HasAttributes = true;
    }


    /*
     * Add text to the element
     *
     * @param      string  $text Text to add
     * @param      boolean $cdata If text should be added as CDATA
     * @access     public
     */
    function addText($text, $cdata = false)
    {
        $this->_HasText = true;
        $this->_Data = substr($this->_Data, 0, -1);

        if ($cdata) {
            $this->_Data.= "<![CDATA[".$text."]]>";
        } else {
            $this->_Data.= $text;
        }

        $this->_Data.= "\n";
    }


    /*
     * Get the data
     *
     * @param      boolean $withPipes  Replace the pipes?
     * @access     public
     * @return     string  Data string
     */
    function getData($withPipes)
    {
        //Ok, replace all the | for spaces..
        if (!$withPipes) {
            $this->_Data = str_replace('|', '    ', $this->_Data);
        }

        $this->cleanEmptyLines();
        return $this->_Data;
    }


    /*
     * Clean empty lines
     *
     * @access      private
     */
    function cleanEmptyLines()
    {
        $xml = preg_split("/\n/", $this->_Data);
        //Added this declaration to avoid this warning:
        //Undefined variable:  newxml in  ...
        $newxml = "";
        foreach ($xml as $line)
            if (!empty($line) && ($line != '    ')) {
                $newxml.= $line."\n";
            }

        //$this->_Data = preg_replace('/>[\t\n]{2,}?</m', ">\n<", $this->_Data);
        $this->_Data = $newxml;
    }

    /*
     * Add another XML to the data
     *
     * @param       string  $xml The XML to add
     * @access      public
     */
    function addXML($xml)
    {
        //Remove the namespace attribute
        $xml = str_replace(" xmlns:piwi=\"http://piwi-project.sf.net/xmlschema\"", "", $xml);
        //How many pipes we have?
        $pipes = str_repeat('|', $this->_Indentation);
        $newxml = "";
        $xml = preg_split("/\n/", $xml);

        $howMany = count($xml);

        //Last line is an empty line?

        foreach ($xml as $line) {
            if (!empty ($line)) {
                $newxml.= $pipes."".$line."\n";
            }
        }

        //little hack: indent next line :-)
        if ($howMany < 1) {
            $this->_Data.= $newxml.$pipes;
        } else {
            $this->_Data.= $newxml.str_repeat('|', $this->_Indentation-1)."\n";
        }
    }
}
?>
