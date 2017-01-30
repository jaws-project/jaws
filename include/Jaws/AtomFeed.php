<?php
/**
 * Simple class to create Atom feeds...
 *
 * @category   XML
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
define('SHOW_EMAILS_IN_FEED', false);

/**
 * A Content construct is an element with arbitrary child content
 */
class AtomContentConstruct
{
    var $ElementName, $Content, $Type, $Mode;

    /**
     * Class constructor
     *
     * @param   string $element Element name
     * @param   string $content Contents of the element
     * @param   string $type text | html | xhtml 
     * @access  public
     */
    function __construct($element, $content, $type = 'text')
    {
        $this->ElementName = $element;
        $this->Content     = $content;
        $this->Type        = $type;
    }

    /**
     * Returns the XML representation
     *
     * @access  public
     */
    function GetXML()
    {
        $type = '';
        if ($this->Type) {
            $type = ' type="' . $this->Type . '"';
        }

        $mode = '';
        if ($this->Mode) {
            $mode = ' mode="' . $this->Mode .'"';
        }

        $res = "<{$this->ElementName} {$type} {$mode}>{$this->Content}</{$this->ElementName}>\n";
        return $res;
    }
}

/**
 * A Date construct is an element whose child content is a W3C Date-Time string
 */
class AtomDateConstruct
{
    var $ElementName, $Date;

    /**
     * Class constructor
     *
     * @param   string $element Element name
     * @param   string $date Date in a valid w3c format
     * @access  public
     */
    function __construct($element, $date)
    {
        $this->ElementName = $element;
        //TODO: Verfy if is a valid date
        $this->Date = $date;
        return true;
    }

    /**
     * Convert date to RFC2822
     *
     * @access  public
     */
    function GetRFC2822Date()
    {
        $year  = substr($this->Date, 0,  4);
        $month = substr($this->Date, 5,  2);
        $day   = substr($this->Date, 8,  2);
        $hour  = substr($this->Date, 11, 2);
        $min   = substr($this->Date, 14, 2);
        $sec   = substr($this->Date, 17, 2);
        return date('r', mktime($hour, $min, $sec, $month, $day, $year));
    }

    /**
     * Returns the XML representation
     *
     * @access  public
     */
    function GetXML()
    {
        $res = "<{$this->ElementName}>{$this->Date}</{$this->ElementName}>\n";
        return $res;
    }
}

/**
 * A Link construct is an element that MUST NOT have any child content
 */
class AtomLinkConstruct
{
    var $Rel, $Type, $HRef, $Title;

    /**
     * Class constructor
     *
     * @param   string $rel Indicates the type of relationship that the link represents
     * @param   string $type MIME Type
     * @param   string $href Contains the link's URI
     * @param   string $title Conveys human-readable information about the link
     * @access  public
     */
    function __construct($rel, $type, $href, $title = null)
    {
        $this->Rel   = $rel;
        $this->Type  = $type;
        $this->HRef  = $href;
        $this->Title = $title;
    }

    /**
     * Returns the XML representation
     *
     * @access  public
     */
    function GetXML()
    {
        if (!empty($this->Title)) {
            $title = strip_tags($this->Title);
            $title = ' title="' . $title . '" ';
        } else {
            $title = '';
        }

        $type = '';
        if (!empty($this->Type)) {
            $type = ' type="' . $this->Type . '" ';
        }

        $res = '<link rel="' . $this->Rel . '" ' . $type . ' href="' . $this->HRef . '"' . $title . "/>\n";
        return $res;
    }
}

/**
 * A Person construct is an element that represent a person information(Name, URL, Email)
 */
class AtomPersonConstruct
{
    var $ElementName, $Name, $URL, $Email;

    /**
     * Class constructor
     *
     * @param   string $element Element name
     * @param   string $name Human readable person name
     * @param   string $url URL associated with the person
     * @param   string $email E-mail address associated with the person
     * @access  public
     */
    function __construct($element, $name, $url = null, $email = null)
    {
        $this->ElementName = $element;
        $this->Name  = $name;
        $this->URL   = $url;
        $this->Email = $email;
    }

