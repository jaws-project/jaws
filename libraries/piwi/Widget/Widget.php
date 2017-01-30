<?php
/**
 * Widget.php - Main Class for all widgets
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
class Widget
{
    /**
     * Gives the widget family name (base, bin, container, misc, none)
     *
     * @var    string $_FamilyWidget;
     * @access private
     */
    var $_familyWidget;

    /**
     * Gives the name of the widget
     *
     * @var    string $_name
     * @access private
     */
    var $_name;

    /**
     * Gives the ID of the widget
     *
     * @var    string $_id
     * @access private
     * @see    setID(), getID()
     */
    var $_id;

    /**
     * Gives the value of the widget (what is visible in the browser)
     *
     * @var    string $_value
     * @access private
     */
    var $_value;

    /**
     * Gives the size of the widget
     *
     * @var    string $_size
     * @access private
     * @see    setSize()
     */
    var $_size;

    /**
     * Gives the visible status of the widget
     *
     * @var    string $_visible
     * @access private
     * @see    setVisible()
     */
    var $_visible = true;

    /**
     * Gives the style of the widget
     *
     * @var    string $_style
     * @access private
     * @see    setStyle()
     */
    var $_style;

    /**
     * Gives the css class of the widget
     *
     * @var    string $_class;
     * @access private
     * @see    setClass()
     */
    var $_class;

    /**
     * A flag that determinates if we are using autostyle or no
     *
     * @var    boolean $_AutoStyle
     * @access private
     */
    var $_autoStyle = false;

    /**
     * Gives the 'packable' status of the widget (if the widget is a container or not)
     *
     * @var    boolean $_packable
     * @see    isPackable()
     * @access private
     */
    var $_packable = false;

    /**
     *
     * Gives the JS.
     *
     * @var    string $_JS
     * @access private
     * @see    getJS()
     */
    var $_JS;

    /**
     *
     * Gives the JS files.
     *
     * @var    string $_files
     * @access private
     * @see    getFiles()
     */
    var $_files;

    /**
     *
     * Gives the XHTML string of the value
     *
     * @var    string $_XHTML
     * @access private
     * @see    get(), buildXHTML()
     */
    var $_XHTML;

    /**
     *
     * PiwiXML handler object
     *
     * @var    object  $_PiwiXML
     * @see    getPiwiXML(), buildPiwiXML()
     * @access public
     */
    #var $_PiwiXML;

    /**
     *
     * Title of the widget
     *
     * @var    object $_title
     * @access private
     * @see    setTitle(), getTitle()
     */
    var $_title;

    /**
     *
     * Role of the widget
     *
     * @var     string   $_role
     * @access  private
     * @see     setRole()
     */
    var $_role;

    /**
     * Container Class (HTML wise)
     * Container is for example a div or a td
     *
     * var     string  $_contClass
     * @access private
     * @see    setContainerClass(), getContainerClass()
     */
    var $_contClass = '';

    /**
     * Initializes the main vars..
     *
     * @access    private
     */
    function init()
    {
        $this->_autoStyle = false;
        $this->_files     = array();
        #$this->_PiwiXML   = new PiwiXMLBuilder();

        if (empty($this->_familyWidget)) {
            $this->_familyWidget = 'base';
        }

        if (empty($this->_id)) {
            $useNameAsId = Piwi::getVarConf('PIWI_NAME_AS_ID');
            if ($useNameAsId === true && !empty($this->_name)) {
                $this->setId($this->_name);
            }
        }
    }

    /**
     * Get the widget class name
     *
     * @access public
     */
    function getClassName()
    {
        return strtolower(get_class($this));
    }

    /**
     * Get the widget family name
     *
     * @access   public
     */
    function getFamilyWidget()
    {
        return $this->_familyWidget;
    }

    /**
     * Set the container class
     *
     * @access    public
     * @param     string Container class to use
     */
    function setContainerClass($contClass)
    {
        $this->_contClass = $contClass;
    }

    /**
     * Get the container class of the widget
     *
     * @access   public
     */
    function getContainerClass()
    {
        return $this->_contClass;
    }

    /**
     * Set the name of the widget
     *
     * @access   public
     */
    function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Get the name fo the widget
     *
     * @access   public
     */
    function getName()
    {
        return $this->_name;
    }

    /**
     * Get the id of the widget
     *
     * @access   public
     */
    function getID()
    {
        return $this->_id;
    }

    /**
     * Set the id of the widget
     *
     * @access   public
     */
    function setID($id)
    {
        Piwi::registerId($id);
        $this->_id = $id;
    }

    /**
     * Set the value of the widget
     *
     * @aram    string   $value  Widget value
     * @access   public
     */
    function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * Get the value of the widget
     *
     * @access   public
     */
    function getValue()
    {
        return $this->_value;
    }

    /**
     * Get the title of the widget
     *
     * @access   public
     */
    function getTitle()
    {
        if (is_null($this->_title)) {
            return ;
        }

        if (is_string($this->_title)) {
            return $this->_title;
        }

        return $this->_title->get();
    }

    /**
     * Set the title
     *
     * @access    public
     * @param string Title to use
     */
    function setTitle($title)
    {
        //$this->_title = new Label($title, $this);
        $this->_title = $title;
    }

    /**
     * Set vale of the role attribute
     *
     * @access  public
     * @param   string  Role value
     */
    function setRole($role)
    {
        $this->_role = $role;
    }

    /**
     * Set the widget size
     *
     * @access   public
     * @param    string Widget size
     */
    function setSize($size)
    {
        $this->_size = $size;
    }

    /**
     * Set the widget as visible
     *
     * @access:   public
     * @param     boolean Is it visible?
     */
    function setVisible($visible = true)
    {
        $this->_visible = $visible;
    }

    /**
     * Return true if the widget has packable properties
     *
     * @access     public
     */
    function isPackable()
    {
        return $this->_packable;
    }

    /**
     * Set the style
     *
     * @access    public
     * @param     string Style to use
     */
    function setStyle($style)
    {
        $this->_style = $style;
    }

    /**
     * Gets the style
     *
     */
    function getStyle()
    {
        return $this->_style;
    }

    /**
     * Fetches the class (css)
     *
     * @access    public
     * @param string CSS class to use
     */
    function setClass($class)
    {
        $this->_class = $class;
    }

    /**
     * Fetches the style
     *
     */
    function getClass()
    {
        return $this->_class;
    }

    /**
     * Build the basic XHTML data (id, style, class, bla bla bla)
     *
     * @access   private
     * @return   string  Core XHTML attributes
     */
    function buildBasicXHTML()
    {
        $xhtml = '';
        if (!empty($this->_name)) {
            $xhtml .= " name=\"".$this->getName()."\"";
        }

        if (!empty($this->_id)) {
            $xhtml .= " id=\"".$this->getID()."\"";
        }

        if (!empty($this->_value) || is_numeric($this->_value)) {
            $xhtml .= " value=\"".str_replace('"','&quot;',$this->_value)."\"";
        }

        if (!empty($this->_class)) {
            $xhtml .= " class=\"".$this->_class."\"";
        }

        if (!$this->_visible) {
            $this->_style.= 'display:none;';
        }

        if (!empty($this->_style)) {
            $xhtml .= " style=\"".$this->_style."\"";
        }

        if (!empty($this->_title)) {
            $xhtml .= " title=\"".$this->getTitle()."\"";
        }

        if (!empty($this->_role)) {
            $xhtml .= " role=\"".$this->_role."\"";
        }

        return $xhtml;
    }

    /**
     * Build the basic PiwiXML data of the widget
     *
     * @access   private
     * @return   string  Core piwiXML attributes
     */
    function buildBasicPiwiXML()
    {
        $this->_PiwiXML->openElement($this->getClassName(), false, true);

        if ($this->_familyWidget == 'bin') {
            if (!empty($this->_name)) {
                $this->_PiwiXML->AddAttribute('name', $this->getName());
            }
        }

        if (!empty($this->_value)) {
            $this->_PiwiXML->AddAttribute('value', $this->getValue());
        }

        if (!empty($this->_class)) {
            $this->_PiwiXML->AddAttribute('class', $this->_class);
        }

        if (!empty($this->_style)) {
            $this->_PiwiXML->AddAttribute('style', $this->_style);
        }
    }

    /**
     * Build the XHTML data
     *
     * @access    public
     */
    function buildXHTML()
    {
        $this->_XHTML = '';
    }

    /**
     * Build the piwiXML data. Main class does nothing
     *
     * @access    public
     */
    function buildPiwiXML()
    {
        //nothing
    }

    /**
     * Get the content of the javascript section
     *
     * @access    public
     * @return    string The JS section
     */
    function getJS()
    {
        return $this->_JS;
    }

    /**
     * Get all files as a string
     *
     * @access    public
     */
    function getFilesAsString()
    {
        $files = '';
        if (is_array($this->_files)) {
            foreach ($this->_files as $file) {
                if (stristr($file, '.css')) {
                    $files.= "<script type=\"text/javascript\">\n";
                    $files.= "    document.write('<link rel=\"stylesheet\" type=\"text/css\" href=\"".$file."\">');\n";
                    $files.= "</script>\n";                  
                } else {
                    $files.= "<script type=\"text/javascript\" src=\"".$file."\">";
                    $files.= "</script>\n";
                }
            }
        }

        return $files;
    }

    /**
     * Get all the files (css and js) and all the js scripts
     *
     * @access    public
     * @return    string The complete JS
     */
    function getFullJS()
    {
        //Get the files as a strng..
        $files = $this->getFilesAsString();
        //Ok, build the JS and the JSfiles
        return $files . '' . $this->_JS;
    }

    /**
     * Get the js files
     *
     * @access    public
     * @return    string The JS section
     */
    function getFiles()
    {
        return $this->_files;
    }

    /**
     * This will clean all the JS of the widget
     *
     * @access    public
     */
    function cleanJS()
    {
        $this->_JS = '';
    }

    /**
     * Add more JS data to the JS.
     *
     * @access    public
     * @param     string  $js JavaScript
     */
    function addJS($js)
    {
        $this->_JS .= $js;
    }

    /**
     * Add more files
     *
     * @access    public
     * @param     array  $files Files
     */
    function addFiles($files)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                $this->addFile($file);
            }
        }
    }

    /**
     * Add just one file
     *
     * @access    public
     * @param     array  $file File
     */
    function addFile($file)
    {
        $this->_files[$file] = $file;
    }

    /**
     * Get the content of the piwiXML
     *
     * @param     boolean $withNoPipes Retrieve the piwiXML with pipes?
     * @access    public
     * @return    string The piwiXML value
     */
    function getPiwiXML($withpipes = false)
    {
        $data = $this->_PiwiXML->getData($withpipes);

        if (empty($data)) {
            $this->BuildPiwiXML();
        }

        return $this->_PiwiXML->getData($withpipes);
    }

    /**
     * Get the content of the XHTML
     *
     * @access    public
     * @return    string The XHTML value
     */
    function getXHTML()
    {
        if (empty($this->_XHTML)) {
            $this->buildXHTML();
        }

        return $this->_HTML;
    }

    /**
     * Get the content of the XHTML var
     *
     * @access    public
     * @param     boolean $withJS  Retrieve the XHTML data with javascript (default: Yes)
     * @return    string The XHTML value
     */
    function get($withJS = true)
    {
        if (empty($this->_XHTML)) {
            $this->buildXHTML();
        }

        if ($withJS) {
            return $this->getFullJS() . '' . $this->_XHTML;
        }

        return $this->_XHTML;
    }

    /**
     * Build the XHTML and piwiXML data
     *
     * @access     public
     */
    function build()
    {
        $this->buildXHTML();
        if (defined('PIWI_CREATE_PIWIXML')) {
            if (PIWI_CREATE_PIWIXML == 'yes' || PIWI_CREATE_PIWIXML == 'y' ||
                PIWI_CREATE_PIWIXML == '1') {
                #$this->buildPiwiXML();
            }
        } else {
            #$this->buildPiwiXML();
        }
    }

    /**
     * Rebuild the XHTML data
     *
     * @access     public
     */
    function rebuild()
    {
        $this->cleanJS();
        $this->build();
    }

    /**
     * Same as get, but it will print the XHTML data
     *
     * @access    public
     * @return    string Print the XHTML value
     */
    function show()
    {
        echo $this->get();
    }

    // {{{ escapeHTML
    /**
     * Escape the data that we are going to output
     *
     * Example:
     *  To print use htmlentities
     *      $string = '"><script>alert(document)</script>';
     *    - $string = escapeHTML($string, true, 'koi8-r');
     *      + $string now is: &quot;&gt;&lt;script&gt;alert(document)&lt;/script&gt;
     *
     *    - $string = escapeHTML($string);
     *      + $string now is: &quot;][[script]]alert(document)[/[script]]
     *
     * @access public
     * @param  string $data     The data that has to be escaped
     * @param  bool   $ents     Use htmlentities or not.
     * @param  string $encoding The encoding to use ( default: utf-8 )
     * @return string (htmlentitied|escaped) The new string.
     */
    function escapeHTML($data, $ents = false, $encoding = 'utf-8')
    {
        $tags = array('/</', '/>/', '/script/', "/'/" , '/"/'   , '/\+/');
        $reps = array('['  , ']'  , '[script]', "[']" , '&quot;', '[plus]');

        if (strlen(trim($data)) > 0 && strlen(trim($encoding)) > 0 && $ents && is_bool($ents)) {
            switch(strtoupper($encoding)) {
            case 'ISO-8859-1':
            case 'ISO-8859-15':
            case 'UTF-8':
            case 'CP866':
            case 'CP1251':
            case 'CP1252':
            case 'KOI8-R':
            case 'BIG-5':
            case 'GB2312':
            case 'BIG5-HKSCS':
            case 'ShIFT_JIS':
            case 'EUC-JP':
                return (string)htmlentities($data, ENT_QUOTES, $encoding);
            default:
                return (string)htmlentities($data, ENT_QUOTES, 'UTF-8');
            }
        } elseif(strlen(trim($data)) > 0) {
            return (string)preg_replace($tags, $reps, $data);
        } else {
            return (string)$data;
        }
    }
    // }}}
}
?>