    /**
     * Returns the XML representation
     *
     * @access  public
     */
    function GetXML()
    {
        $res =  "\t<{$this->ElementName}>\n";
        $res .= "\t\t<name>{$this->Name}</name>\n";
        if ($this->URL) {
            $res .= "\t\t<uri>{$this->URL}</uri>\n";
        }
        if (($this->Email) && (SHOW_EMAILS_IN_FEED)){
            $res .= "\t\t<email>{$this->Email}</email>\n";
        }
        $res .= "\t</{$this->ElementName}>\n";

        return $res;
    }

}

/**
 * An Enclosure construct is an element that represent a media object
 */
class AtomEnclosureConstruct
{
    var $ElementName, $URL, $Size, $Mime;
   
    /**
     * Constructs the enclosure
     *
     * @access  public
     * @param   string   $element  Element (tag) name
     * @param   string   $url      Media URL
     * @param   string   $size     Media size (bytes)
     * @param   string   $mime     Media mime type
     */
    function __construct($element, $url, $size, $mime)
    {
        $this->ElementName = $element;
        $this->URL         = $url;
        $this->Size        = $size;
        $this->Mime        = $mime;
    }
    
    /**
     * Returns the XML representation
     *
     * @access  public
     * @return  string  XML representation
     */
    function getXML()
    {
        return "\t<{$this->ElementName} url=\"{$this->URL}\" type=\"{$this->Mime}\" length=\"{$this->Size}\"/>\n";
    }
}

class AtomCategoryConstruct
{
    var $ElementName, $Term, $Label, $Scheme;

    function __construct($element, $term, $label, $scheme)
    {
        $this->ElementName = $element;
        $this->Term = $term;
        $this->Label = $label;
        $this->Scheme = $scheme;
    }

    function getXML()
    {
        return "\t<{$this->ElementName} scheme=\"{$this->Scheme}\" term=\"{$this->Term}\" label=\"{$this->Label}\"/>\n";
    }
}

/**
 * Represents an individual entry that is contained by the feed
 *
 * @access  public
 */
class AtomEntry
{
    var $Title, $TitleNoCData, $Link, $Author, $Contributors, $Id, $Modified, $Issued, $Created, $Summary, $Content, $Categories,
        $Enclosures;

    /**
     * Set the author info for this entry
     *
     * @param   string $name Author name
     * @param   string $url Author URL
     * @param   string $email Author e-mail
     * @access  public
     */
    function SetAuthor($name, $url = null, $email = null)
    {
        $this->Author = new AtomPersonConstruct('author', $name, $url,$email);
    }

    /**
     * Adds a contributor to this entry
     *
     * @param   string $name contributor name
     * @param   string $url contributor URL
     * @param   string $email contributor e-mail
     * @access  public
     */
    function AddContributor($name, $url = null, $email = null)
    {
        $this->Contributors[] = new AtomPersonConstruct('contributor', $name,$url, $email);
    }

    /**
     * Sets the entry title
     *
     * @param   string $title Title for the entry
     * @access  public
     */
    function SetTitle($title)
    {
        $this->Title = new AtomContentConstruct('title', $this->ToCDATA($title));
        $this->TitleNoCData = $title;
    }

    /**
     * Set entry link
     *
     * @param   string  $url URI associated with the entry
     * @access  public
     */
    function SetLink($url)
    {
        $this->Link = new AtomLinkConstruct('alternate', 'text/html', $url, $this->TitleNoCData);
    }

    /**
     * Set entry ID
     *
     * @param   string $id A valid and unique URI
     * @access  public
     */
    function SetId($id)
    {
        //TODO: Verify if $id is an URI
        $this->Id = $id;
        return true;
    }
    
    /**
     * Add an enclosure entry
     *
     * @access  public
     * @param   string  $url  URL it refers
     * @param  int     $size Object size (in bytes)
     * @param   string  $mime Mime type
     */
    function AddEnclosure($url, $size, $mime)
    {
        $this->Enclosure[] = new AtomEnclosureConstruct('enclosure', $url, $size, $mime);
    }

    /**
     * Set modified date
     *
     * @param   string $date A valid date
     * @access  public
     */
    function SetUpdated($date)
    {
        return $this->Modified = new AtomDateConstruct('updated', $date);
    }

    /**
     * Set created date
     *
     * @param   string $date A valid date
     * @access  public
     */
    function SetPublished($date)
    {
        return $this->Created = new AtomDateConstruct('published', $date);
    }

    /**
     * Returns given html enclosed in a CDATA tag
     *
     * @param   string $html HTML string
     * @access  public
     */
    function ToCDATA($html)
    {
        $html = str_replace('&nbsp;', '&#160;', $html);
        $html = "<![CDATA[ {$html} ]]>";
        return $html;
    }

    /**
     * Set summary
     *
     * @param   string $summary Short summary, abstract or excerpt of the entry
     * @param   string $type text, html, xhtml
     * @access  public
     */
    function SetSummary($summary, $type = 'text')
    {
        $this->Summary = new AtomContentConstruct('summary', $this->ToCDATA($summary), $type);
    }

    /**
     * Set entry content
     *
     * @param   string $content Content of the entry
     * @param   string $type text, html, xhtml
     * @access  public
     */
    function SetContent($content, $type = 'text')
    {
        $this->Content = new AtomContentConstruct('content', $this->ToCDATA($content), $type);
    }

    /**
     * Add a category to feed
     * @param   string  $category  Category name
     * @access  public
     */
    function AddCategory($term, $label, $schema)
    {
        $this->Categories[] = new AtomCategoryConstruct('category', $term, $label, $schema);
    }

    /**
     * Returns the XML representation
     *
     * @access  public
     */
    function GetXML()
    {
        $res = "<entry>\n";
        $res .= "\t".$this->Title->GetXML();
        $res .= "\t".$this->Link->GetXML();
        $res .= $this->Author->GetXML();
        if ($this->Contributors) {
            foreach($this->Contributors as $contributor) {
                $res .= "\t".$contributor->GetXML();
            }
        }

        if ($this->Enclosures) {
            foreach($this->Enclosures as $enclosure) {
                $res .= "\t".$enclosure->GetXML();
            }
        }

        $res .= "\t<id>".$this->Id."</id>\n";
        $res .= "\t".$this->Modified->GetXML();
        if ($this->Created) {
            $res .= "\t".$this->Created->GetXML();
        }

        if ($this->Summary) {
            $res .= "\t".$this->Summary->GetXML();
        }

        if ($this->Content) {
            $res .= "\t".$this->Content->GetXML();
        }
        if ($this->Categories) {
            foreach ($this->Categories as $c) {
                $res .= "\t" . $c->getXML();
            }
        }
        $res .= "</entry>\n";

        return $res;
    }
}

/**
 * Atom Feed document
 *
 * @access  public
 */
class Jaws_AtomFeed
{
    var $Version, $Lang, $Stylesheet, $StylesheetType;
    var $SiteUrl, $Title, $Link, $Author, $Contributors, $TagLine, $Id, $Generator, $Copyright, $Info, $Modified, $Entries;

    /**
     * Class constructor
     *
     * @param   string $version Version of the Atom specification
     * @param   string $lang Default natural language of the feed, must be a registered language tag(RFC3066)
     * @access  public
     */
    function __construct($version = '1.0', $lang = 'en-us')
    {
        $this->Version = $version;
        $this->Lang    = $lang;
        $this->SiteURL = $GLOBALS['app']->GetSiteURL('/');
    }

    /**
     * Set the CSS
     *
     * @param   string $style Stylesheet URL
     * @param   string $type MIME Type
     * @access  public
     */
    function SetStyle($style, $type = 'text/css')
    {
        $this->Stylesheet     = $style;
        $this->StylesheetType = $type;
    }

    /**
     * Sets the feed title
     *
     * @param   string $title Title for the feed
     * @access  public
     */
    function SetTitle($title)
    {
        $this->Title = new AtomContentConstruct('title', $title);
    }

    /**
     * Set feed link
     *
     * @param   string  $url URI associated with the feed
     * @access  public
     */
    function SetLink($url)
    {
        $this->Link = new AtomLinkConstruct('self', null, $url, $this->Title->Content);
    }

    /**
     * Site URL
     * 
     * @param   string $url  Site URL
     */
    function SetSiteURL($url) {
        $this->SiteURL = $url;
    }

    /**
     * Set the author info
     *
     * @param   string $name Author name
     * @param   string $url Author URL
     * @param   string $email Author e-mail
     * @access  public
     */
    function SetAuthor($name, $url = null, $email = null)
    {
        $this->Author = new AtomPersonConstruct('author', $name, $url, $email);
    }

    /**
     * Adds a contributor to the feed
     *
     * @param   string $name contributor name
     * @param   string $url contributor URL
     * @param   string $email contributor e-mail
     * @access  public
     */
    function AddContributor($name, $url = null, $email = null)
    {
        $this->Contributors[] = new AtomPersonConstruct('contributor', $name, $url, $email);
    }


    /**
     * Set the tagline
     *
     * @param   string $tagline Description or tagline of the feed
     * @access  public
     */
    function SetTagLine($tagline)
    {
        $this->TagLine = new AtomContentConstruct('subtitle', $tagline);
    }

    /**
     * Set Feed ID
     *
     * @param   string $id A valid and unique IRI
     * @access  public
     */
    function SetId($id)
    {
        //TODO: Verify if $id is an IRI
        $this->Id = $id;
        return true;
    }

    /**
     * Set feed generator
     *
     * @param   string $generator Software agent used to create the feed
     * @access  public
     */
    function SetGenerator($generator)
    {
        $this->Generator = $generator;
    }

    /**
     * Set a copyright for the feed
     *
     * @param   string $copyright Human-readable copyright statement for the feed
     * @access  public
     */
    function SetCopyright($copyright)
    {
        $this->Copyright = new AtomContentConstruct('rights', $copyright);
    }

    /**
     * Set additional info for the feed
     *
     * @param   string $info Explanation of the feed format itself
     * @access  public
     */
    function SetInfo($info)
    {
        $this->Info = new AtomContentConstruct('info', $info);
    }

    /**
     * Set modified date
     *
     * @param   string $date A valid date
     * @access  public
     */
    function SetUpdated($date)
    {
        return $this->Modified = new AtomDateConstruct('updated', $date);
    }

    /**
     * Adds an entry to the feed
     *
     * @param   AtomEntry $entry Entry
     * @access  public
     */
    function AddEntry($entry)
    {
        if (strtoupper(get_class($entry)) == strtoupper('AtomEntry')) {
            $this->Entries[] = $entry;
            return true;
        }

        return false;
    }


    /**
     * Returns the XML representation
     *
     * @access  public
     */
    function GetXML()
    {
        $xmlheader = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

        $res = $xmlheader;

        if ($this->Lang) {
            $lang = "xml:lang=\"{$this->Lang}\"";
        }

        if ($this->Stylesheet) {
            $res .= "<?xml-stylesheet type=\"{$this->StylesheetType}\" href=\"{$this->Stylesheet}\" ?>";
        }

        $res .= "<feed xmlns=\"http://www.w3.org/2005/Atom\">\n";
        $res .= $this->Link->GetXML();
        $res .= $this->Title->GetXML();
        $res .= $this->Author->GetXML();
        if ($this->Contributors) {
            foreach($this->Contributors as $contributor) {
                $res .= $contributor->GetXML();
            }
        }

        if ($this->TagLine) {
            $res .= $this->TagLine->GetXML();
        }

        if ($this->Id) {
            $res .= "<id>{$this->Id}</id>\n";
        }

        if ($this->Generator) {
            $res .= "<generator>{$this->Generator}</generator>\n";
        }

        if ($this->Copyright) {
            $res .= $this->Copyright->GetXML();
        }

        if ($this->Info) {
            $res .= $this->Info->GetXML();
        }

        $res .= $this->Modified->GetXML();
        if ($this->Entries) {
            foreach ($this->Entries as $entry) {
                $res .= $entry->GetXML();
            }
        }

        $res .= "</feed>";

        return $res;
    }

    /**
     * Print the XML representation
     *
     * @access  public
     */
    function Show()
    {
        return $this->GetXML();
    }

    /**
     * Returns the feed in a valid RSS2 representation
     *
     * @access  public
     */
    function ToRSS2()
    {
        $res  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $res .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
        $res .= "<channel>\n";
        $res .= "\t<title>{$this->Title->Content}</title>\n";
        $res .= "\t<description>{$this->TagLine->Content}</description>\n";
        $res .= "\t<link>{$this->SiteURL}</link>\n";
        $res .= "\t<managingEditor>{$this->Author->Email} ({$this->Author->Name})</managingEditor>\n";
        $res .= "\t<copyright>{$this->Copyright->Content}</copyright>\n";
        $res .= "\t<pubDate>".$this->Modified->GetRFC2822Date()."</pubDate>\n";
        $res .= "\t<generator>{$this->Generator}</generator>\n";
        $res .= "\t<atom:link href=\"{$this->Link->HRef}\" rel=\"self\" type=\"application/rss+xml\" />\n";
        if (count($this->Entries) > 0) {
            foreach ($this->Entries as $entry) {
                $res .= "\t<item>\n";
                if (isset($entry->Categories)) {
                    foreach ($entry->Categories as $c) {
                        $res .= "\t\t<category>" . $c->Label . "</category>\n";
                    }
                }
                //Enclosures
                if (isset($entry->Enclosure)) {
                    foreach ($entry->Enclosure as $enc) {
                        $res .= "\t" . $enc->getXML();
                    }
                }
                $res .= "\t\t<title>{$entry->Title->Content}</title>\n";
                $res .= "\t\t<description>{$entry->Summary->Content}</description>\n";
                $res .= "\t\t<link>{$entry->Link->HRef}</link>\n";
                $res .= "\t\t<author>{$entry->Author->Email} ({$entry->Author->Name})</author>\n";
                $res .= "\t\t<guid>{$entry->Link->HRef}</guid>\n";
                $res .= "\t\t<pubDate>".$entry->Created->GetRFC2822Date()."</pubDate>\n";
                $res .= "\t</item>\n";
            }
        }
        $res .= "</channel>\n";
        $res .= "</rss>\n";

        return $res;
    }

    /**
     * Returns the feed in a valid OPML representation
     *
     * @access  public
     */
    function ToOPML()
    {
        $res  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $res .= "<opml>\n";
        $res .= "<head>\n";
        $res .= "\t<title>{$this->Title->Content}</title>\n";
        $res .= "\t<dateCreated>{$this->Modified->Date}</dateCreated>\n";
        $res .= "\t<dateModified>{$this->Modified->Date}</dateModified>\n";
        $res .= "\t<ownerName>{$this->Author->Name}</ownerName>\n";
        $res .= "\t<ownerEmail>{$this->Author->Email}</ownerEmail>\n";
        $res .= "</head>\n";
        $res .= "<body>\n";
        foreach ($this->Entries as $entry) {
            $res .= "<outline text=\"{$entry->Title->Content}\" type=\"link\" url=\"{$entry->Link->HRef}\" ".
                "whenLastUpdate=\"{$this->Modified->Date}\" />";
        }
        $res .= "</body>\n";
        $res .= "</opml>\n";

        return $res;
    }

}